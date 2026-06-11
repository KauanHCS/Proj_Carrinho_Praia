<?php

namespace CarrinhoDePreia\Controllers;

class GuardasolController extends BaseController
{
    public static function cadastrar(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();
        $numero = (string) self::input($_POST, 'numero', '');

        if ($numero === '') {
            self::error('Número do guarda-sol é obrigatório');
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO guardasois (numero, usuario_id) VALUES (?, ?)');
            $stmt->execute([$numero, $userId]);
            self::json(true, ['guardasol_id' => $pdo->lastInsertId(), 'numero' => $numero], 'Guarda-sol cadastrado');
        } catch (\Throwable $e) {
            self::logError('Erro cadastrar guardasol', ['error' => $e->getMessage()]);
            self::error('Erro ao cadastrar guarda-sol');
        }
    }

    public static function ocupar(): void
    {
        $userId       = self::requireAuth();
        $pdo          = self::getPdo();
        $guardasolId  = (int) self::input($_POST, 'guardasol_id', 0);
        $clienteNome  = (string) self::input($_POST, 'cliente_nome', '');

        if ($guardasolId === 0) {
            self::error('ID do guarda-sol é obrigatório');
        }

        try {
            $stmt = $pdo->prepare(
                "UPDATE guardasois SET status = 'ocupado', cliente_nome = ?, horario_ocupacao = NOW()\n                 WHERE id = ? AND usuario_id = ?"
            );
            $stmt->execute([$clienteNome, $guardasolId, $userId]);
            self::json(true, null, 'Guarda-sol ocupado');
        } catch (\Throwable $e) {
            self::logError('Erro ocupar guardasol', ['error' => $e->getMessage()]);
            self::error('Erro ao ocupar guarda-sol');
        }
    }

    public static function adicionarComanda(): void
    {
        $userId       = self::requireAuth();
        $pdo          = self::getPdo();
        $guardasolId  = (int) self::input($_POST, 'guardasol_id', 0);
        $produtos     = (string) self::input($_POST, 'produtos', '');
        $subtotal     = (float) self::input($_POST, 'subtotal', 0);

        if ($guardasolId === 0 || $produtos === '') {
            self::error('Dados inválidos');
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('SELECT numero, cliente_nome FROM guardasois WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$guardasolId, $userId]);
            $guardasol = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$guardasol) {
                $pdo->rollBack();
                self::error('Guarda-sol não encontrado');
            }

            $stmt = $pdo->prepare('INSERT INTO comandas (guardasol_id, usuario_id, produtos, subtotal) VALUES (?, ?, ?, ?)');
            $stmt->execute([$guardasolId, $userId, $produtos, $subtotal]);
            $comandaId = $pdo->lastInsertId();

            // Atualiza total consumido
            $stmt = $pdo->prepare('UPDATE guardasois SET total_consumido = total_consumido + ? WHERE id = ?');
            $stmt->execute([$subtotal, $guardasolId]);

            // Garantir que o guarda-sol fique como ocupado quando adicionar uma comanda
            $stmt = $pdo->prepare("UPDATE guardasois SET status = 'ocupado', horario_ocupacao = COALESCE(horario_ocupacao, NOW()) WHERE id = ? AND status <> 'ocupado'");
            $stmt->execute([$guardasolId]);

            $numeroPedido = 'GS' . str_pad((string) $guardasol['numero'], 3, '0', STR_PAD_LEFT) . '-' . str_pad((string) $comandaId, 4, '0', STR_PAD_LEFT);
            $nomeCliente  = $guardasol['cliente_nome'] ?: 'Guarda-sol ' . $guardasol['numero'];

            $stmt = $pdo->prepare(
                "INSERT INTO pedidos (numero_pedido, nome_cliente, produtos, total, usuario_vendedor_id, status, observacoes)\n                 VALUES (?, ?, ?, ?, ?, 'pendente', ?)"
            );
            $stmt->execute([
                $numeroPedido, $nomeCliente, $produtos, $subtotal, $userId,
                'Pedido do Guarda-sol ' . $guardasol['numero'] . ' - Comanda #' . $comandaId,
            ]);

            $pdo->commit();
            self::json(true, ['comanda_id' => $comandaId, 'pedido_numero' => $numeroPedido], 'Comanda adicionada e pedido enviado para preparo');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            self::logError('Erro adicionar comanda', ['error' => $e->getMessage()]);
            self::error('Erro ao adicionar comanda');
        }
    }

    public static function finalizar(): void
    {
        $userId      = self::requireAuth();
        $pdo         = self::getPdo();
        $guardasolId = (int) self::input($_POST, 'guardasol_id', 0);

        if ($guardasolId === 0) {
            self::error('ID do guarda-sol é obrigatório');
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE comandas SET status = 'fechado', data_fechamento = NOW() WHERE guardasol_id = ? AND status = 'aberto'");
            $stmt->execute([$guardasolId]);

            $stmt = $pdo->prepare(
                "UPDATE guardasois SET status = 'vazio', cliente_nome = NULL, horario_ocupacao = NULL, total_consumido = 0.00\n                 WHERE id = ? AND usuario_id = ?"
            );
            $stmt->execute([$guardasolId, $userId]);

            $pdo->commit();
            self::json(true, null, 'Guarda-sol finalizado e liberado');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            self::logError('Erro finalizar guardasol', ['error' => $e->getMessage()]);
            self::error('Erro ao finalizar guarda-sol');
        }
    }

