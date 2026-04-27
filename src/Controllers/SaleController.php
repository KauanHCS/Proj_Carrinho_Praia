<?php

namespace CarrinhoDePreia\Controllers;

class SaleController extends BaseController
{
    public static function finalizarVenda(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        $carrinho                  = (string) self::input($_POST, 'carrinho', '');
        $formaPagamento            = (string) self::input($_POST, 'forma_pagamento', '');
        $nomeCliente               = (string) self::input($_POST, 'nome_cliente', '');
        $telefoneCliente           = (string) self::input($_POST, 'telefone_cliente', '');
        $criarPedido               = (string) self::input($_POST, 'criar_pedido', '0');
        $valorPago                 = (float) self::input($_POST, 'valor_pago', 0);
        $formaPagamentoSecundaria  = self::input($_POST, 'forma_pagamento_secundaria', null);
        $valorPagoSecundario       = self::input($_POST, 'valor_pago_secundario', null);
        $formaPagamentoTerciaria   = self::input($_POST, 'forma_pagamento_terciaria', null);
        $valorPagoTerciario        = self::input($_POST, 'valor_pago_terciario', null);
        $clienteFiadoId            = self::input($_POST, 'cliente_fiado_id', null);

        if ($carrinho === '') {
            self::error('Carrinho não pode estar vazio');
        }

        $itensCarrinho = json_decode($carrinho, true);
        if (!is_array($itensCarrinho)) {
            self::error('Formato inválido do carrinho');
        }

        $total = 0.0;
        foreach ($itensCarrinho as $item) {
            $total += ((float) ($item['preco'] ?? 0)) * ((int) ($item['quantidade'] ?? 0));
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE funcao_funcionario IN ('financeiro', 'financeiro_e_anotar') AND ativo = 1");
            $stmt->execute();
            $temFinanceiro = $stmt->fetchColumn() > 0;
            $statusPagamento = $temFinanceiro ? 'pendente' : 'pago';

            $stmt = $pdo->prepare(
                'INSERT INTO vendas (usuario_id, nome_cliente, total, forma_pagamento, valor_pago,
                    forma_pagamento_secundaria, valor_pago_secundario,
                    forma_pagamento_terciaria, valor_pago_terciario,
                    cliente_fiado_id, status_pagamento, data)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([
                $userId, $nomeCliente, $total, $formaPagamento, $valorPago,
                $formaPagamentoSecundaria, $valorPagoSecundario,
                $formaPagamentoTerciaria, $valorPagoTerciario,
                $clienteFiadoId, $statusPagamento,
            ]);
            $vendaId = $pdo->lastInsertId();

            foreach ($itensCarrinho as $item) {
                $stmt = $pdo->prepare(
                    'UPDATE produtos SET quantidade = quantidade - ?
                     WHERE id = ? AND usuario_id = ? AND quantidade >= ?'
                );
                $stmt->execute([
                    $item['quantidade'], $item['id'], $userId, $item['quantidade'],
                ]);
                if ($stmt->rowCount() === 0) {
                    throw new \Exception('Estoque insuficiente para: ' . ($item['nome'] ?? 'item'));
                }
            }

            $pedidoId      = null;
            $pedidoCriado  = false;
            if ($criarPedido === '1') {
                $stmt = $pdo->prepare(
                    "INSERT INTO pedidos (nome_cliente, telefone_cliente, produtos, total, usuario_vendedor_id, status, data_pedido)
                     VALUES (?, ?, ?, ?, ?, 'pendente', NOW())"
                );
                $stmt->execute([$nomeCliente, $telefoneCliente, $carrinho, $total, $userId]);
                $pedidoId     = $pdo->lastInsertId();
                $pedidoCriado = true;
            }

            if ($clienteFiadoId) {
                $valorFiado = 0.0;
                if ($formaPagamento === 'fiado')             { $valorFiado += (float) $valorPago; }
                if ($formaPagamentoSecundaria === 'fiado')   { $valorFiado += (float) $valorPagoSecundario; }
                if ($formaPagamentoTerciaria === 'fiado')    { $valorFiado += (float) $valorPagoTerciario; }

                if ($valorFiado > 0) {
                    $stmt = $pdo->prepare(
                        "INSERT INTO pagamentos_fiado (cliente_id, venda_id, valor, tipo, forma_pagamento, observacoes, data_pagamento, registrado_por)
                         VALUES (?, ?, ?, 'compra', 'Fiado', ?, NOW(), ?)"
                    );
                    $stmt->execute([$clienteFiadoId, $vendaId, $valorFiado, 'Venda Rápida - Carrinho de Praia', $userId]);

                    $stmt = $pdo->prepare(
                        'UPDATE clientes_fiado SET saldo_devedor = saldo_devedor + ?, ultima_compra = NOW() WHERE id = ?'
                    );
                    $stmt->execute([$valorFiado, $clienteFiadoId]);
                }
            }

            $pdo->commit();

            $msg = $temFinanceiro
                ? 'Venda registrada! Aguardando processamento do pagamento pelo financeiro.'
                : 'Venda finalizada com sucesso!';

            self::json(true, [
                'venda_id'         => $vendaId,
                'pedido_criado'    => $pedidoCriado,
                'pedido_id'        => $pedidoId,
                'total'            => $total,
                'status_pagamento' => $statusPagamento,
                'tem_financeiro'   => $temFinanceiro,
            ], $msg);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            self::logError('Erro ao processar venda', ['error' => $e->getMessage()]);
            self::error('Erro ao processar venda');
        }
    }

