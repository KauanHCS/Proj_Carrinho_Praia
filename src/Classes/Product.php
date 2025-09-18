<?php

namespace CarrinhoDePreia;

use CarrinhoDePreia\Database;

/**
 * Classe Product - Gerencia produtos
 * Mantém compatibilidade total com as funções originais
 */
class Product
{
    private $db;
    private $validCategories = ['bebida', 'comida', 'acessorio', 'outros'];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Salvar novo produto - mantém mesma lógica original
     */
    public function save($usuarioId, $dados)
    {
        try {
            // Sanitize inputs - mesma lógica original
            $nome = $this->sanitizeInput($dados['nome'] ?? '');
            $categoria = $this->sanitizeInput($dados['categoria'] ?? '');
            $precoCompra = $this->sanitizeInput($dados['preco_compra'] ?? '');
            $precoVenda = $this->sanitizeInput($dados['preco_venda'] ?? '');
            $quantidade = $this->sanitizeInput($dados['quantidade'] ?? '');
            $limiteMinimo = $this->sanitizeInput($dados['limite_minimo'] ?? '');
            $validade = $this->sanitizeInput($dados['validade'] ?? '') ?: null;
            $observacoes = $this->sanitizeInput($dados['observacoes'] ?? '');

            // Validate inputs - mesmas validações originais
            if (empty($nome) || strlen($nome) < 2 || strlen($nome) > 100) {
                throw new \Exception('Nome do produto deve ter entre 2 e 100 caracteres');
            }

            if (!in_array($categoria, $this->validCategories)) {
                throw new \Exception('Categoria inválida');
            }

            if (!$this->validatePrice($precoCompra)) {
                throw new \Exception('Preço de compra deve ser um valor positivo');
            }

            if (!$this->validatePrice($precoVenda)) {
                throw new \Exception('Preço de venda deve ser um valor positivo');
            }

            if ($precoVenda <= $precoCompra) {
                throw new \Exception('Preço de venda deve ser maior que preço de compra');
            }

            if (!$this->validateQuantity($quantidade)) {
                throw new \Exception('Quantidade deve ser um número inteiro positivo');
            }

            if (!$this->validateQuantity($limiteMinimo)) {
                throw new \Exception('Limite mínimo deve ser um número inteiro positivo');
            }

            // Validate date if provided
            if ($validade && !empty($validade)) {
                $date = \DateTime::createFromFormat('Y-m-d', $validade);
                if (!$date || $date < new \DateTime()) {
                    throw new \Exception('Data de validade deve ser futura');
                }
            }

            // Verificar se o produto já existe para este usuário
            $existingProduct = $this->db->selectOne(
                "SELECT id FROM produtos WHERE nome = ? AND usuario_id = ?",
                "si",
                [$nome, $usuarioId]
            );

            if ($existingProduct) {
                throw new \Exception('Você já possui um produto com este nome');
            }

            // Inserir produto - mesma estrutura original
            $produtoId = $this->db->insert(
                "INSERT INTO produtos (nome, preco_compra, preco_venda, quantidade, categoria, limite_minimo, validade, observacoes, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                "sddisissi",
                [$nome, $precoCompra, $precoVenda, $quantidade, $categoria, $limiteMinimo, $validade, $observacoes, $usuarioId]
            );

            return [
                'success' => true,
                'data' => ['produto_id' => $produtoId],
                'message' => 'Produto cadastrado com sucesso'
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
     * Atualizar produto - mantém mesma lógica original
     */
    public function update($usuarioId, $dados)
    {
        try {
            $id = $dados['id'];
            $nome = $dados['nome'];
            $categoria = $dados['categoria'];
            $precoCompra = $dados['preco_compra'];
            $precoVenda = $dados['preco_venda'];
            $quantidade = $dados['quantidade'];
            $limiteMinimo = $dados['limite_minimo'];
            $validade = $dados['validade'] ?: null;
            $observacoes = $dados['observacoes'] ?: '';

            // Verificar se o produto existe e pertence ao usuário
            $existingProduct = $this->db->selectOne(
                "SELECT id FROM produtos WHERE id = ? AND usuario_id = ?",
                "ii",
                [$id, $usuarioId]
            );

            if (!$existingProduct) {
                throw new \Exception('Produto não encontrado ou não pertence a você');
            }

            // Validar preços - mesmas validações originais
            if (!$this->validatePrice($precoCompra)) {
                throw new \Exception('Preço de compra deve ser um valor positivo');
            }

            if (!$this->validatePrice($precoVenda)) {
                throw new \Exception('Preço de venda deve ser um valor positivo');
            }

            if ($precoVenda <= $precoCompra) {
                throw new \Exception('Preço de venda deve ser maior que preço de compra');
            }

            // Atualizar produto - mesma estrutura original
            $this->db->execute(
                "UPDATE produtos SET nome = ?, preco_compra = ?, preco_venda = ?, quantidade = ?, categoria = ?, limite_minimo = ?, validade = ?, observacoes = ? WHERE id = ? AND usuario_id = ?",
                "sddississi",
                [$nome, $precoCompra, $precoVenda, $quantidade, $categoria, $limiteMinimo, $validade, $observacoes, $id, $usuarioId]
            );

            return [
                'success' => true,
                'data' => ['produto_id' => $id],
                'message' => 'Produto atualizado com sucesso'
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
     * Excluir produto - mantém mesma lógica original
     */
    public function delete($usuarioId, $id)
    {
        try {
            // Verificar se o produto existe e pertence ao usuário
            $product = $this->db->selectOne(
                "SELECT id, nome FROM produtos WHERE id = ? AND usuario_id = ?",
                "ii",
                [$id, $usuarioId]
            );

            if (!$product) {
                throw new \Exception('Produto não encontrado ou não pertence a você');
            }

            // Excluir produto - permite excluir mesmo com vendas (mantém histórico)
            $this->db->execute(
                "DELETE FROM produtos WHERE id = ? AND usuario_id = ?",
                "ii",
                [$id, $usuarioId]
            );

            return [
                'success' => true,
                'data' => ['produto_id' => $id, 'nome' => $product['nome']],
                'message' => 'Produto excluído com sucesso'
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
     * Obter produto por ID - mantém mesma lógica original
     */
    public function getById($usuarioId, $id)
    {
        try {
            $product = $this->db->selectOne(
                "SELECT * FROM produtos WHERE id = ? AND usuario_id = ?",
                "ii",
                [$id, $usuarioId]
            );

            if (!$product) {
                throw new \Exception('Produto não encontrado ou não pertence a você');
            }

            return [
                'success' => true,
                'data' => ['produto' => $product],
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
     * Listar todos os produtos do usuário
     */
    public function getAll($usuarioId, $filters = [])
    {
        try {
            $sql = "SELECT * FROM produtos WHERE usuario_id = ?";
            $params = [$usuarioId];
            $types = "i";

            // Aplicar filtros se fornecidos
            if (!empty($filters['categoria'])) {
                $sql .= " AND categoria = ?";
                $params[] = $filters['categoria'];
                $types .= "s";
            }

            if (!empty($filters['nome'])) {
                $sql .= " AND nome LIKE ?";
                $params[] = "%" . $filters['nome'] . "%";
                $types .= "s";
            }

            if (isset($filters['estoque_baixo']) && $filters['estoque_baixo']) {
                $sql .= " AND quantidade <= limite_minimo";
            }

            $sql .= " ORDER BY nome";

            $products = $this->db->select($sql, $types, $params);

            return [
                'success' => true,
                'data' => $products,
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
     * Obter produtos disponíveis para venda (quantidade > 0)
     */
    public function getAvailableForSale($usuarioId)
    {
        try {
            $products = $this->db->select(
                "SELECT * FROM produtos WHERE quantidade > 0 AND usuario_id = ? ORDER BY nome",
                "i",
                [$usuarioId]
            );

            return [
                'success' => true,
                'data' => $products,
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
     * Verificar produtos com estoque baixo - mantém mesma lógica original
     */
    public function checkLowStock($usuarioId)
    {
        try {
            $product = $this->db->selectOne(
                "SELECT id, nome, quantidade, limite_minimo FROM produtos WHERE quantidade <= limite_minimo AND ativo = 1 AND usuario_id = ? ORDER BY quantidade ASC, nome ASC LIMIT 1",
                "i",
                [$usuarioId]
            );

            if ($product) {
                return [
                    'success' => true,
                    'data' => ['produto' => $product],
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
     * Obter produtos mais vendidos - mantém mesma lógica original
     */
    public function getMostSold($usuarioId, $limit = 10, $days = 30)
    {
        try {
            $sql = "SELECT p.nome, p.categoria, SUM(iv.quantidade) as total_vendido, 
                           COUNT(DISTINCT iv.venda_id) as num_vendas
                    FROM itens_venda iv 
                    JOIN produtos p ON iv.produto_id = p.id 
                    JOIN vendas v ON iv.venda_id = v.id 
                    WHERE v.data >= DATE_SUB(CURDATE(), INTERVAL ? DAY) AND p.usuario_id = ? 
                    GROUP BY p.id, p.nome, p.categoria 
                    ORDER BY total_vendido DESC 
                    LIMIT ?";

            $products = $this->db->select($sql, "iii", [$days, $usuarioId, $limit]);

            // Formatar dados para compatibilidade
            $formattedProducts = [];
            foreach ($products as $product) {
                $formattedProducts[] = [
                    'nome' => $product['nome'],
                    'categoria' => $product['categoria'],
                    'total_vendido' => (int)$product['total_vendido'],
                    'num_vendas' => (int)$product['num_vendas']
                ];
            }

            return [
                'success' => true,
                'data' => $formattedProducts,
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
     * Buscar produtos por termo
     */
    public function search($usuarioId, $searchTerm, $categoria = '')
    {
        try {
            $sql = "SELECT * FROM produtos WHERE usuario_id = ? AND nome LIKE ?";
            $params = [$usuarioId, "%" . $searchTerm . "%"];
            $types = "is";

            if (!empty($categoria)) {
                $sql .= " AND categoria = ?";
                $params[] = $categoria;
                $types .= "s";
            }

            $sql .= " ORDER BY nome";

            $products = $this->db->select($sql, $types, $params);

            return [
                'success' => true,
                'data' => $products,
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
     * Verificar disponibilidade do produto para venda
     */
    public function checkAvailability($productId, $quantidade)
    {
        try {
            $product = $this->db->selectOne(
                "SELECT quantidade, nome FROM produtos WHERE id = ?",
                "i",
                [$productId]
            );

            if (!$product) {
                throw new \Exception('Produto não encontrado');
            }

            if ($product['quantidade'] < $quantidade) {
                throw new \Exception('Estoque insuficiente para ' . $product['nome']);
            }

            return [
                'success' => true,
                'data' => $product,
                'message' => 'Produto disponível'
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
     * Atualizar apenas o estoque do produto (usado pelo Stock)
     */
    public function updateStock($productId, $novaQuantidade)
    {
        try {
            $affectedRows = $this->db->execute(
                "UPDATE produtos SET quantidade = ? WHERE id = ?",
                "ii",
                [$novaQuantidade, $productId]
            );

            if ($affectedRows > 0) {
                return [
                    'success' => true,
                    'message' => 'Estoque atualizado com sucesso'
                ];
            } else {
                throw new \Exception('Produto não encontrado');
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validações - mantém compatibilidade
     */
    private function validatePrice($price)
    {
        return is_numeric($price) && $price > 0;
    }

    private function validateQuantity($quantity)
    {
        return is_numeric($quantity) && intval($quantity) == $quantity && $quantity > 0;
    }

    private function sanitizeInput($input)
    {
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
        return $input;
    }

    /**
     * Obter categorias válidas
     */
    public function getValidCategories()
    {
        return $this->validCategories;
    }
}