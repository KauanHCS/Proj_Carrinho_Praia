<?php

namespace CarrinhoDePreia\Services;

use CarrinhoDePreia\Repositories\SaleRepository;
use CarrinhoDePreia\Repositories\ProductRepository;
use CarrinhoDePreia\Logger;
use CarrinhoDePreia\Cache;
use CarrinhoDePreia\Database;

/**
 * Class SaleService
 * 
 * Camada de serviço para gerenciamento de vendas.
 * Orquestra transações complexas e relatórios.
 * 
 * @package CarrinhoDePreia\Services
 */
class SaleService
{
    private SaleRepository $repository;
    private ProductRepository $productRepository;
    private Database $db;
    private ProductService $productService;
    private const CACHE_TTL = 1800; // 30 minutos

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->repository = new SaleRepository();
        $this->productRepository = new ProductRepository();
        $this->db = Database::getInstance();
        $this->productService = new ProductService();
    }

    /**
     * Cria uma nova venda com transação
     * 
     * @param array $saleData Dados da venda
     * @param array $items Itens da venda
     * @return array Resposta com sucesso/erro
     */
    public function createSale(array $saleData, array $items): array
    {
        try {
            // Validar dados básicos
            if (empty($saleData['usuario_id']) || empty($items)) {
                return [
                    'success' => false,
                    'message' => 'Dados insuficientes para criar venda'
                ];
            }

            // Calcular valor total e validar estoque
            $valorTotal = 0;
            foreach ($items as $item) {
                $product = $this->productRepository->findById($item['produto_id']);
                
                if (!$product) {
                    return [
                        'success' => false,
                        'message' => "Produto ID {$item['produto_id']} não encontrado"
                    ];
                }

                if ((int)$product['quantidade'] < $item['quantidade']) {
                    return [
                        'success' => false,
                        'message' => "Estoque insuficiente para produto: {$product['nome']}"
                    ];
                }

                $valorTotal += (float)$product['preco'] * $item['quantidade'];
            }

            // Adicionar timestamp se não fornecido
            if (!isset($saleData['data_venda'])) {
                $saleData['data_venda'] = date('Y-m-d H:i:s');
            }

            // Iniciar transação
            $this->db->beginTransaction();

            try {
                // Criar venda
                $saleData['valor_total'] = $valorTotal;
                $saleData['status'] = $saleData['status'] ?? 'concluida';
                
                $saleId = $this->repository->create($saleData);

                if (!$saleId) {
                    throw new \Exception('Erro ao criar venda');
                }

                // Criar itens da venda e atualizar estoque
                foreach ($items as $item) {
                    $product = $this->productRepository->findById($item['produto_id']);
                    
                    // Inserir item
                    $itemData = [
                        'venda_id' => $saleId,
                        'produto_id' => $item['produto_id'],
                        'quantidade' => $item['quantidade'],
                        'preco_unitario' => $product['preco'],
                        'subtotal' => (float)$product['preco'] * $item['quantidade']
                    ];

                    $query = "INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario, subtotal) 
                             VALUES (:venda_id, :produto_id, :quantidade, :preco_unitario, :subtotal)";
                    
                    $this->db->insert($query, $itemData);

                    // Atualizar estoque
                    $this->productRepository->decrementStock($item['produto_id'], $item['quantidade']);
                }

                // Commit da transação
                $this->db->commit();

                // Invalidar cache
                Cache::forgetPattern('sales_*');
                Cache::forgetPattern('products_*');
                Cache::forget("dashboard_sales_{$saleData['usuario_id']}");
                Cache::forget("dashboard_products_{$saleData['usuario_id']}");

                Logger::info('Venda criada com sucesso', [
                    'sale_id' => $saleId,
                    'user_id' => $saleData['usuario_id'],
                    'total_value' => $valorTotal,
                    'items_count' => count($items)
                ]);

                return [
                    'success' => true,
                    'message' => 'Venda registrada com sucesso',
                    'data' => [
                        'id' => $saleId,
                        'valor_total' => $valorTotal
                    ]
                ];
            } catch (\Exception $e) {
                // Rollback em caso de erro
                $this->db->rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            Logger::error('Erro ao criar venda', [
                'error' => $e->getMessage(),
                'sale_data' => $saleData,
                'items' => $items
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao criar venda: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancela uma venda e restaura estoque
     * 
     * @param int $saleId ID da venda
     * @param int $userId ID do usuário (verificação)
     * @return array Resposta com sucesso/erro
     */
    public function cancelSale(int $saleId, int $userId): array
    {
        try {
            // Buscar venda com itens
            $sale = $this->repository->findWithItems($saleId);

            if (!$sale) {
                return [
                    'success' => false,
                    'message' => 'Venda não encontrada'
                ];
            }

            if ((int)$sale['usuario_id'] !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Você não tem permissão para cancelar esta venda'
                ];
            }

            if ($sale['status'] === 'cancelada') {
                return [
                    'success' => false,
                    'message' => 'Esta venda já está cancelada'
                ];
            }

            // Iniciar transação
            $this->db->beginTransaction();

            try {
                // Atualizar status da venda
                $this->repository->update($saleId, ['status' => 'cancelada']);

                // Restaurar estoque dos produtos
                foreach ($sale['itens'] as $item) {
                    $this->productRepository->incrementStock(
                        $item['produto_id'],
                        $item['quantidade']
                    );
                }

                // Commit
                $this->db->commit();

                // Invalidar cache
                Cache::forgetPattern('sales_*');
                Cache::forgetPattern('products_*');
                Cache::forget("dashboard_sales_{$userId}");

                Logger::info('Venda cancelada', [
                    'sale_id' => $saleId,
                    'user_id' => $userId,
                    'items_count' => count($sale['itens'])
                ]);

                return [
                    'success' => true,
                    'message' => 'Venda cancelada e estoque restaurado'
                ];
            } catch (\Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            Logger::error('Erro ao cancelar venda', [
                'error' => $e->getMessage(),
                'sale_id' => $saleId
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao cancelar venda: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Busca venda por ID com itens
     * 
     * @param int $saleId ID da venda
     * @return array|null Dados da venda
     */
    public function getSale(int $saleId): ?array
    {
        return Cache::remember("sale_with_items_{$saleId}", function() use ($saleId) {
            return $this->repository->findWithItems($saleId);
        }, self::CACHE_TTL);
    }

    /**
     * Lista vendas do usuário
     * 
     * @param int $userId ID do usuário
     * @param array $options Opções de paginação
     * @return array Lista de vendas
     */
    public function getUserSales(int $userId, array $options = []): array
    {
        $cacheKey = "sales_user_{$userId}_" . md5(json_encode($options));
        
        return Cache::remember($cacheKey, function() use ($userId, $options) {
            return $this->repository->findByUser($userId, $options);
        }, self::CACHE_TTL);
    }

    /**
     * Gera relatório de vendas por período
     * 
     * @param int $userId ID do usuário
     * @param string $dataInicio Data inicial (Y-m-d)
     * @param string $dataFim Data final (Y-m-d)
     * @return array Relatório completo
     */
    public function getSalesReport(int $userId, string $dataInicio, string $dataFim): array
    {
        $cacheKey = "sales_report_{$userId}_{$dataInicio}_{$dataFim}";
        
        return Cache::remember($cacheKey, function() use ($userId, $dataInicio, $dataFim) {
            $report = $this->repository->getSalesReport($dataInicio, $dataFim, $userId);
            
            // Adicionar análises extras
            $report['analises'] = [
                'crescimento' => $this->calculateGrowth($userId, $dataInicio, $dataFim),
                'produtos_mais_vendidos' => $this->getTopSellingProducts($userId, $dataInicio, $dataFim)
            ];
            
            return $report;
        }, self::CACHE_TTL);
    }

    /**
     * Retorna dashboard de vendas
     * 
     * @param int $userId ID do usuário
     * @return array Dashboard com métricas
     */
    public function getSalesDashboard(int $userId): array
    {
        return Cache::remember("dashboard_sales_{$userId}", function() use ($userId) {
            $metrics = $this->repository->getDashboardMetrics($userId);
            
            // Adicionar comparação com mês anterior
            $mesAnterior = date('Y-m-01', strtotime('-1 month'));
            $fimMesAnterior = date('Y-m-t', strtotime('-1 month'));
            
            $metrics['mes_anterior'] = [
                'vendas' => $this->repository->countSalesByPeriod($mesAnterior, $fimMesAnterior, $userId),
                'receita' => $this->repository->getTotalSalesByPeriod($mesAnterior, $fimMesAnterior, $userId)
            ];
            
            // Calcular crescimento
            if ($metrics['mes_anterior']['vendas'] > 0) {
                $metrics['crescimento_vendas'] = (($metrics['vendas_mes'] - $metrics['mes_anterior']['vendas']) / $metrics['mes_anterior']['vendas']) * 100;
            } else {
                $metrics['crescimento_vendas'] = 0;
            }
            
            if ($metrics['mes_anterior']['receita'] > 0) {
                $metrics['crescimento_receita'] = (($metrics['receita_mes'] - $metrics['mes_anterior']['receita']) / $metrics['mes_anterior']['receita']) * 100;
            } else {
                $metrics['crescimento_receita'] = 0;
            }
            
            return $metrics;
        }, self::CACHE_TTL);
    }

    /**
     * Calcula taxa de crescimento
     * 
     * @param int $userId ID do usuário
     * @param string $dataInicio Data inicial
     * @param string $dataFim Data final
     * @return array Dados de crescimento
     */
    private function calculateGrowth(int $userId, string $dataInicio, string $dataFim): array
    {
        // Calcular período anterior de mesmo tamanho
        $dias = (strtotime($dataFim) - strtotime($dataInicio)) / 86400;
        $periodoAnteriorFim = date('Y-m-d', strtotime($dataInicio) - 86400);
        $periodoAnteriorInicio = date('Y-m-d', strtotime($periodoAnteriorFim) - ($dias * 86400));
        
        $vendasAtual = $this->repository->countSalesByPeriod($dataInicio, $dataFim, $userId);
        $receitaAtual = $this->repository->getTotalSalesByPeriod($dataInicio, $dataFim, $userId);
        
        $vendasAnterior = $this->repository->countSalesByPeriod($periodoAnteriorInicio, $periodoAnteriorFim, $userId);
        $receitaAnterior = $this->repository->getTotalSalesByPeriod($periodoAnteriorInicio, $periodoAnteriorFim, $userId);
        
        return [
            'vendas' => [
                'atual' => $vendasAtual,
                'anterior' => $vendasAnterior,
                'percentual' => $vendasAnterior > 0 ? (($vendasAtual - $vendasAnterior) / $vendasAnterior) * 100 : 0
            ],
            'receita' => [
                'atual' => $receitaAtual,
                'anterior' => $receitaAnterior,
                'percentual' => $receitaAnterior > 0 ? (($receitaAtual - $receitaAnterior) / $receitaAnterior) * 100 : 0
            ]
        ];
    }

    /**
     * Retorna produtos mais vendidos no período
     * 
     * @param int $userId ID do usuário
     * @param string $dataInicio Data inicial
     * @param string $dataFim Data final
     * @param int $limit Limite de produtos
     * @return array Produtos mais vendidos
     */
    private function getTopSellingProducts(int $userId, string $dataInicio, string $dataFim, int $limit = 10): array
    {
        try {
            $query = "SELECT 
                        p.id,
                        p.nome,
                        p.categoria,
                        SUM(iv.quantidade) as total_vendido,
                        SUM(iv.subtotal) as receita_total,
                        COUNT(DISTINCT iv.venda_id) as numero_vendas
                     FROM produtos p
                     INNER JOIN itens_venda iv ON p.id = iv.produto_id
                     INNER JOIN vendas v ON iv.venda_id = v.id
                     WHERE v.usuario_id = :usuario_id
                     AND v.status != 'cancelada'
                     AND DATE(v.data_venda) BETWEEN :data_inicio AND :data_fim
                     GROUP BY p.id
                     ORDER BY total_vendido DESC
                     LIMIT :limit";
            
            return $this->db->select($query, [
                'usuario_id' => $userId,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'limit' => $limit
            ]);
        } catch (\Exception $e) {
            Logger::error('Erro ao buscar produtos mais vendidos', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Valida dados de venda
     * 
     * @param array $saleData Dados da venda
     * @return array Erros de validação (vazio se OK)
     */
    private function validateSaleData(array $saleData): array
    {
        $errors = [];

        if (empty($saleData['usuario_id'])) {
            $errors[] = 'ID do usuário é obrigatório';
        }

        if (empty($saleData['cliente_nome'])) {
            $errors[] = 'Nome do cliente é obrigatório';
        }

        if (empty($saleData['forma_pagamento'])) {
            $errors[] = 'Forma de pagamento é obrigatória';
        }

        return $errors;
    }
}
