<?php

namespace CarrinhoDePreia\Controllers;

class FiadoController extends BaseController
{
    public static function cadastrarCliente(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        $nome           = trim((string) self::input($_POST, 'nome', ''));
        $telefone       = (string) self::input($_POST, 'telefone', '');
        $cpf            = (string) self::input($_POST, 'cpf', '');
        $endereco       = (string) self::input($_POST, 'endereco', '');
        $limiteCredito  = (float) self::input($_POST, 'limite_credito', 500.00);
        $observacoes    = (string) self::input($_POST, 'observacoes', '');

        if ($nome === '') {
            self::error('Nome do cliente é obrigatório');
        }

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO clientes_fiado (usuario_id, nome, telefone, cpf, endereco, limite_credito, observacoes, data_cadastro)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([$userId, $nome, $telefone, $cpf, $endereco, $limiteCredito, $observacoes]);
            self::json(true, ['cliente_id' => $pdo->lastInsertId(), 'nome' => $nome], 'Cliente cadastrado com sucesso');
        } catch (\Throwable $e) {
            self::logError('Erro ao cadastrar cliente fiado', ['error' => $e->getMessage()]);
            self::error('Erro ao cadastrar cliente');
        }
    }

    public static function registrarPagamento(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        $clienteId       = (int) self::input($_POST, 'cliente_id', 0);
        $valor           = (float) self::input($_POST, 'valor', 0);
        $formaPagamento  = (string) self::input($_POST, 'forma_pagamento', 'Dinheiro');
        $observacoes     = (string) self::input($_POST, 'observacoes', '');

        if ($clienteId === 0 || $valor <= 0) {
            self::error('Dados inválidos');
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('SELECT * FROM clientes_fiado WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$clienteId, $userId]);
            $cliente = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$cliente) {
                throw new \Exception('Cliente não encontrado');
            }

            $saldoAtual = (float) $cliente['saldo_devedor'];
            if ($valor > $saldoAtual) {
                throw new \Exception('Valor do pagamento maior que o saldo devedor');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO pagamentos_fiado (cliente_id, valor, tipo, forma_pagamento, observacoes, data_pagamento, registrado_por)
                 VALUES (?, ?, 'pagamento', ?, ?, NOW(), ?)"
            );
            $stmt->execute([$clienteId, $valor, $formaPagamento, $observacoes, $userId]);
            $pagamentoId = $pdo->lastInsertId();

            $novoSaldo = $saldoAtual - $valor;
            $stmt = $pdo->prepare('UPDATE clientes_fiado SET saldo_devedor = ? WHERE id = ?');
            $stmt->execute([$novoSaldo, $clienteId]);

            $pdo->commit();

            self::json(true, [
                'pagamento_id'   => $pagamentoId,
                'saldo_anterior' => $saldoAtual,
                'valor_pago'     => $valor,
                'novo_saldo'     => $novoSaldo,
            ], 'Pagamento registrado com sucesso');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            self::logError('Erro ao registrar pagamento fiado', ['error' => $e->getMessage()]);
            self::error($e->getMessage());
        }
    }

    public static function dashboard(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();
        $hoje   = date('Y-m-d');
        $mes    = date('Y-m');

        try {
            $stmt = $pdo->prepare('SELECT SUM(saldo_devedor) AS total FROM clientes_fiado WHERE usuario_id = ? AND ativo = 1');
            $stmt->execute([$userId]);
            $totalReceber = (float) ($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);

            $stmt = $pdo->prepare('SELECT COUNT(*) AS qtd FROM clientes_fiado WHERE usuario_id = ? AND ativo = 1 AND saldo_devedor > 0');
            $stmt->execute([$userId]);
            $qtdClientes = (int) ($stmt->fetch(\PDO::FETCH_ASSOC)['qtd'] ?? 0);

            $stmt = $pdo->prepare(
                'SELECT COUNT(*) AS qtd, COALESCE(SUM(saldo_devedor), 0) AS valor
                 FROM clientes_fiado
                 WHERE usuario_id = ? AND ativo = 1 AND saldo_devedor > 0
                       AND DATEDIFF(NOW(), ultima_compra) > 30'
            );
            $stmt->execute([$userId]);
            $inadimplentes = $stmt->fetch(\PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare(
                "SELECT COUNT(*) AS qtd, COALESCE(SUM(valor), 0) AS total
                 FROM pagamentos_fiado pf
                 INNER JOIN clientes_fiado cf ON pf.cliente_id = cf.id
                 WHERE cf.usuario_id = ? AND DATE(pf.data_pagamento) = ? AND pf.tipo = 'pagamento'"
            );
            $stmt->execute([$userId, $hoje]);
            $recebidoHoje = $stmt->fetch(\PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare(
                "SELECT COUNT(*) AS qtd, COALESCE(SUM(valor), 0) AS total
                 FROM pagamentos_fiado pf
                 INNER JOIN clientes_fiado cf ON pf.cliente_id = cf.id
                 WHERE cf.usuario_id = ? AND DATE_FORMAT(pf.data_pagamento, '%Y-%m') = ? AND pf.tipo = 'compra'"
            );
            $stmt->execute([$userId, $mes]);
            $vendasMes = $stmt->fetch(\PDO::FETCH_ASSOC);

            self::json(true, [
                'total_receber'          => $totalReceber,
                'qtd_clientes'           => $qtdClientes,
                'clientes_inadimplentes' => (int) $inadimplentes['qtd'],
                'valor_inadimplente'     => (float) $inadimplentes['valor'],
                'recebido_hoje'          => (float) $recebidoHoje['total'],
                'qtd_pagamentos_hoje'    => (int) $recebidoHoje['qtd'],
                'vendas_mes'             => (float) $vendasMes['total'],
                'qtd_vendas_mes'         => (int) $vendasMes['qtd'],
            ], 'Dashboard carregado');
        } catch (\Throwable $e) {
            self::logError('Erro dashboard fiado', ['error' => $e->getMessage()]);
            self::error('Erro ao carregar dashboard');
        }
    }

    public static function listarClientes(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        $query = trim((string) ($_GET['query'] ?? ''));

        try {
            $sql = 
                "SELECT cf.*,
                        DATEDIFF(NOW(), cf.ultima_compra) AS dias_sem_comprar,
                        COALESCE(SUM(CASE WHEN pf.tipo = 'compra' THEN pf.valor ELSE 0 END), 0) AS total_compras,
                        COALESCE(SUM(CASE WHEN pf.tipo = 'pagamento' THEN pf.valor ELSE 0 END), 0) AS total_pago
                 FROM clientes_fiado cf
                 LEFT JOIN pagamentos_fiado pf ON cf.id = pf.cliente_id
                 WHERE cf.usuario_id = ? AND cf.ativo = 1";

            $params = [$userId];

            if ($query !== '') {
                $sql .= " AND (cf.nome LIKE ? OR cf.telefone LIKE ? OR cf.celular LIKE ? )";
                $like = '%' . $query . '%';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }

            $sql .= " GROUP BY cf.id ORDER BY cf.saldo_devedor DESC, cf.nome ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            self::json(true, $stmt->fetchAll(\PDO::FETCH_ASSOC), 'Clientes carregados');
        } catch (\Throwable $e) {
            self::logError('Erro listar clientes fiado', ['error' => $e->getMessage()]);
            self::error('Erro ao listar clientes');
        }
    }

    public static function historicoCliente(): void
    {
        $userId    = self::requireAuth();
        $pdo       = self::getPdo();
        $clienteId = (int) self::input($_GET, 'cliente_id', 0);

        if ($clienteId === 0) {
            self::error('ID do cliente não informado');
        }

        try {
            $stmt = $pdo->prepare('SELECT * FROM clientes_fiado WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$clienteId, $userId]);
            $cliente = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$cliente) {
                self::error('Cliente não encontrado');
            }

            $stmt = $pdo->prepare(
                'SELECT * FROM pagamentos_fiado WHERE cliente_id = ? ORDER BY data_pagamento DESC LIMIT 100'
            );
            $stmt->execute([$clienteId]);
            $historico = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            self::json(true, ['cliente' => $cliente, 'historico' => $historico], 'Histórico carregado');
        } catch (\Throwable $e) {
            self::logError('Erro histórico cliente', ['error' => $e->getMessage()]);
            self::error('Erro ao carregar histórico');
        }
    }
}
