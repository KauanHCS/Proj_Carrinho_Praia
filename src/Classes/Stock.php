<?php

namespace CarrinhoDePreia;

use CarrinhoDePreia\Database;
use CarrinhoDePreia\Product;

/**
 * Classe Stock - Controla movimentações e estoque
 * Mantém compatibilidade total com os triggers existentes
 */
class Stock
{
    private $db;
    private $product;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->product = new Product();
    }

    /**
     * Reabastecer estoque - mantém mesma lógica original
     */
    public function reabastecer($produtoId, $quantidade, $usuarioId = null)
    {
        try {
            if (!$this->validateQuantity($quantidade)) {
                throw new \Exception('Quantidade deve ser um número inteiro positivo');
            }

            // Verificar se o produto existe
            $sql = "SELECT id, nome, quantidade FROM produtos WHERE id = ?";
            $params = [$produtoId];
            $types = "i";

            // Se especificado usuário, verificar se o produto pertence a ele
            if ($usuarioId) {
                $sql .= " AND usuario_id = ?";
                $params[] = $usuarioId;
                $types .= "i";
            }

            $produto = $this->db->selectOne($sql, $types, $params);

            if (!$produto) {
                throw new \Exception('Produto não encontrado ou não pertence a você');
            }

            $estoqueAnterior = $produto['quantidade'];
            $estoqueAtual = $estoqueAnterior + $quantidade;

            // Atualizar estoque - O trigger tr_produto_estoque_updated automaticamente
            // registrará a movimentação quando o estoque for atualizado
            $sql = "UPDATE produtos SET quantidade = ? WHERE id = ?";
            $this->db->execute($sql, "ii", [$estoqueAtual, $produtoId]);

            return [
                'success' => true,
                'data' => [
                    'produto_id' => $produtoId,
                    'nome' => $produto['nome'],
                    'quantidade_antiga' => $estoqueAnterior,
                    'quantidade_nova' => $estoqueAtual,
                    'quantidade_adicionada' => $quantidade
                ],
                'message' => 'Estoque reabastecido com sucesso'
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
     * Ajustar estoque manualmente
     */
    public function ajustarEstoque($produtoId, $novaQuantidade, $motivo, $usuarioId = null)
    {
        try {
            if (!$this->validateQuantity($novaQuantidade, true)) {
                throw new \Exception('Quantidade deve ser um número inteiro não negativo');
            }

            if (empty($motivo)) {
                throw new \Exception('Motivo do ajuste é obrigatório');
            }

            // Verificar se o produto existe
            $sql = "SELECT id, nome, quantidade FROM produtos WHERE id = ?";
            $params = [$produtoId];
            $types = "i";

            if ($usuarioId) {
                $sql .= " AND usuario_id = ?";
                $params[] = $usuarioId;
                $types .= "i";
            }

            $produto = $this->db->selectOne($sql, $types, $params);

            if (!$produto) {
                throw new \Exception('Produto não encontrado ou não pertence a você');
            }

            $estoqueAnterior = $produto['quantidade'];
            
            // Atualizar estoque
            $sql = "UPDATE produtos SET quantidade = ? WHERE id = ?";
            $this->db->execute($sql, "ii", [$novaQuantidade, $produtoId]);

            // Registrar movimentação manual (além do trigger automático)
            $tipoMovimentacao = ($novaQuantidade > $estoqueAnterior) ? 'entrada' : 'saida';
            $quantidadeMovimentada = abs($novaQuantidade - $estoqueAnterior);
            
            $sql = "INSERT INTO movimentacoes (produto_id, tipo, quantidade, quantidade_anterior, quantidade_atual, descricao, usuario_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $this->db->insert($sql, "isiissi", [
                $produtoId,
                'ajuste',
                $quantidadeMovimentada,
                $estoqueAnterior,
                $novaQuantidade,
                'Ajuste manual: ' . $motivo,
                $usuarioId
            ]);

            return [
                'success' => true,
                'data' => [
                    'produto_id' => $produtoId,
                    'nome' => $produto['nome'],
                    'quantidade_anterior' => $estoqueAnterior,
                    'quantidade_nova' => $novaQuantidade,
                    'diferenca' => $novaQuantidade - $estoqueAnterior,
                    'motivo' => $motivo
                ],
                'message' => 'Estoque ajustado com sucesso'
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
     * Registrar perda de estoque
     */
    public function registrarPerda($produtoId, $quantidade, $motivo, $usuarioId = null)
    {
        try {
            if (!$this->validateQuantity($quantidade)) {
                throw new \Exception('Quantidade deve ser um número inteiro positivo');
            }

            if (empty($motivo)) {
                throw new \Exception('Motivo da perda é obrigatório');
            }

            // Verificar se o produto existe
            $sql = "SELECT id, nome, quantidade FROM produtos WHERE id = ?";
            $params = [$produtoId];
            $types = "i";

            if ($usuarioId) {
                $sql .= " AND usuario_id = ?";
                $params[] = $usuarioId;
                $types .= "i";
            }

            $produto = $this->db->selectOne($sql, $types, $params);

            if (!$produto) {
                throw new \Exception('Produto não encontrado ou não pertence a você');
            }

            $estoqueAnterior = $produto['quantidade'];
            
            if ($estoqueAnterior < $quantidade) {
                throw new \Exception('Quantidade da perda é maior que o estoque disponível');
            }

            $estoqueAtual = $estoqueAnterior - $quantidade;

            // Atualizar estoque
            $sql = "UPDATE produtos SET quantidade = ? WHERE id = ?";
            $this->db->execute($sql, "ii", [$estoqueAtual, $produtoId]);

            // Registrar movimentação de perda
            $sql = "INSERT INTO movimentacoes (produto_id, tipo, quantidade, quantidade_anterior, quantidade_atual, descricao, usuario_id) 
                    VALUES (?, 'perda', ?, ?, ?, ?, ?)";
            
            $this->db->insert($sql, "iiissi", [
                $produtoId,
                $quantidade,
                $estoqueAnterior,
                $estoqueAtual,
                'Perda: ' . $motivo,
                $usuarioId
            ]);

            return [
                'success' => true,
                'data' => [
                    'produto_id' => $produtoId,
                    'nome' => $produto['nome'],
                    'quantidade_perdida' => $quantidade,
                    'estoque_anterior' => $estoqueAnterior,
                    'estoque_atual' => $estoqueAtual,
                    'motivo' => $motivo
                ],
                'message' => 'Perda registrada com sucesso'
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
     * Obter histórico de movimentações
     */
    public function getHistoricoMovimentacoes($usuarioId = null, $produtoId = null, $limite = 50)
    {
        try {
            $sql = "SELECT m.*, p.nome as produto_nome 
                    FROM movimentacoes m 
                    JOIN produtos p ON m.produto_id = p.id 
                    WHERE 1=1";
            
            $params = [];
            $types = "";

            if ($usuarioId) {
                $sql .= " AND p.usuario_id = ?";
                $params[] = $usuarioId;
                $types .= "i";
            }

            if ($produtoId) {
                $sql .= " AND m.produto_id = ?";
                $params[] = $produtoId;
                $types .= "i";
            }

            $sql .= " ORDER BY m.data DESC LIMIT ?";
            $params[] = $limite;
            $types .= "i";

            $movimentacoes = $this->db->select($sql, $types, $params);

            return [
                'success' => true,
                'data' => $movimentacoes,
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
     * Obter produtos com estoque baixo - compatível com template original
     */
    public function getProdutosEstoqueBaixo($usuarioId)
    {
        try {
            $sql = "SELECT id, nome, quantidade, limite_minimo, categoria 
                    FROM produtos 
                    WHERE quantidade <= limite_minimo 
                    AND quantidade >= 0 
                    AND usuario_id = ? 
                    ORDER BY quantidade ASC, nome ASC";

            $produtos = $this->db->select($sql, "i", [$usuarioId]);

            return [
                'success' => true,
                'data' => $produtos,
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
     * Obter primeiro produto com estoque baixo (para alertas)
     * Mantém compatibilidade com função original
     */
    public function getProximoAlertaEstoque($usuarioId)
    {
        try {
            $sql = "SELECT id, nome, quantidade, limite_minimo 
                    FROM produtos 
                    WHERE quantidade <= limite_minimo 
                    AND ativo = 1 
                    AND usuario_id = ? 
                    ORDER BY quantidade ASC, nome ASC 
                    LIMIT 1";

            $produto = $this->db->selectOne($sql, "i", [$usuarioId]);

            if ($produto) {
                return [
                    'success' => true,
                    'data' => ['produto' => $produto],
                    'message' => ''
                ];
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Nenhum produto com estoque baixo'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter produtos próximos do vencimento
     */
    public function getProdutosProximosVencimento($usuarioId, $dias = 7)
    {
        try {
            $sql = "SELECT id, nome, quantidade, validade, 
                           DATEDIFF(validade, CURDATE()) as dias_para_vencer
                    FROM produtos 
                    WHERE validade IS NOT NULL 
                    AND DATEDIFF(validade, CURDATE()) <= ? 
                    AND DATEDIFF(validade, CURDATE()) >= 0
                    AND usuario_id = ?
                    ORDER BY validade ASC";

            $produtos = $this->db->select($sql, "ii", [$dias, $usuarioId]);

            return [
                'success' => true,
                'data' => $produtos,
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
     * Obter estatísticas de estoque
     */
    public function getEstatisticasEstoque($usuarioId)
    {
        try {
            // Estatísticas gerais
            $sql = "SELECT 
                        COUNT(*) as total_produtos,
                        SUM(quantidade) as total_itens,
                        COUNT(CASE WHEN quantidade <= limite_minimo THEN 1 END) as produtos_estoque_baixo,
                        COUNT(CASE WHEN quantidade = 0 THEN 1 END) as produtos_sem_estoque,
                        AVG(quantidade) as media_estoque
                    FROM produtos 
                    WHERE usuario_id = ? AND ativo = 1";

            $stats = $this->db->selectOne($sql, "i", [$usuarioId]);

            // Produtos por categoria
            $sql = "SELECT 
                        categoria,
                        COUNT(*) as quantidade_produtos,
                        SUM(quantidade) as total_itens
                    FROM produtos 
                    WHERE usuario_id = ? AND ativo = 1
                    GROUP BY categoria 
                    ORDER BY total_itens DESC";

            $categorias = $this->db->select($sql, "i", [$usuarioId]);

            // Últimas movimentações
            $ultimasMovimentacoes = $this->getHistoricoMovimentacoes($usuarioId, null, 10);

            return [
                'success' => true,
                'data' => [
                    'estatisticas_gerais' => $stats ?: [
                        'total_produtos' => 0,
                        'total_itens' => 0,
                        'produtos_estoque_baixo' => 0,
                        'produtos_sem_estoque' => 0,
                        'media_estoque' => 0
                    ],
                    'produtos_por_categoria' => $categorias,
                    'ultimas_movimentacoes' => $ultimasMovimentacoes['data'] ?? []
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
     * Calcular valor total do estoque
     */
    public function getValorTotalEstoque($usuarioId)
    {
        try {
            $sql = "SELECT 
                        SUM(quantidade * preco_compra) as valor_compra,
                        SUM(quantidade * preco_venda) as valor_venda,
                        SUM(quantidade * (preco_venda - COALESCE(preco_compra, 0))) as lucro_potencial
                    FROM produtos 
                    WHERE usuario_id = ? AND ativo = 1";

            $valores = $this->db->selectOne($sql, "i", [$usuarioId]);

            return [
                'success' => true,
                'data' => $valores ?: [
                    'valor_compra' => 0,
                    'valor_venda' => 0,
                    'lucro_potencial' => 0
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
     * Transferir estoque entre produtos (se aplicável)
     */
    public function transferirEstoque($produtoOrigemId, $produtoDestinoId, $quantidade, $motivo, $usuarioId)
    {
        try {
            if (!$this->validateQuantity($quantidade)) {
                throw new \Exception('Quantidade deve ser um número inteiro positivo');
            }

            // Iniciar transação
            $conn = $this->db->getConnection();
            $conn->begin_transaction();

            // Verificar produto origem
            $produtoOrigem = $this->db->selectOne(
                "SELECT id, nome, quantidade FROM produtos WHERE id = ? AND usuario_id = ?",
                "ii",
                [$produtoOrigemId, $usuarioId]
            );

            if (!$produtoOrigem) {
                throw new \Exception('Produto de origem não encontrado');
            }

            if ($produtoOrigem['quantidade'] < $quantidade) {
                throw new \Exception('Quantidade insuficiente no produto de origem');
            }

            // Verificar produto destino
            $produtoDestino = $this->db->selectOne(
                "SELECT id, nome, quantidade FROM produtos WHERE id = ? AND usuario_id = ?",
                "ii",
                [$produtoDestinoId, $usuarioId]
            );

            if (!$produtoDestino) {
                throw new \Exception('Produto de destino não encontrado');
            }

            // Atualizar estoques
            $this->db->execute(
                "UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?",
                "ii",
                [$quantidade, $produtoOrigemId]
            );

            $this->db->execute(
                "UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?",
                "ii",
                [$quantidade, $produtoDestinoId]
            );

            // Registrar movimentações
            $descricaoSaida = "Transferência para: " . $produtoDestino['nome'] . " - " . $motivo;
            $descricaoEntrada = "Transferência de: " . $produtoOrigem['nome'] . " - " . $motivo;

            $this->db->insert(
                "INSERT INTO movimentacoes (produto_id, tipo, quantidade, quantidade_anterior, quantidade_atual, descricao, usuario_id) 
                 SELECT ?, 'saida', ?, quantidade + ?, quantidade, ?, ? FROM produtos WHERE id = ?",
                "iiissi",
                [$produtoOrigemId, $quantidade, $quantidade, $descricaoSaida, $usuarioId, $produtoOrigemId]
            );

            $this->db->insert(
                "INSERT INTO movimentacoes (produto_id, tipo, quantidade, quantidade_anterior, quantidade_atual, descricao, usuario_id) 
                 SELECT ?, 'entrada', ?, quantidade - ?, quantidade, ?, ? FROM produtos WHERE id = ?",
                "iiissi",
                [$produtoDestinoId, $quantidade, $quantidade, $descricaoEntrada, $usuarioId, $produtoDestinoId]
            );

            $conn->commit();

            return [
                'success' => true,
                'data' => [
                    'produto_origem' => $produtoOrigem['nome'],
                    'produto_destino' => $produtoDestino['nome'],
                    'quantidade_transferida' => $quantidade,
                    'motivo' => $motivo
                ],
                'message' => 'Transferência realizada com sucesso'
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
     * Validar quantidade - permite zero se especificado
     */
    private function validateQuantity($quantity, $allowZero = false)
    {
        if (!is_numeric($quantity)) {
            return false;
        }
        
        $quantity = intval($quantity);
        
        if ($allowZero) {
            return $quantity >= 0 && intval($quantity) == $quantity;
        } else {
            return $quantity > 0 && intval($quantity) == $quantity;
        }
    }
}