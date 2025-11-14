<?php

namespace CarrinhoDePreia\Repositories;

/**
 * Class ProductRepository
 * 
 * Repositório específico para gerenciamento de produtos.
 * Implementa métodos customizados além das operações CRUD básicas.
 * 
 * @package CarrinhoDePreia\Repositories
 */
class ProductRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected string $table = 'produtos';

    /**
     * {@inheritDoc}
     */
    protected array $fillable = [
        'nome',
        'descricao',
        'preco',
        'quantidade',
        'categoria',
        'usuario_id',
        'imagem'
    ];

    /**
     * Busca produtos por usuário
     * 
     * @param int $userId ID do usuário
     * @param array $options Opções de paginação e ordenação
     * @return array Lista de produtos
     */
    public function findByUser(int $userId, array $options = []): array
    {
        return $this->findBy(['usuario_id' => $userId], $options);
    }

    /**
     * Busca produtos por categoria
     * 
     * @param string $categoria Nome da categoria
     * @param array $options Opções de paginação e ordenação
     * @return array Lista de produtos
     */
    public function findByCategory(string $categoria, array $options = []): array
    {
        return $this->findBy(['categoria' => $categoria], $options);
    }

    /**
     * Busca produtos com estoque baixo
     * 
     * @param int $threshold Limite de quantidade (padrão: 10)
     * @param int|null $userId ID do usuário (opcional)
     * @return array Lista de produtos com estoque baixo
     */
    public function findLowStock(int $threshold = 10, ?int $userId = null): array
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE quantidade <= :threshold";
            $params = ['threshold' => $threshold];
            
            if ($userId !== null) {
                $query .= " AND usuario_id = :usuario_id";
                $params['usuario_id'] = $userId;
            }
            
            $query .= " ORDER BY quantidade ASC";
            
            return $this->db->select($query, $params);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Busca produtos por nome (LIKE)
     * 
     * @param string $searchTerm Termo de busca
     * @param int|null $userId ID do usuário (opcional)
     * @return array Lista de produtos encontrados
     */
    public function searchByName(string $searchTerm, ?int $userId = null): array
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE nome LIKE :search";
            $params = ['search' => "%{$searchTerm}%"];
            
            if ($userId !== null) {
                $query .= " AND usuario_id = :usuario_id";
                $params['usuario_id'] = $userId;
            }
            
            $query .= " ORDER BY nome ASC";
            
            return $this->db->select($query, $params);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Atualiza o estoque de um produto
     * 
     * @param int $id ID do produto
     * @param int $quantidade Nova quantidade
     * @return bool Sucesso da operação
     */
    public function updateStock(int $id, int $quantidade): bool
    {
        return $this->update($id, ['quantidade' => $quantidade]);
    }

    /**
     * Decrementa o estoque de um produto
     * 
     * @param int $id ID do produto
     * @param int $quantidade Quantidade a decrementar
     * @return bool Sucesso da operação
     */
    public function decrementStock(int $id, int $quantidade): bool
    {
        try {
            $query = "UPDATE {$this->table} 
                     SET quantidade = quantidade - :quantidade 
                     WHERE {$this->primaryKey} = :id 
                     AND quantidade >= :quantidade";
            
            $result = $this->db->update($query, [
                'id' => $id,
                'quantidade' => $quantidade
            ]);
            
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Incrementa o estoque de um produto
     * 
     * @param int $id ID do produto
     * @param int $quantidade Quantidade a incrementar
     * @return bool Sucesso da operação
     */
    public function incrementStock(int $id, int $quantidade): bool
    {
        try {
            $query = "UPDATE {$this->table} 
                     SET quantidade = quantidade + :quantidade 
                     WHERE {$this->primaryKey} = :id";
            
            $result = $this->db->update($query, [
                'id' => $id,
                'quantidade' => $quantidade
            ]);
            
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Busca produtos mais vendidos
     * 
     * @param int $limit Número de produtos a retornar
     * @param int|null $userId ID do usuário (opcional)
     * @return array Lista de produtos mais vendidos
     */
    public function findBestSellers(int $limit = 10, ?int $userId = null): array
    {
        try {
            $query = "SELECT p.*, COUNT(iv.id) as total_vendas, SUM(iv.quantidade) as qtd_vendida
                     FROM {$this->table} p
                     INNER JOIN itens_venda iv ON p.id = iv.produto_id
                     INNER JOIN vendas v ON iv.venda_id = v.id";
            
            $params = ['limit' => $limit];
            
            if ($userId !== null) {
                $query .= " WHERE p.usuario_id = :usuario_id";
                $params['usuario_id'] = $userId;
            }
            
            $query .= " GROUP BY p.id
                       ORDER BY qtd_vendida DESC
                       LIMIT :limit";
            
            return $this->db->select($query, $params);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Calcula o valor total do estoque
     * 
     * @param int|null $userId ID do usuário (opcional)
     * @return float Valor total do estoque
     */
    public function getTotalStockValue(?int $userId = null): float
    {
        try {
            $query = "SELECT SUM(preco * quantidade) as total FROM {$this->table}";
            $params = [];
            
            if ($userId !== null) {
                $query .= " WHERE usuario_id = :usuario_id";
                $params['usuario_id'] = $userId;
            }
            
            $result = $this->db->select($query, $params);
            
            return !empty($result) ? (float) $result[0]['total'] : 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Agrupa produtos por categoria com totais
     * 
     * @param int|null $userId ID do usuário (opcional)
     * @return array Categorias com totais
     */
    public function groupByCategory(?int $userId = null): array
    {
        try {
            $query = "SELECT 
                        categoria,
                        COUNT(*) as total_produtos,
                        SUM(quantidade) as total_quantidade,
                        SUM(preco * quantidade) as valor_total
                     FROM {$this->table}";
            
            $params = [];
            
            if ($userId !== null) {
                $query .= " WHERE usuario_id = :usuario_id";
                $params['usuario_id'] = $userId;
            }
            
            $query .= " GROUP BY categoria ORDER BY total_produtos DESC";
            
            return $this->db->select($query, $params);
        } catch (\Exception $e) {
            return [];
        }
    }
}
