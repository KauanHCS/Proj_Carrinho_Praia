<?php

namespace CarrinhoDePreia\Repositories;

/**
 * Class SaleRepository
 * 
 * Repositório específico para gerenciamento de vendas.
 * Implementa métodos de análise e relatórios.
 * 
 * @package CarrinhoDePreia\Repositories
 */
class SaleRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected string $table = 'vendas';

    /**
     * {@inheritDoc}
     */
    protected array $fillable = [
        'usuario_id',
        'cliente_nome',
        'cliente_email',
        'cliente_telefone',
        'valor_total',
        'forma_pagamento',
        'status',
        'data_venda'
    ];

    /**
     * Busca vendas por usuário
     * 
     * @param int $userId ID do usuário
     * @param array $options Opções de paginação e ordenação
     * @return array Lista de vendas
     */
    public function findByUser(int $userId, array $options = []): array
    {
        return $this->findBy(['usuario_id' => $userId], $options);
    }

    /**
     * Busca vendas por período
     * 
     * @param string $dataInicio Data inicial (Y-m-d)
     * @param string $dataFim Data final (Y-m-d)
     * @param int|null $userId ID do usuário (opcional)
     * @return array Lista de vendas
     */
    public function findByPeriod(string $dataInicio, string $dataFim, ?int $userId = null): array
    {
        try {
            $query = "SELECT * FROM {$this->table} 
                     WHERE DATE(data_venda) BETWEEN :data_inicio AND :data_fim";
            
            $params = [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ];
            
            if ($userId !== null) {
                $query .= " AND usuario_id = :usuario_id";
                $params['usuario_id'] = $userId;
            }
            
            $query .= " ORDER BY data_venda DESC";
            
            return $this->db->select($query, $params);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Busca vendas por status
     * 
     * @param string $status Status da venda
     * @param int|null $userId ID do usuário (opcional)
     * @return array Lista de vendas
     */
    public function findByStatus(string $status, ?int $userId = null): array
    {
        $criteria = ['status' => $status];
        
        if ($userId !== null) {
            $criteria['usuario_id'] = $userId;
        }
        
        return $this->findBy($criteria, ['orderBy' => 'data_venda', 'orderDir' => 'DESC']);
    }

    /**
     * Busca vendas com itens incluídos (JOIN)
     * 
     * @param int $vendaId ID da venda
     * @return array|null Dados da venda com itens
     */
    public function findWithItems(int $vendaId): ?array
    {
        try {
            $query = "SELECT 
                        v.*,
                        iv.id as item_id,
                        iv.produto_id,
                        iv.quantidade as item_quantidade,
                        iv.preco_unitario,
                        iv.subtotal,
                        p.nome as produto_nome,
                        p.categoria as produto_categoria
                     FROM {$this->table} v
                     LEFT JOIN itens_venda iv ON v.id = iv.venda_id
                     LEFT JOIN produtos p ON iv.produto_id = p.id
                     WHERE v.id = :venda_id";
            
            $result = $this->db->select($query, ['venda_id' => $vendaId]);
            
            if (empty($result)) {
                return null;
            }
            
            // Estruturar dados com itens aninhados
            $venda = [
                'id' => $result[0]['id'],
                'usuario_id' => $result[0]['usuario_id'],
                'cliente_nome' => $result[0]['cliente_nome'],
                'cliente_email' => $result[0]['cliente_email'],
                'cliente_telefone' => $result[0]['cliente_telefone'],
                'valor_total' => $result[0]['valor_total'],
                'forma_pagamento' => $result[0]['forma_pagamento'],
                'status' => $result[0]['status'],
                'data_venda' => $result[0]['data_venda'],
                'itens' => []
            ];
            
            foreach ($result as $row) {
                if ($row['item_id']) {
                    $venda['itens'][] = [
                        'id' => $row['item_id'],
                        'produto_id' => $row['produto_id'],
                        'produto_nome' => $row['produto_nome'],
                        'produto_categoria' => $row['produto_categoria'],
                        'quantidade' => $row['item_quantidade'],
                        'preco_unitario' => $row['preco_unitario'],
                        'subtotal' => $row['subtotal']
                    ];
                }
            }
            
            return $venda;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Calcula total de vendas por período
     * 
     * @param string $dataInicio Data inicial (Y-m-d)
     * @param string $dataFim Data final (Y-m-d)
     * @param int|null $userId ID do usuário (opcional)
     * @return float Total de vendas
     */
    public function getTotalSalesByPeriod(string $dataInicio, string $dataFim, ?int $userId = null): float
    {
        try {
            $query = "SELECT SUM(valor_total) as total FROM {$this->table}
                     WHERE DATE(data_venda) BETWEEN :data_inicio AND :data_fim
                     AND status != 'cancelada'";
            
            $params = [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ];
            
            if ($userId !== null) {
                $query .= " AND usuario_id = :usuario_id";
                $params['usuario_id'] = $userId;
            }
            
            $result = $this->db->select($query, $params);
            
            return !empty($result) ? (float) $result[0]['total'] : 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Conta vendas por período
     * 
     * @param string $dataInicio Data inicial (Y-m-d)
     * @param string $dataFim Data final (Y-m-d)
     * @param int|null $userId ID do usuário (opcional)
     * @return int Total de vendas
     */
    public function countSalesByPeriod(string $dataInicio, string $dataFim, ?int $userId = null): int
    {
        try {
            $query = "SELECT COUNT(*) as total FROM {$this->table}
                     WHERE DATE(data_venda) BETWEEN :data_inicio AND :data_fim";
            
            $params = [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ];
            
            if ($userId !== null) {
                $query .= " AND usuario_id = :usuario_id";
                $params['usuario_id'] = $userId;
            }
            
            $result = $this->db->select($query, $params);
            
            return !empty($result) ? (int) $result[0]['total'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calcula ticket médio
     * 
     * @param int|null $userId ID do usuário (opcional)
     * @return float Ticket médio
     */
    public function getAverageTicket(?int $userId = null): float
    {
        try {
            $query = "SELECT AVG(valor_total) as media FROM {$this->table}
                     WHERE status != 'cancelada'";
            
            $params = [];
            
            if ($userId !== null) {
                $query .= " AND usuario_id = :usuario_id";
                $params['usuario_id'] = $userId;
            }
            
            $result = $this->db->select($query, $params);
            
            return !empty($result) ? (float) $result[0]['media'] : 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Busca vendas agrupadas por forma de pagamento
     * 
     * @param int|null $userId ID do usuário (opcional)
     * @return array Vendas por forma de pagamento
     */
    public function groupByPaymentMethod(?int $userId = null): array
    {
        try {
            $query = "SELECT 
                        forma_pagamento,
                        COUNT(*) as total_vendas,
                        SUM(valor_total) as total_valor
                     FROM {$this->table}
                     WHERE status != 'cancelada'";
            
            $params = [];
            
            if ($userId !== null) {
                $query .= " AND usuario_id = :usuario_id";
                $params['usuario_id'] = $userId;
            }
            
            $query .= " GROUP BY forma_pagamento ORDER BY total_vendas DESC";
            
            return $this->db->select($query, $params);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Dashboard com métricas principais
     * 
     * @param int|null $userId ID do usuário (opcional)
     * @return array Métricas do dashboard
     */
    public function getDashboardMetrics(?int $userId = null): array
    {
        $hoje = date('Y-m-d');
        $primeiroDiaMes = date('Y-m-01');
        
        return [
            'vendas_hoje' => $this->countSalesByPeriod($hoje, $hoje, $userId),
            'vendas_mes' => $this->countSalesByPeriod($primeiroDiaMes, $hoje, $userId),
            'receita_hoje' => $this->getTotalSalesByPeriod($hoje, $hoje, $userId),
            'receita_mes' => $this->getTotalSalesByPeriod($primeiroDiaMes, $hoje, $userId),
            'ticket_medio' => $this->getAverageTicket($userId),
            'por_pagamento' => $this->groupByPaymentMethod($userId)
        ];
    }

    /**
     * Gera relatório de vendas por período
     * 
     * @param string $dataInicio Data inicial (Y-m-d)
     * @param string $dataFim Data final (Y-m-d)
     * @param int|null $userId ID do usuário (opcional)
     * @return array Relatório completo
     */
    public function getSalesReport(string $dataInicio, string $dataFim, ?int $userId = null): array
    {
        return [
            'periodo' => [
                'inicio' => $dataInicio,
                'fim' => $dataFim
            ],
            'total_vendas' => $this->countSalesByPeriod($dataInicio, $dataFim, $userId),
            'receita_total' => $this->getTotalSalesByPeriod($dataInicio, $dataFim, $userId),
            'ticket_medio' => $this->getAverageTicket($userId),
            'vendas' => $this->findByPeriod($dataInicio, $dataFim, $userId),
            'por_pagamento' => $this->groupByPaymentMethod($userId)
        ];
    }
}
