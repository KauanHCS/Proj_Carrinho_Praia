<?php

namespace CarrinhoDePreia\Repositories;

use CarrinhoDePreia\Database;
use CarrinhoDePreia\Logger;

/**
 * Class BaseRepository
 * 
 * Implementação base abstrata do padrão Repository.
 * Fornece operações CRUD genéricas que podem ser reutilizadas
 * por repositórios concretos ou sobreescritas conforme necessário.
 * 
 * @package CarrinhoDePreia\Repositories
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Database Instância do banco de dados
     */
    protected Database $db;

    /**
     * @var string Nome da tabela associada ao repositório
     */
    protected string $table;

    /**
     * @var string Nome da coluna ID (chave primária)
     */
    protected string $primaryKey = 'id';

    /**
     * @var array Campos permitidos para inserção/atualização
     */
    protected array $fillable = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?array
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
            $result = $this->db->select($query, ['id' => $id]);
            
            return !empty($result) ? $result[0] : null;
        } catch (\Exception $e) {
            Logger::error('Erro ao buscar por ID', [
                'repository' => static::class,
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(array $filters = [], array $options = []): array
    {
        try {
            $query = "SELECT * FROM {$this->table}";
            $params = [];
            
            // Aplicar filtros
            if (!empty($filters)) {
                $conditions = [];
                foreach ($filters as $column => $value) {
                    $conditions[] = "{$column} = :{$column}";
                    $params[$column] = $value;
                }
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
            
            // Ordenação
            if (isset($options['orderBy'])) {
                $orderDir = $options['orderDir'] ?? 'ASC';
                $query .= " ORDER BY {$options['orderBy']} {$orderDir}";
            }
            
            // Paginação
            if (isset($options['limit'])) {
                $query .= " LIMIT :limit";
                $params['limit'] = (int) $options['limit'];
                
                if (isset($options['offset'])) {
                    $query .= " OFFSET :offset";
                    $params['offset'] = (int) $options['offset'];
                }
            }
            
            return $this->db->select($query, $params);
        } catch (\Exception $e) {
            Logger::error('Erro ao buscar todos os registros', [
                'repository' => static::class,
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(array $criteria): ?array
    {
        try {
            $conditions = [];
            $params = [];
            
            foreach ($criteria as $column => $value) {
                $conditions[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
            
            $query = "SELECT * FROM {$this->table} WHERE " 
                   . implode(' AND ', $conditions) 
                   . " LIMIT 1";
            
            $result = $this->db->select($query, $params);
            
            return !empty($result) ? $result[0] : null;
        } catch (\Exception $e) {
            Logger::error('Erro ao buscar um registro', [
                'repository' => static::class,
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria, array $options = []): array
    {
        try {
            $conditions = [];
            $params = [];
            
            foreach ($criteria as $column => $value) {
                $conditions[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
            
            $query = "SELECT * FROM {$this->table}";
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
            
            // Ordenação
            if (isset($options['orderBy'])) {
                $orderDir = $options['orderDir'] ?? 'ASC';
                $query .= " ORDER BY {$options['orderBy']} {$orderDir}";
            }
            
            // Paginação
            if (isset($options['limit'])) {
                $query .= " LIMIT :limit";
                $params['limit'] = (int) $options['limit'];
                
                if (isset($options['offset'])) {
                    $query .= " OFFSET :offset";
                    $params['offset'] = (int) $options['offset'];
                }
            }
            
            return $this->db->select($query, $params);
        } catch (\Exception $e) {
            Logger::error('Erro ao buscar registros', [
                'repository' => static::class,
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): int|false
    {
        try {
            // Filtrar apenas campos permitidos
            $data = $this->filterFillable($data);
            
            if (empty($data)) {
                Logger::warning('Tentativa de criar registro sem dados válidos', [
                    'repository' => static::class
                ]);
                return false;
            }
            
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
            
            $result = $this->db->insert($query, $data);
            
            if ($result) {
                Logger::info('Registro criado com sucesso', [
                    'repository' => static::class,
                    'id' => $result
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Logger::error('Erro ao criar registro', [
                'repository' => static::class,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): bool
    {
        try {
            // Filtrar apenas campos permitidos
            $data = $this->filterFillable($data);
            
            if (empty($data)) {
                Logger::warning('Tentativa de atualizar registro sem dados válidos', [
                    'repository' => static::class,
                    'id' => $id
                ]);
                return false;
            }
            
            $setClause = [];
            foreach (array_keys($data) as $column) {
                $setClause[] = "{$column} = :{$column}";
            }
            
            $query = "UPDATE {$this->table} SET " 
                   . implode(', ', $setClause) 
                   . " WHERE {$this->primaryKey} = :id";
            
            $data['id'] = $id;
            
            $result = $this->db->update($query, $data);
            
            if ($result) {
                Logger::info('Registro atualizado com sucesso', [
                    'repository' => static::class,
                    'id' => $id
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Logger::error('Erro ao atualizar registro', [
                'repository' => static::class,
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        try {
            $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
            
            $result = $this->db->delete($query, ['id' => $id]);
            
            if ($result) {
                Logger::info('Registro deletado com sucesso', [
                    'repository' => static::class,
                    'id' => $id
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Logger::error('Erro ao deletar registro', [
                'repository' => static::class,
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function count(array $criteria = []): int
    {
        try {
            $query = "SELECT COUNT(*) as total FROM {$this->table}";
            $params = [];
            
            if (!empty($criteria)) {
                $conditions = [];
                foreach ($criteria as $column => $value) {
                    $conditions[] = "{$column} = :{$column}";
                    $params[$column] = $value;
                }
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
            
            $result = $this->db->select($query, $params);
            
            return !empty($result) ? (int) $result[0]['total'] : 0;
        } catch (\Exception $e) {
            Logger::error('Erro ao contar registros', [
                'repository' => static::class,
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function exists(array $criteria): bool
    {
        return $this->count($criteria) > 0;
    }

    /**
     * Filtra dados mantendo apenas campos permitidos (fillable)
     * 
     * @param array $data Dados originais
     * @return array Dados filtrados
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Retorna o nome da tabela
     * 
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Retorna a chave primária
     * 
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }
}
