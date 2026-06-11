<?php

namespace CarrinhoDePreia\Controllers;

class ProductController extends BaseController
{
    public static function salvar(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        $nome         = (string) self::input($_POST, 'nome', '');
        $categoria    = (string) self::input($_POST, 'categoria', '');
        $precoCompra  = (float) self::input($_POST, 'preco_compra', 0);
        $precoVenda   = (float) self::input($_POST, 'preco_venda', 0);
        $quantidade   = (int) self::input($_POST, 'quantidade', 0);
        $limiteMinimo = (int) self::input($_POST, 'limite_minimo', 0);
        $validade     = self::input($_POST, 'validade', null);
        $observacoes  = (string) self::input($_POST, 'observacoes', '');

        if ($nome === '' || $categoria === '') {
            self::error('Nome e categoria são obrigatórios');
        }

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO produtos (nome, categoria, preco_compra, preco_venda, quantidade, limite_minimo, validade, observacoes, usuario_id, data_cadastro)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([$nome, $categoria, $precoCompra, $precoVenda, $quantidade, $limiteMinimo, $validade, $observacoes, $userId]);

            self::json(true, ['produto_id' => $pdo->lastInsertId(), 'nome' => $nome], 'Produto cadastrado com sucesso');
        } catch (\Throwable $e) {
            self::logError('Erro ao cadastrar produto', ['error' => $e->getMessage()]);
            self::error('Erro ao cadastrar produto');
        }
    }

    public static function atualizar(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        $id           = (int) self::input($_POST, 'id', 0);
        $nome         = (string) self::input($_POST, 'nome', '');
        $categoria    = (string) self::input($_POST, 'categoria', '');
        $precoCompra  = (float) self::input($_POST, 'preco_compra', 0);
        $precoVenda   = (float) self::input($_POST, 'preco_venda', 0);
        $quantidade   = (int) self::input($_POST, 'quantidade', 0);
        $limiteMinimo = (int) self::input($_POST, 'limite_minimo', 0);
        $validade     = self::input($_POST, 'validade', null);
        $observacoes  = (string) self::input($_POST, 'observacoes', '');

        if ($id === 0 || $nome === '' || $categoria === '') {
            self::error('ID, nome e categoria são obrigatórios');
        }

        try {
            $stmt = $pdo->prepare(
                'UPDATE produtos
                 SET nome = ?, categoria = ?, preco_compra = ?, preco_venda = ?,
                     quantidade = ?, limite_minimo = ?, validade = ?, observacoes = ?
                 WHERE id = ? AND usuario_id = ?'
            );
            $stmt->execute([$nome, $categoria, $precoCompra, $precoVenda, $quantidade, $limiteMinimo, $validade, $observacoes, $id, $userId]);

            if ($stmt->rowCount() > 0) {
                self::json(true, ['produto_id' => $id, 'nome' => $nome], 'Produto atualizado com sucesso');
            }
            self::error('Produto não encontrado ou sem alterações');
        } catch (\Throwable $e) {
            self::logError('Erro ao atualizar produto', ['error' => $e->getMessage()]);
            self::error('Erro ao atualizar produto');
        }
    }

    public static function excluir(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        $id = (int) self::input($_POST, 'id', 0);
        if ($id === 0) {
            self::error('ID do produto é obrigatório');
        }

        try {
            // Buscar nome do produto para mensagem
            $stmt = $pdo->prepare('SELECT nome FROM produtos WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$id, $userId]);
            $produto = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$produto) {
                self::error('Produto não encontrado');
            }

            // Em vez de DELETE físico, faz soft delete para não quebrar FKs
            $stmt = $pdo->prepare('UPDATE produtos SET ativo = 0 WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$id, $userId]);

            if ($stmt->rowCount() === 0) {
                self::error('Não foi possível excluir o produto');
            }

            self::json(true, ['nome' => $produto['nome']], 'Produto excluído com sucesso');
        } catch (\Throwable $e) {
            self::logError('Erro ao excluir produto', ['error' => $e->getMessage()]);
            self::error('Erro ao excluir produto');
        }
    }

    public static function reabastecer(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        $produtoId  = (int) self::input($_POST, 'produto_id', 0);
        $quantidade = (int) self::input($_POST, 'quantidade', 0);

        if ($produtoId === 0 || $quantidade <= 0) {
            self::error('Dados inválidos');
        }

        try {
            $stmt = $pdo->prepare(
                'UPDATE produtos SET quantidade = quantidade + ? WHERE id = ? AND usuario_id = ?'
            );
            $stmt->execute([$quantidade, $produtoId, $userId]);

            $stmt = $pdo->prepare('SELECT nome FROM produtos WHERE id = ?');
            $stmt->execute([$produtoId]);
            $produto = $stmt->fetch(\PDO::FETCH_ASSOC);

            self::json(true, ['nome' => $produto['nome'] ?? 'Produto'], 'Estoque reabastecido com sucesso');
        } catch (\Throwable $e) {
            self::logError('Erro ao reabastecer', ['error' => $e->getMessage()]);
            self::error('Erro ao reabastecer');
        }
    }

    public static function listar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userId = $_SESSION['usuario_id'] ?? $_GET['usuario_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            self::error('Usuário não autenticado');
        }

        $pdo = self::getPdo();
        try {
            $stmt = $pdo->prepare('SELECT * FROM produtos WHERE usuario_id = ? AND ativo = 1 ORDER BY nome ASC');
            $stmt->execute([$userId]);
            self::json(true, $stmt->fetchAll(\PDO::FETCH_ASSOC), 'Produtos listados com sucesso');
        } catch (\Throwable $e) {
            self::logError('Erro ao listar produtos', ['error' => $e->getMessage()]);
            self::error('Erro ao listar produtos');
        }
    }

    public static function getProduto(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id === 0) {
            self::error('ID do produto é obrigatório');
        }

        try {
            $stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = ? AND usuario_id = ? AND ativo = 1');
            $stmt->execute([$id, $userId]);
            $produto = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$produto) {
                self::error('Produto não encontrado');
            }

            self::json(true, ['produto' => $produto], 'Produto carregado com sucesso');
        } catch (\Throwable $e) {
            self::logError('Erro ao obter produto', ['error' => $e->getMessage()]);
            self::error('Erro ao obter produto');
        }
    }
}