    public static function listarVendasFinanceiro(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        $filtroStatus = (string) self::input($_GET, 'status', '');
        $filtroData   = (string) self::input($_GET, 'data', '');

        $sql = 'SELECT v.id, v.data, v.forma_pagamento, v.total, v.valor_pago, v.troco, v.desconto,
                       v.cliente_nome, v.cliente_telefone, v.observacoes, v.status,
                       u.nome AS vendedor_nome, v.usuario_id
                FROM vendas v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                WHERE v.usuario_id = ?';
        $params = [$userId];

        if ($filtroStatus !== '') { $sql .= ' AND v.status = ?'; $params[] = $filtroStatus; }
        if ($filtroData !== '')   { $sql .= ' AND DATE(v.data) = ?'; $params[] = $filtroData; }

        $sql .= ' ORDER BY v.data DESC';

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $vendas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($vendas as &$venda) {
                $stmtItems = $pdo->prepare(
                    'SELECT iv.quantidade, iv.preco_unitario, iv.subtotal, p.nome AS produto_nome
                     FROM itens_venda iv LEFT JOIN produtos p ON iv.produto_id = p.id
                     WHERE iv.venda_id = ?'
                );
                $stmtItems->execute([$venda['id']]);
                $itens = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);

                $produtos = [];
                foreach ($itens as $item) {
                    $produtos[] = $item['quantidade'] . 'x ' . $item['produto_nome'];
                }
                $venda['produtos']      = $itens;
                $venda['produtos_info'] = implode(', ', $produtos);
                $venda['numero_guardasol'] = null;
                if (!empty($venda['observacoes'])) {
                    if (preg_match('/Guarda-sol\s+(\d+)/i', $venda['observacoes'], $m)) {
                        $venda['numero_guardasol'] = $m[1];
                    } elseif (preg_match('/GS(\d+)/i', $venda['observacoes'], $m)) {
                        $venda['numero_guardasol'] = $m[1];
                    }
                }
            }

            self::json(true, $vendas, 'Vendas carregadas');
        } catch (\Throwable $e) {
            self::logError('Erro listarVendasFinanceiro', ['error' => $e->getMessage()]);
            self::error('Erro ao carregar vendas');
        }
    }
}
