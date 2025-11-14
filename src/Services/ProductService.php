<?php

namespace CarrinhoDePreia\Services;

use CarrinhoDePreia\Repositories\ProductRepository;
use CarrinhoDePreia\Validators\ProductValidator;
use CarrinhoDePreia\Exceptions\ValidationException;
use CarrinhoDePreia\Logger;
use CarrinhoDePreia\Cache;

/**
 * Class ProductService
 * 
 * Camada de serviço para gerenciamento de produtos.
 * Encapsula regras de negócio e orquestra operações complexas.
 * 
 * @package CarrinhoDePreia\Services
 */
class ProductService
{
    private ProductRepository $repository;
    private ProductValidator $validator;
    private const CACHE_TTL = 3600; // 1 hora

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->repository = new ProductRepository();
        $this->validator = new ProductValidator();
    }

    /**
     * Cria um novo produto com validação
     * 
     * @param array $data Dados do produto
     * @return array Resposta com sucesso/erro
     */
    public function createProduct(array $data): array
    {
        try {
            // Validar dados
            if (!$this->validator->validate($data)) {
                throw new ValidationException(
                    'Dados inválidos',
                    $this->validator->getErrors()
                );
            }

            // Criar produto
            $productId = $this->repository->create($data);

            if (!$productId) {
                throw new \Exception('Erro ao criar produto no banco de dados');
            }

            // Invalidar cache
            Cache::forget("products_user_{$data['usuario_id']}");
            Cache::forgetPattern('products_*');

            Logger::info('Produto criado via service', [
                'product_id' => $productId,
                'user_id' => $data['usuario_id']
            ]);

            return [
                'success' => true,
                'message' => 'Produto criado com sucesso',
                'data' => ['id' => $productId]
            ];
        } catch (ValidationException $e) {
            Logger::warning('Validação falhou ao criar produto', [
                'errors' => $e->getErrors(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->getErrors()
            ];
        } catch (\Exception $e) {
            Logger::error('Erro ao criar produto', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao criar produto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Atualiza um produto existente
     * 
     * @param int $id ID do produto
     * @param array $data Dados para atualização
     * @return array Resposta com sucesso/erro
     */
    public function updateProduct(int $id, array $data): array
    {
        try {
            // Verificar se produto existe
            $product = $this->repository->findById($id);
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Produto não encontrado'
                ];
            }

            // Validar dados (atualização parcial)
            if (!$this->validator->validateUpdate($data)) {
                throw new ValidationException(
                    'Dados inválidos',
                    $this->validator->getErrors()
                );
            }

            // Atualizar
            $success = $this->repository->update($id, $data);

            if (!$success) {
                throw new \Exception('Erro ao atualizar produto no banco de dados');
            }

            // Invalidar cache
            Cache::forget("product_{$id}");
            Cache::forget("products_user_{$product['usuario_id']}");
            Cache::forgetPattern('products_*');

            Logger::info('Produto atualizado via service', [
                'product_id' => $id,
                'user_id' => $product['usuario_id']
            ]);

            return [
                'success' => true,
                'message' => 'Produto atualizado com sucesso'
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->getErrors()
            ];
        } catch (\Exception $e) {
            Logger::error('Erro ao atualizar produto', [
                'error' => $e->getMessage(),
                'product_id' => $id
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao atualizar produto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove um produto com verificações de segurança
     * 
     * @param int $id ID do produto
     * @param int $userId ID do usuário (dono)
     * @return array Resposta com sucesso/erro
     */
    public function deleteProduct(int $id, int $userId): array
    {
        try {
            // Verificar se produto existe e pertence ao usuário
            $product = $this->repository->findById($id);
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Produto não encontrado'
                ];
            }

            if ((int)$product['usuario_id'] !== $userId) {
                Logger::warning('Tentativa de deletar produto de outro usuário', [
                    'product_id' => $id,
                    'owner_id' => $product['usuario_id'],
                    'attempted_by' => $userId
                ]);

                return [
                    'success' => false,
                    'message' => 'Você não tem permissão para deletar este produto'
                ];
            }

            // Deletar
            $success = $this->repository->delete($id);

            if (!$success) {
                throw new \Exception('Erro ao deletar produto no banco de dados');
            }

            // Invalidar cache
            Cache::forget("product_{$id}");
            Cache::forget("products_user_{$userId}");
            Cache::forgetPattern('products_*');

            Logger::info('Produto deletado via service', [
                'product_id' => $id,
                'user_id' => $userId
            ]);

            return [
                'success' => true,
                'message' => 'Produto deletado com sucesso'
            ];
        } catch (\Exception $e) {
            Logger::error('Erro ao deletar produto', [
                'error' => $e->getMessage(),
                'product_id' => $id
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao deletar produto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Busca produto por ID com cache
     * 
     * @param int $id ID do produto
     * @return array|null Dados do produto
     */
    public function getProduct(int $id): ?array
    {
        return Cache::remember("product_{$id}", function() use ($id) {
            return $this->repository->findById($id);
        }, self::CACHE_TTL);
    }

    /**
     * Lista produtos do usuário com cache
     * 
     * @param int $userId ID do usuário
     * @param array $options Opções de paginação/ordenação
     * @return array Lista de produtos
     */
    public function getUserProducts(int $userId, array $options = []): array
    {
        $cacheKey = "products_user_{$userId}_" . md5(json_encode($options));
        
        return Cache::remember($cacheKey, function() use ($userId, $options) {
            return $this->repository->findByUser($userId, $options);
        }, self::CACHE_TTL);
    }

    /**
     * Processa venda e atualiza estoque
     * 
     * @param int $productId ID do produto
     * @param int $quantity Quantidade vendida
     * @return array Resposta com sucesso/erro
     */
    public function processSale(int $productId, int $quantity): array
    {
        try {
            // Verificar se produto existe
            $product = $this->repository->findById($productId);
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Produto não encontrado'
                ];
            }

            // Verificar estoque suficiente
            if ((int)$product['quantidade'] < $quantity) {
                return [
                    'success' => false,
                    'message' => 'Estoque insuficiente',
                    'data' => [
                        'disponivel' => $product['quantidade'],
                        'solicitado' => $quantity
                    ]
                ];
            }

            // Decrementar estoque
            $success = $this->repository->decrementStock($productId, $quantity);

            if (!$success) {
                throw new \Exception('Erro ao atualizar estoque');
            }

            // Invalidar cache
            Cache::forget("product_{$productId}");
            Cache::forget("products_user_{$product['usuario_id']}");
            Cache::forgetPattern('products_*');

            Logger::info('Venda processada - estoque atualizado', [
                'product_id' => $productId,
                'quantity_sold' => $quantity,
                'remaining_stock' => (int)$product['quantidade'] - $quantity
            ]);

            return [
                'success' => true,
                'message' => 'Estoque atualizado com sucesso',
                'data' => [
                    'novo_estoque' => (int)$product['quantidade'] - $quantity
                ]
            ];
        } catch (\Exception $e) {
            Logger::error('Erro ao processar venda', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'quantity' => $quantity
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao processar venda: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Adiciona estoque ao produto
     * 
     * @param int $productId ID do produto
     * @param int $quantity Quantidade a adicionar
     * @param int $userId ID do usuário (verificação de permissão)
     * @return array Resposta com sucesso/erro
     */
    public function addStock(int $productId, int $quantity, int $userId): array
    {
        try {
            // Verificar se produto existe e pertence ao usuário
            $product = $this->repository->findById($productId);
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Produto não encontrado'
                ];
            }

            if ((int)$product['usuario_id'] !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Você não tem permissão para alterar este produto'
                ];
            }

            // Incrementar estoque
            $success = $this->repository->incrementStock($productId, $quantity);

            if (!$success) {
                throw new \Exception('Erro ao adicionar estoque');
            }

            // Invalidar cache
            Cache::forget("product_{$productId}");
            Cache::forget("products_user_{$userId}");
            Cache::forgetPattern('products_*');

            Logger::info('Estoque adicionado', [
                'product_id' => $productId,
                'quantity_added' => $quantity,
                'new_stock' => (int)$product['quantidade'] + $quantity
            ]);

            return [
                'success' => true,
                'message' => 'Estoque adicionado com sucesso',
                'data' => [
                    'novo_estoque' => (int)$product['quantidade'] + $quantity
                ]
            ];
        } catch (\Exception $e) {
            Logger::error('Erro ao adicionar estoque', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao adicionar estoque: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verifica produtos com estoque baixo
     * 
     * @param int $userId ID do usuário
     * @param int $threshold Limite de estoque (padrão: 10)
     * @return array Produtos com estoque baixo
     */
    public function getLowStockProducts(int $userId, int $threshold = 10): array
    {
        return $this->repository->findLowStock($threshold, $userId);
    }

    /**
     * Busca produtos por termo
     * 
     * @param string $searchTerm Termo de busca
     * @param int|null $userId ID do usuário (opcional)
     * @return array Produtos encontrados
     */
    public function searchProducts(string $searchTerm, ?int $userId = null): array
    {
        if (strlen($searchTerm) < 2) {
            return [
                'success' => false,
                'message' => 'Termo de busca deve ter no mínimo 2 caracteres'
            ];
        }

        $products = $this->repository->searchByName($searchTerm, $userId);

        return [
            'success' => true,
            'data' => $products,
            'total' => count($products)
        ];
    }

    /**
     * Retorna dashboard de produtos
     * 
     * @param int $userId ID do usuário
     * @return array Dashboard com métricas
     */
    public function getProductsDashboard(int $userId): array
    {
        return Cache::remember("dashboard_products_{$userId}", function() use ($userId) {
            return [
                'total_produtos' => $this->repository->count(['usuario_id' => $userId]),
                'valor_estoque' => $this->repository->getTotalStockValue($userId),
                'estoque_baixo' => count($this->repository->findLowStock(10, $userId)),
                'por_categoria' => $this->repository->groupByCategory($userId),
                'mais_vendidos' => $this->repository->findBestSellers(5, $userId)
            ];
        }, self::CACHE_TTL);
    }
}