    public static function remover(): void
    {
        $userId      = self::requireAuth();
        $pdo         = self::getPdo();
        $guardasolId = (int) self::input($_POST, 'guardasol_id', 0);

        if ($guardasolId === 0) {
            self::error('ID do guarda-sol é obrigatório');
        }

        try {
            $stmt = $pdo->prepare('SELECT status FROM guardasois WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$guardasolId, $userId]);
            if (!$stmt->fetch(\PDO::FETCH_ASSOC)) {
                self::error('Guarda-sol não encontrado');
            }

            $stmt = $pdo->prepare('UPDATE guardasois SET ativo = 0 WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$guardasolId, $userId]);

            self::json(true, null, 'Guarda-sol removido');
        } catch (\Throwable $e) {
            self::logError('Erro remover guardasol', ['error' => $e->getMessage()]);
            self::error('Erro ao remover guarda-sol');
        }
    }

    public static function fecharComanda(): void
    {
        $userId      = self::requireAuth();
        $pdo         = self::getPdo();
        $guardasolId = (int) self::input($_POST, 'guardasol_id', 0);

        if ($guardasolId === 0) {
            self::error('ID do guarda-sol é obrigatório');
        }

        try {
            $stmt = $pdo->prepare("UPDATE guardasois SET status = 'aguardando_pagamento' WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$guardasolId, $userId]);
            self::json(true, null, 'Comanda fechada, aguardando pagamento');
        } catch (\Throwable $e) {
            self::logError('Erro fechar comanda', ['error' => $e->getMessage()]);
            self::error('Erro ao fechar comanda');
        }
    }

    public static function finalizarPagamento(): void
    {
        $userId          = self::requireAuth();
        $pdo             = self::getPdo();
        $guardasolId     = (int) self::input($_POST, 'guardasol_id', 0);
        $formaPagamento  = (string) self::input($_POST, 'forma_pagamento', '');
        $total           = (float) self::input($_POST, 'total', 0);

        if ($guardasolId === 0 || $formaPagamento === '') {
            self::error('Dados inválidos');
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM comandas WHERE guardasol_id = ? AND status = 'aberto'");
            $stmt->execute([$guardasolId]);
            $comandas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!$comandas) {
                $pdo->rollBack();
                self::error('Nenhuma comanda aberta encontrada');
            }

            $stmt = $pdo->prepare('INSERT INTO vendas (usuario_id, total, forma_pagamento) VALUES (?, ?, ?)');
            $stmt->execute([$userId, $total, $formaPagamento]);
            $vendaId = $pdo->lastInsertId();

            foreach ($comandas as $c) {
                $produtos = json_decode($c['produtos'], true) ?: [];
                foreach ($produtos as $prod) {
                    $stmt = $pdo->prepare('UPDATE produtos SET quantidade = quantidade - ? WHERE id = ? AND usuario_id = ?');
                    $stmt->execute([$prod['quantidade'], $prod['produto_id'], $userId]);
                }
            }

            $stmt = $pdo->prepare("UPDATE comandas SET status = 'fechado', data_fechamento = NOW() WHERE guardasol_id = ? AND status = 'aberto'");
            $stmt->execute([$guardasolId]);

            $stmt = $pdo->prepare(
                "UPDATE guardasois SET status = 'vazio', cliente_nome = NULL, horario_ocupacao = NULL, total_consumido = 0.00\n                 WHERE id = ? AND usuario_id = ?"
            );
            $stmt->execute([$guardasolId, $userId]);

            $pdo->commit();
            self::json(true, ['venda_id' => $vendaId], 'Pagamento realizado e guarda-sol liberado');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            self::logError('Erro finalizarPagamentoComanda', ['error' => $e->getMessage()]);
            self::error('Erro ao processar pagamento');
        }
    }

    public static function listar(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        try {
            $stmt = $pdo->prepare(
                "SELECT * FROM view_resumo_guardasois\n                 WHERE usuario_id = ?\n                 ORDER BY CASE status\n                            WHEN 'aguardando_pagamento' THEN 1\n                            WHEN 'ocupado' THEN 2\n                            WHEN 'vazio' THEN 3\n                          END, CAST(numero AS UNSIGNED), numero"
            );
            $stmt->execute([$userId]);
            self::json(true, $stmt->fetchAll(\PDO::FETCH_ASSOC), 'Guarda-sóis carregados');
        } catch (\Throwable $e) {
            self::logError('Erro listar guardasois', ['error' => $e->getMessage()]);
            self::error('Erro ao listar guarda-sóis');
        }
    }

    public static function obterComandas(): void
    {
        $userId      = self::requireAuth();
        $pdo         = self::getPdo();
        $guardasolId = (int) self::input($_GET, 'guardasol_id', 0);

        if ($guardasolId === 0) {
            self::error('ID do guarda-sol não informado');
        }

        try {
            $stmt = $pdo->prepare('SELECT * FROM guardasois WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$guardasolId, $userId]);
            if (!$stmt->fetch(\PDO::FETCH_ASSOC)) {
                self::error('Guarda-sol não encontrado');
            }

            $stmt = $pdo->prepare("SELECT * FROM comandas WHERE guardasol_id = ? AND status = 'aberto' ORDER BY data_pedido ASC");
            $stmt->execute([$guardasolId]);
            self::json(true, $stmt->fetchAll(\PDO::FETCH_ASSOC), 'Comandas carregadas');
        } catch (\Throwable $e) {
            self::logError('Erro obter comandas', ['error' => $e->getMessage()]);
            self::error('Erro ao carregar comandas');
        }
    }
}
