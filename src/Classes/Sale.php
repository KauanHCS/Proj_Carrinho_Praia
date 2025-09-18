<?php

namespace CarrinhoDePreia;

use CarrinhoDePreia\Database;
use CarrinhoDePreia\Product;

/**
 * Classe Sale - Gerencia vendas e carrinho
 * Mantém compatibilidade total com as funções originais
 */
class Sale
{
    private $db;
    private $product;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->product = new Product();
    }

    /**
     * Finalizar venda - mantém mesma lógica original
     */
    public function finalizarVenda($carrinho, $formaPagamento, $valorPago = 0, $usuarioId = null)
    {
        try {
            if (empty($carrinho) || !is_array($carrinho)) {
                throw new \Exception('Carrinho inválido ou vazio');
            }

            if (empty($formaPagamento)) {
                throw new \Exception('Forma de pagamento é obrigatória');
            }

            // Validar formas de pagamento permitidas
            $formasPermitidas = ['dinheiro', 'pix', 'cartao', 'multiplo'];
            if (!in_array($formaPagamento, $formasPermitidas)) {
                throw new \Exception('Forma de pagamento inválida');
            }

            // Iniciar transação - mesma lógica original
            $conn = $this->db->getConnection();
            $conn->begin_transaction();

            // Calcular total
            $total = 0;
            foreach ($carrinho as $item) {
                if (!isset($item['id'], $item['quantidade'], $item['preco'])) {
                    throw new \Exception('Item do carrinho inválido');
                }
                $total += $item['preco'] * $item['quantidade'];
            }

            // Validar valor pago para dinheiro
            if ($formaPagamento === 'dinheiro') {
                if ($valorPago < $total) {
                    throw new \Exception('Valor pago insuficiente');
                }
            }

            // Verificar disponibilidade de estoque para todos os itens
            foreach ($carrinho as $item) {
                $availability = $this->product->checkAvailability($item['id'], $item['quantidade']);
                if (!$availability['success']) {
                    throw new \Exception($availability['message']);
                }
            }

            // Inserir venda - mesma estrutura original
            $troco = ($formaPagamento === 'dinheiro') ? max(0, $valorPago - $total) : 0;
            
            $sql = "INSERT INTO vendas (data, forma_pagamento, total, valor_pago, troco, usuario_id) VALUES (NOW(), ?, ?, ?, ?, ?)";
            $vendaId = $this->db->insert($sql, "sdddi", [
                $formaPagamento, 
                $total, 
                $valorPago, 
                $troco, 
                $usuarioId
            ]);

            // Inserir itens da venda e atualizar estoque - mantém triggers originais
            foreach ($carrinho as $item) {
                // Inserir item da venda - O trigger tr_item_venda_inserted automaticamente:
                // 1. Atualizará o estoque do produto
                // 2. Registrará a movimentação de saída
                $sql = "INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)";
                $this->db->insert($sql, "iiid", [
                    $vendaId, 
                    $item['id'], 
                    $item['quantidade'], 
                    $item['preco']
                ]);
            }

            // Commit da transação
            $conn->commit();

            return [
                'success' => true,
                'data' => [
                    'venda_id' => $vendaId,
                    'total' => $total,
                    'troco' => $troco,
                    'forma_pagamento' => $formaPagamento
                ],
                'message' => 'Venda finalizada com sucesso'
            ];

        } catch (\Exception $e) {
            // Rollback em caso de erro
            if (isset($conn)) {
                $conn->rollback();
            }
            
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter vendas por período
     */
    public function getVendasPorPeriodo($usuarioId, $dataInicio = null, $dataFim = null)
    {
        try {
            $sql = "SELECT v.*, u.nome as vendedor_nome 
                    FROM vendas v 
                    LEFT JOIN usuarios u ON v.usuario_id = u.id 
                    WHERE 1=1";
            
            $params = [];
            $types = "";

            if ($usuarioId) {
                $sql .= " AND v.usuario_id = ?";
                $params[] = $usuarioId;
                $types .= "i";
            }

            if ($dataInicio) {
                $sql .= " AND DATE(v.data) >= ?";
                $params[] = $dataInicio;
                $types .= "s";
            }

            if ($dataFim) {
                $sql .= " AND DATE(v.data) <= ?";
                $params[] = $dataFim;
                $types .= "s";
            }

            $sql .= " ORDER BY v.data DESC";

            $vendas = $this->db->select($sql, $types, $params);

            return [
                'success' => true,
                'data' => $vendas,
                'message' => ''
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter vendas de hoje - compatível com template original
     */
    public function getVendasHoje($usuarioId = null)
    {
        return $this->getVendasPorPeriodo($usuarioId, date('Y-m-d'), date('Y-m-d'));
    }

    /**
     * Obter detalhes de uma venda específica
     */
    public function getVendaDetalhes($vendaId, $usuarioId = null)
    {
        try {
            $sql = "SELECT v.*, u.nome as vendedor_nome 
                    FROM vendas v 
                    LEFT JOIN usuarios u ON v.usuario_id = u.id 
                    WHERE v.id = ?";
            
            $params = [$vendaId];
            $types = "i";

            if ($usuarioId) {
                $sql .= " AND v.usuario_id = ?";
                $params[] = $usuarioId;
                $types .= "i";
            }

            $venda = $this->db->selectOne($sql, $types, $params);

            if (!$venda) {
                throw new \Exception('Venda não encontrada');
            }

            // Obter itens da venda
            $sql = "SELECT iv.*, p.nome as produto_nome 
                    FROM itens_venda iv 
                    JOIN produtos p ON iv.produto_id = p.id 
                    WHERE iv.venda_id = ?";
            
            $itens = $this->db->select($sql, "i", [$vendaId]);

            $venda['itens'] = $itens;

            return [
                'success' => true,
                'data' => $venda,
                'message' => ''
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancelar venda (se permitido)
     */
    public function cancelarVenda($vendaId, $usuarioId, $motivo = '')
    {
        try {
            // Verificar se a venda existe e pertence ao usuário
            $venda = $this->getVendaDetalhes($vendaId, $usuarioId);
            if (!$venda['success']) {
                throw new \Exception('Venda não encontrada');
            }

            $vendaData = $venda['data'];

            // Verificar se a venda é de hoje (regra de negócio)
            $dataVenda = date('Y-m-d', strtotime($vendaData['data']));
            $hoje = date('Y-m-d');
            
            if ($dataVenda !== $hoje) {
                throw new \Exception('Apenas vendas do dia podem ser canceladas');
            }

            // Iniciar transação
            $conn = $this->db->getConnection();
            $conn->begin_transaction();

            // Restaurar estoque dos produtos
            foreach ($vendaData['itens'] as $item) {
                $sql = "UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?";
                $this->db->execute($sql, "ii", [$item['quantidade'], $item['produto_id']]);

                // Registrar movimentação de estoque
                $sql = "INSERT INTO movimentacoes (produto_id, tipo, quantidade, quantidade_anterior, quantidade_atual, descricao, venda_id) 
                        SELECT ?, 'entrada', ?, quantidade - ?, quantidade, ?, ? FROM produtos WHERE id = ?";
                $this->db->execute($sql, "iiissii", [
                    $item['produto_id'],
                    $item['quantidade'],
                    $item['quantidade'],
                    "Cancelamento de venda - " . $item['produto_nome'],
                    $vendaId,
                    $item['produto_id']
                ]);
            }

            // Atualizar status da venda
            $sql = "UPDATE vendas SET status = 'cancelada', observacoes = ? WHERE id = ? AND usuario_id = ?";
            $this->db->execute($sql, "sii", ["Cancelada: " . $motivo, $vendaId, $usuarioId]);

            $conn->commit();

            return [
                'success' => true,
                'data' => ['venda_id' => $vendaId],
                'message' => 'Venda cancelada com sucesso'
            ];

        } catch (\Exception $e) {
            if (isset($conn)) {
                $conn->rollback();
            }
            
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter estatísticas de vendas para relatórios
     */
    public function getEstatisticasVendas($usuarioId, $periodo = 'hoje')
    {
        try {
            $whereDate = "";
            $params = [];
            $types = "";

            // Filtrar por usuário se especificado
            $whereUser = "";
            if ($usuarioId) {
                $whereUser = " AND p.usuario_id = ?";
                $params[] = $usuarioId;
                $types .= "i";
            }

            switch ($periodo) {
                case 'hoje':
                    $whereDate = " AND DATE(v.data) = CURDATE()";
                    break;
                case 'mes':
                    $whereDate = " AND YEAR(v.data) = YEAR(CURDATE()) AND MONTH(v.data) = MONTH(CURDATE())";
                    break;
                case 'ano':
                    $whereDate = " AND YEAR(v.data) = YEAR(CURDATE())";
                    break;
            }

            // Total de vendas
            $sql = "SELECT 
                        COUNT(DISTINCT v.id) as total_vendas,
                        SUM(v.total) as receita_total,
                        AVG(v.total) as ticket_medio,
                        SUM(iv.quantidade) as total_itens
                    FROM vendas v 
                    JOIN itens_venda iv ON v.id = iv.venda_id 
                    JOIN produtos p ON iv.produto_id = p.id 
                    WHERE v.status = 'concluida' $whereDate $whereUser";

            $stats = $this->db->selectOne($sql, $types, $params);

            // Vendas por forma de pagamento
            $sql = "SELECT 
                        forma_pagamento,
                        COUNT(*) as quantidade,
                        SUM(total) as valor_total
                    FROM vendas v
                    " . ($whereUser ? "JOIN itens_venda iv ON v.id = iv.venda_id JOIN produtos p ON iv.produto_id = p.id" : "") . "
                    WHERE status = 'concluida' $whereDate $whereUser
                    GROUP BY forma_pagamento";

            $formasPagamento = $this->db->select($sql, $types, $params);

            return [
                'success' => true,
                'data' => [
                    'estatisticas' => $stats ?: [
                        'total_vendas' => 0,
                        'receita_total' => 0,
                        'ticket_medio' => 0,
                        'total_itens' => 0
                    ],
                    'formas_pagamento' => $formasPagamento
                ],
                'message' => ''
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar carrinho antes da venda
     */
    public function validarCarrinho($carrinho)
    {
        try {
            if (empty($carrinho) || !is_array($carrinho)) {
                throw new \Exception('Carrinho vazio ou inválido');
            }

            foreach ($carrinho as $item) {
                if (!isset($item['id'], $item['quantidade'], $item['preco'])) {
                    throw new \Exception('Item do carrinho com dados incompletos');
                }

                if ($item['quantidade'] <= 0) {
                    throw new \Exception('Quantidade deve ser maior que zero');
                }

                if ($item['preco'] <= 0) {
                    throw new \Exception('Preço deve ser maior que zero');
                }

                // Verificar se o produto ainda existe e tem estoque
                $availability = $this->product->checkAvailability($item['id'], $item['quantidade']);
                if (!$availability['success']) {
                    throw new \Exception($availability['message']);
                }
            }

            return [
                'success' => true,
                'data' => ['carrinho_valido' => true],
                'message' => 'Carrinho válido'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Calcular troco
     */
    public function calcularTroco($total, $valorPago)
    {
        $troco = max(0, $valorPago - $total);
        
        return [
            'success' => true,
            'data' => [
                'total' => $total,
                'valor_pago' => $valorPago,
                'troco' => $troco,
                'suficiente' => $valorPago >= $total
            ],
            'message' => ''
        ];
    }
}