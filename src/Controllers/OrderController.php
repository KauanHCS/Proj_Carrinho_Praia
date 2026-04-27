<?php

namespace CarrinhoDePreia\Controllers;

class OrderController extends BaseController
{
    public static function atualizarStatus(): void
    {
        self::requireAuth();
        $pdo        = self::getPdo();
        $pedidoId   = (int) self::input($_POST, 'pedido_id', 0);
        $novoStatus = (string) self::input($_POST, 'novo_status', '');

        if ($pedidoId === 0 || $novoStatus === '') {
            self::error('Dados inválidos');
        }

        $statusValidos = ['pendente', 'em_preparo', 'pronto', 'entregue', 'cancelado'];
        if (!in_array($novoStatus, $statusValidos, true)) {
            self::error('Status inválido');
        }

        try {
            $stmt = $pdo->prepare('UPDATE pedidos SET status = ?, data_atualizacao = NOW() WHERE id = ?');
            $stmt->execute([$novoStatus, $pedidoId]);
            self::json(true, ['status' => $novoStatus], 'Status atualizado com sucesso');
        } catch (\Throwable $e) {
            self::logError('Erro atualizar status pedido', ['error' => $e->getMessage()]);
            self::error('Erro ao atualizar status');
        }
    }

    public static function listar(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        $filtroStatus = (string) self::input($_GET, 'status', '');
        $filtroData   = (string) self::input($_GET, 'data', '');

        $sql    = 'SELECT * FROM pedidos WHERE usuario_vendedor_id = ?';
        $params = [$userId];

        if ($filtroStatus !== '') { $sql .= ' AND status = ?'; $params[] = $filtroStatus; }
        if ($filtroData !== '')   { $sql .= ' AND DATE(data_pedido) = ?'; $params[] = $filtroData; }

        $sql .= ' ORDER BY data_pedido DESC';

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            self::json(true, $stmt->fetchAll(\PDO::FETCH_ASSOC), 'Pedidos carregados');
        } catch (\Throwable $e) {
            self::logError('Erro listar pedidos', ['error' => $e->getMessage()]);
            self::error('Erro ao carregar pedidos');
        }
    }
}
