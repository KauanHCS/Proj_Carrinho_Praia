<?php

namespace CarrinhoDePreia;

use CarrinhoDePreia\Database;
use CarrinhoDePreia\Sale;
use CarrinhoDePreia\Product;
use CarrinhoDePreia\Stock;

/**
 * Classe Report - Gera dashboards e relatórios
 * Mantém compatibilidade total com as consultas originais
 */
class Report
{
    private $db;
    private $sale;
    private $product;
    private $stock;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->sale = new Sale();
        $this->product = new Product();
        $this->stock = new Stock();
    }

    /**
     * Obter dados para dashboard principal - compatível com template original
     */
    public function getDashboardData($usuarioId)
    {
        try {
            // Vendas do dia - mantém mesma consulta original
            $vendasDia = $this->getVendasDia($usuarioId);
            
            // Lucro estimado do dia - mesma lógica original
            $lucroDia = $this->getLucroDia($usuarioId);
            
            // Total de estoque - mesma consulta original
            $totalEstoque = $this->getTotalEstoque($usuarioId);
            
            // Produtos com estoque baixo - compatível com template
            $produtosEstoqueBaixo = $this->getProdutosEstoqueBaixo($usuarioId);

            return [
                'success' => true,
                'data' => [
                    'vendas_dia' => $vendasDia['data'],
                    'lucro_dia' => $lucroDia['data'],
                    'total_estoque' => $totalEstoque['data'],
                    'produtos_estoque_baixo' => $produtosEstoqueBaixo['data']
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
     * Obter vendas do dia - mantém mesma consulta do template original
     */
    public function getVendasDia($usuarioId)
    {
        try {
            $sql = "SELECT SUM(v.total) as total FROM vendas v 
                    JOIN itens_venda iv ON v.id = iv.venda_id 
                    JOIN produtos p ON iv.produto_id = p.id 
                    WHERE DATE(v.data) = CURDATE() AND p.usuario_id = ?";

            $resultado = $this->db->selectOne($sql, "i", [$usuarioId]);

            // Quantidade de itens vendidos
            $sql = "SELECT SUM(iv.quantidade) as total_itens FROM itens_venda iv 
                    JOIN vendas v ON iv.venda_id = v.id 
                    JOIN produtos p ON iv.produto_id = p.id
                    WHERE DATE(v.data) = CURDATE() AND p.usuario_id = ?";

            $itens = $this->db->selectOne($sql, "i", [$usuarioId]);

            return [
                'success' => true,
                'data' => [
                    'total' => $resultado['total'] ?? 0,
                    'total_itens' => $itens['total_itens'] ?? 0
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
     * Obter lucro do dia - mantém mesmo cálculo do template original
     */
    public function getLucroDia($usuarioId)
    {
        try {
            // Calcular lucro real baseado nos preços de compra e venda - mesma fórmula original
            $sql = "SELECT 
                        SUM((p.preco_venda - COALESCE(p.preco_compra, 0)) * iv.quantidade) as lucro_real
                    FROM vendas v 
                    JOIN itens_venda iv ON v.id = iv.venda_id 
                    JOIN produtos p ON iv.produto_id = p.id 
                    WHERE DATE(v.data) = CURDATE() AND p.usuario_id = ?";

            $resultado = $this->db->selectOne($sql, "i", [$usuarioId]);

            return [
                'success' => true,
                'data' => [
                    'lucro_real' => $resultado['lucro_real'] ?? 0
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
     * Obter total de estoque - mantém mesma consulta do template original
     */
    public function getTotalEstoque($usuarioId)
    {
        try {
            $sql = "SELECT SUM(quantidade) as total FROM produtos WHERE usuario_id = ?";
            $resultado = $this->db->selectOne($sql, "i", [$usuarioId]);

            // Produtos abaixo do limite
            $sql = "SELECT COUNT(*) as count FROM produtos WHERE quantidade <= limite_minimo AND usuario_id = ?";
            $abaixoLimite = $this->db->selectOne($sql, "i", [$usuarioId]);

            return [
                'success' => true,
                'data' => [
                    'total' => $resultado['total'] ?? 0,
                    'produtos_abaixo_limite' => $abaixoLimite['count'] ?? 0
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
     * Obter produtos com estoque baixo - compatível com template original
     */
    public function getProdutosEstoqueBaixo($usuarioId)
    {
        return $this->stock->getProdutosEstoqueBaixo($usuarioId);
    }

    /**
     * Obter produtos mais vendidos - mantém mesma consulta do template original
     */
    public function getProdutosMaisVendidos($usuarioId, $limit = 5, $periodo = 30)
    {
        try {
            // Mesma consulta do template original
            $sql = "SELECT p.nome, p.categoria, SUM(iv.quantidade) as total_vendido, 
                           COUNT(DISTINCT iv.venda_id) as num_vendas
                    FROM itens_venda iv 
                    JOIN produtos p ON iv.produto_id = p.id 
                    WHERE p.usuario_id = ? 
                    GROUP BY p.id, p.nome, p.categoria 
                    ORDER BY total_vendido DESC 
                    LIMIT ?";

            $produtos = $this->db->select($sql, "ii", [$usuarioId, $limit]);

            // Formatar dados como no template original
            $produtosFormatados = [];
            foreach ($produtos as $produto) {
                $produtosFormatados[] = [
                    'nome' => $produto['nome'],
                    'categoria' => $produto['categoria'],
                    'total_vendido' => (int)$produto['total_vendido'],
                    'num_vendas' => (int)$produto['num_vendas']
                ];
            }

            return [
                'success' => true,
                'data' => $produtosFormatados,
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
     * Obter dados para gráfico de vendas - compatível com JavaScript original
     */
    public function getDadosGraficoVendas($usuarioId, $periodo = 30)
    {
        return $this->product->getMostSold($usuarioId, 10, $periodo);
    }

    /**
     * Relatório de vendas por período
     */
    public function getRelatorioVendas($usuarioId, $dataInicio = null, $dataFim = null)
    {
        try {
            $sql = "SELECT 
                        DATE(v.data) as data_venda,
                        COUNT(v.id) as total_vendas,
                        SUM(v.total) as receita_total,
                        SUM(v.desconto) as desconto_total,
                        AVG(v.total) as ticket_medio,
                        SUM(CASE WHEN v.forma_pagamento = 'dinheiro' THEN v.total ELSE 0 END) as dinheiro,
                        SUM(CASE WHEN v.forma_pagamento = 'pix' THEN v.total ELSE 0 END) as pix,
                        SUM(CASE WHEN v.forma_pagamento = 'cartao' THEN v.total ELSE 0 END) as cartao,
                        SUM(CASE WHEN v.forma_pagamento = 'multiplo' THEN v.total ELSE 0 END) as multiplo
                    FROM vendas v
                    JOIN itens_venda iv ON v.id = iv.venda_id
                    JOIN produtos p ON iv.produto_id = p.id
                    WHERE v.status = 'concluida' AND p.usuario_id = ?";

            $params = [$usuarioId];
            $types = "i";

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

            $sql .= " GROUP BY DATE(v.data) ORDER BY data_venda DESC";

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
     * Relatório de produtos por categoria
     */
    public function getRelatorioProdutosPorCategoria($usuarioId)
    {
        try {
            $sql = "SELECT 
                        p.categoria,
                        COUNT(*) as total_produtos,
                        SUM(p.quantidade) as total_estoque,
                        AVG(p.quantidade) as media_estoque,
                        SUM(p.quantidade * p.preco_venda) as valor_total,
                        COUNT(CASE WHEN p.quantidade <= p.limite_minimo THEN 1 END) as produtos_estoque_baixo
                    FROM produtos p
                    WHERE p.usuario_id = ? AND p.ativo = 1
                    GROUP BY p.categoria
                    ORDER BY total_produtos DESC";

            $categorias = $this->db->select($sql, "i", [$usuarioId]);

            return [
                'success' => true,
                'data' => $categorias,
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
     * Exportar dados de vendas para CSV - mantém mesma estrutura
     */
    public function exportarVendas($usuarioId, $dataInicio = null, $dataFim = null)
    {
        try {
            $sql = "SELECT 
                        v.id,
                        v.data,
                        v.forma_pagamento,
                        v.total,
                        v.valor_pago,
                        v.troco,
                        v.desconto,
                        u.nome as vendedor,
                        GROUP_CONCAT(CONCAT(p.nome, ' (', iv.quantidade, 'x)') SEPARATOR '; ') as produtos
                    FROM vendas v
                    LEFT JOIN usuarios u ON v.usuario_id = u.id
                    JOIN itens_venda iv ON v.id = iv.venda_id
                    JOIN produtos p ON iv.produto_id = p.id
                    WHERE p.usuario_id = ?";

            $params = [$usuarioId];
            $types = "i";

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

            $sql .= " GROUP BY v.id ORDER BY v.data DESC";

            $vendas = $this->db->select($sql, $types, $params);

            return [
                'success' => true,
                'data' => $vendas,
                'message' => '',
                'filename' => 'vendas_' . date('Y-m-d') . '.csv'
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
     * Exportar dados de produtos para CSV - com análise de lucro
     */
    public function exportarProdutos($usuarioId)
    {
        try {
            $sql = "SELECT 
                        p.id,
                        p.nome,
                        p.categoria,
                        p.preco_compra,
                        p.preco_venda,
                        p.quantidade,
                        p.limite_minimo,
                        p.validade,
                        p.observacoes,
                        p.data_cadastro,
                        (p.preco_venda - COALESCE(p.preco_compra, 0)) as margem_lucro,
                        CASE 
                            WHEN p.preco_compra > 0 THEN 
                                ROUND(((p.preco_venda - p.preco_compra) / p.preco_compra) * 100, 2)
                            ELSE 0 
                        END as percentual_lucro,
                        (p.quantidade * p.preco_venda) as valor_estoque,
                        COALESCE(vendas.total_vendido, 0) as total_vendido,
                        CASE 
                            WHEN p.quantidade <= p.limite_minimo THEN 'ESTOQUE BAIXO'
                            WHEN p.quantidade = 0 THEN 'SEM ESTOQUE'
                            ELSE 'OK'
                        END as status_estoque
                    FROM produtos p
                    LEFT JOIN (
                        SELECT 
                            iv.produto_id,
                            SUM(iv.quantidade) as total_vendido
                        FROM itens_venda iv
                        JOIN vendas v ON iv.venda_id = v.id
                        WHERE v.status = 'concluida'
                        GROUP BY iv.produto_id
                    ) vendas ON p.id = vendas.produto_id
                    WHERE p.usuario_id = ?
                    ORDER BY p.nome";

            $produtos = $this->db->select($sql, "i", [$usuarioId]);

            return [
                'success' => true,
                'data' => $produtos,
                'message' => '',
                'filename' => 'produtos_' . date('Y-m-d') . '.csv'
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
     * Relatório de lucratividade detalhado
     */
    public function getRelatorioLucratividade($usuarioId, $periodo = 30)
    {
        try {
            $sql = "SELECT 
                        p.nome,
                        p.categoria,
                        p.preco_compra,
                        p.preco_venda,
                        SUM(iv.quantidade) as total_vendido,
                        SUM(iv.quantidade * p.preco_compra) as custo_total,
                        SUM(iv.quantidade * p.preco_venda) as receita_total,
                        SUM(iv.quantidade * (p.preco_venda - COALESCE(p.preco_compra, 0))) as lucro_total,
                        CASE 
                            WHEN p.preco_compra > 0 THEN 
                                ROUND(((p.preco_venda - p.preco_compra) / p.preco_compra) * 100, 2)
                            ELSE 0 
                        END as margem_percentual
                    FROM produtos p
                    JOIN itens_venda iv ON p.id = iv.produto_id
                    JOIN vendas v ON iv.venda_id = v.id
                    WHERE p.usuario_id = ? 
                    AND v.status = 'concluida'
                    AND v.data >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                    GROUP BY p.id, p.nome, p.categoria, p.preco_compra, p.preco_venda
                    ORDER BY lucro_total DESC";

            $produtos = $this->db->select($sql, "ii", [$usuarioId, $periodo]);

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
     * Análise de performance de vendas por hora
     */
    public function getPerformanceVendasPorHora($usuarioId, $data = null)
    {
        try {
            if (!$data) {
                $data = date('Y-m-d');
            }

            $sql = "SELECT 
                        HOUR(v.data) as hora,
                        COUNT(v.id) as total_vendas,
                        SUM(v.total) as receita_total,
                        AVG(v.total) as ticket_medio,
                        SUM(iv.quantidade) as total_itens
                    FROM vendas v
                    JOIN itens_venda iv ON v.id = iv.venda_id
                    JOIN produtos p ON iv.produto_id = p.id
                    WHERE DATE(v.data) = ? 
                    AND p.usuario_id = ?
                    AND v.status = 'concluida'
                    GROUP BY HOUR(v.data)
                    ORDER BY hora";

            $vendas = $this->db->select($sql, "si", [$data, $usuarioId]);

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
     * Gerar dados para backup completo
     */
    public function gerarDadosBackup($usuarioId)
    {
        try {
            $backup = [
                'usuario_id' => $usuarioId,
                'data_backup' => date('Y-m-d H:i:s'),
                'produtos' => [],
                'vendas' => [],
                'movimentacoes' => []
            ];

            // Exportar produtos
            $produtos = $this->exportarProdutos($usuarioId);
            $backup['produtos'] = $produtos['data'];

            // Exportar vendas (últimos 90 dias)
            $dataInicio = date('Y-m-d', strtotime('-90 days'));
            $vendas = $this->exportarVendas($usuarioId, $dataInicio);
            $backup['vendas'] = $vendas['data'];

            // Exportar movimentações (últimos 30 dias)
            $movimentacoes = $this->stock->getHistoricoMovimentacoes($usuarioId, null, 1000);
            $backup['movimentacoes'] = $movimentacoes['data'];

            return [
                'success' => true,
                'data' => $backup,
                'message' => '',
                'filename' => 'backup_' . $usuarioId . '_' . date('Y-m-d') . '.json'
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
     * Obter KPIs (Key Performance Indicators) do negócio
     */
    public function getKPIs($usuarioId, $periodo = 'mes')
    {
        try {
            $whereDate = "";
            switch ($periodo) {
                case 'hoje':
                    $whereDate = "AND DATE(v.data) = CURDATE()";
                    break;
                case 'semana':
                    $whereDate = "AND v.data >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'mes':
                    $whereDate = "AND YEAR(v.data) = YEAR(CURDATE()) AND MONTH(v.data) = MONTH(CURDATE())";
                    break;
                case 'ano':
                    $whereDate = "AND YEAR(v.data) = YEAR(CURDATE())";
                    break;
            }

            // KPIs principais
            $sql = "SELECT 
                        COUNT(DISTINCT v.id) as total_vendas,
                        SUM(v.total) as receita_total,
                        AVG(v.total) as ticket_medio,
                        SUM(iv.quantidade) as total_itens_vendidos,
                        COUNT(DISTINCT p.id) as produtos_diferentes_vendidos,
                        SUM((p.preco_venda - COALESCE(p.preco_compra, 0)) * iv.quantidade) as lucro_total
                    FROM vendas v
                    JOIN itens_venda iv ON v.id = iv.venda_id
                    JOIN produtos p ON iv.produto_id = p.id
                    WHERE v.status = 'concluida' 
                    AND p.usuario_id = ? $whereDate";

            $kpis = $this->db->selectOne($sql, "i", [$usuarioId]);

            return [
                'success' => true,
                'data' => $kpis ?: [
                    'total_vendas' => 0,
                    'receita_total' => 0,
                    'ticket_medio' => 0,
                    'total_itens_vendidos' => 0,
                    'produtos_diferentes_vendidos' => 0,
                    'lucro_total' => 0
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
}