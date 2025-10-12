<?php
/**
 * ACTIONS.PHP CORRIGIDO - POO
 * Versão limpa sem problemas de JSON
 */

// Configurar saída JSON limpa
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Capturar qualquer output indesejado
ob_start();

// Desabilitar display de erros
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Definir PROJECT_ROOT
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(dirname(__DIR__)));
}

// Incluir arquivos necessários
require_once PROJECT_ROOT . '/autoload.php';
require_once PROJECT_ROOT . '/config/database.php';

use CarrinhoDePreia\Database;
use CarrinhoDePreia\User;
use CarrinhoDePreia\Product;
use CarrinhoDePreia\Sale;
use CarrinhoDePreia\Stock;
use CarrinhoDePreia\Report;

try {
    
    // Função para resposta JSON limpa
    function cleanJsonResponse($success, $data = null, $message = '') {
        // Limpar qualquer output anterior
        if (ob_get_level()) {
            ob_clean();
        }
        
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Processar requisições POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'login':
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                
                // Login demo rápido
                if ($email === 'demo@carrinho.com' && $password === '123456') {
                    cleanJsonResponse(true, [
                        'nome' => 'Usuário Demo',
                        'email' => $email
                    ], 'Login demo realizado com sucesso!');
                }
                
                // Login normal com classe User
                try {
                    $user = new User();
                    $result = $user->login($email, $password);
                    cleanJsonResponse($result['success'], $result['data'], $result['message']);
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro de conexão: ' . $e->getMessage());
                }
                break;
                
            case 'register':
                try {
                    $user = new User();
                    $result = $user->register(
                        $_POST['nome'] ?? '',
                        $_POST['sobrenome'] ?? '',
                        $_POST['email'] ?? '',
                        $_POST['telefone'] ?? '',
                        $_POST['password'] ?? '',
                        $_POST['confirm_password'] ?? ''
                    );
                    cleanJsonResponse($result['success'], $result['data'], $result['message']);
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro de conexão: ' . $e->getMessage());
                }
                break;
                
            case 'finalizar_venda':
                try {
                    $carrinho = json_decode($_POST['carrinho'] ?? '[]', true);
                    $formaPagamento = $_POST['forma_pagamento'] ?? '';
                    $valorPago = (float)($_POST['valor_pago'] ?? 0);
                    
                    $sale = new Sale();
                    $result = $sale->finalizarVenda($carrinho, $formaPagamento, $valorPago);
                    cleanJsonResponse($result['success'], $result['data'], $result['message']);
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro ao finalizar venda: ' . $e->getMessage());
                }
                break;
                
            case 'salvar_produto':
                try {
                    $user = new User();
                    $usuarioId = $user->verificarLogin();
                    
                    $product = new Product();
                    $result = $product->save($usuarioId, [
                        'nome' => $_POST['nome'] ?? '',
                        'categoria' => $_POST['categoria'] ?? '',
                        'preco_compra' => $_POST['preco_compra'] ?? '',
                        'preco_venda' => $_POST['preco_venda'] ?? '',
                        'quantidade' => $_POST['quantidade'] ?? '',
                        'limite_minimo' => $_POST['limite_minimo'] ?? '',
                        'validade' => $_POST['validade'] ?? '',
                        'observacoes' => $_POST['observacoes'] ?? ''
                    ]);
                    cleanJsonResponse($result['success'], $result['data'], $result['message']);
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, $e->getMessage());
                }
                break;
                
            case 'atualizar_produto':
                try {
                    // DEBUG: Remover verificação de login temporariamente
                    // $user = new User();
                    // $usuarioId = $user->verificarLogin();
                    $usuarioId = 1; // Usar ID fixo para debug
                    
                    $product = new Product();
                    $result = $product->update($usuarioId, [
                        'id' => $_POST['id'] ?? '',
                        'nome' => $_POST['nome'] ?? '',
                        'categoria' => $_POST['categoria'] ?? '',
                        'preco_compra' => $_POST['preco_compra'] ?? '',
                        'preco_venda' => $_POST['preco_venda'] ?? '',
                        'quantidade' => $_POST['quantidade'] ?? '',
                        'limite_minimo' => $_POST['limite_minimo'] ?? '',
                        'validade' => $_POST['validade'] ?? '',
                        'observacoes' => $_POST['observacoes'] ?? ''
                    ]);
                    cleanJsonResponse($result['success'], $result['data'], $result['message']);
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, $e->getMessage());
                }
                break;
                
            case 'excluir_produto':
                try {
                    // DEBUG: Remover verificação de login temporariamente
                    // $user = new User();
                    // $usuarioId = $user->verificarLogin();
                    $usuarioId = 1; // Usar ID fixo para debug
                    
                    $product = new Product();
                    $result = $product->delete($usuarioId, $_POST['id']);
                    cleanJsonResponse($result['success'], $result['data'], $result['message']);
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, $e->getMessage());
                }
                break;
                
            case 'reabastecer':
                try {
                    // DEBUG: Remover verificação de login temporariamente
                    // $user = new User();
                    // $usuarioId = $user->verificarLogin();
                    $usuarioId = 1; // Usar ID fixo para debug
                    
                    $stock = new Stock();
                    $result = $stock->reabastecer($_POST['produto_id'], $_POST['quantidade'], $usuarioId);
                    cleanJsonResponse($result['success'], $result['data'], $result['message']);
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, $e->getMessage());
                }
                break;
                
            default:
                cleanJsonResponse(false, null, 'Ação inválida: ' . $action);
        }
    }
    // Processar requisições GET  
    else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'verificar_estoque_baixo':
                try {
                    // DEBUG: Usar usuário padrão para debug
                    $usuarioId = $_GET['usuario_id'] ?? 1;
                    $stock = new Stock();
                    $result = $stock->getProximoAlertaEstoque($usuarioId);
                    cleanJsonResponse($result['success'], $result['data'], $result['message']);
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro ao verificar estoque: ' . $e->getMessage());
                }
                break;
                
            case 'get_produto':
                try {
                    // DEBUG: Remover verificação de login temporariamente
                    // $user = new User();
                    // $usuarioId = $user->verificarLogin();
                    $usuarioId = 1; // Usar ID fixo para debug
                    
                    $id = $_GET['id'] ?? 0;
                    $product = new Product();
                    $result = $product->getById($usuarioId, $id);
                    cleanJsonResponse($result['success'], $result['data'], $result['message']);
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro ao buscar produto: ' . $e->getMessage());
                }
                break;
                
            case 'get_produtos_mais_vendidos':
                try {
                    $db = Database::getInstance();
                    
                    // Primeiro, vamos buscar todos os produtos mais vendidos (sem filtro de usuário)
                    // para garantir que o gráfico funcione
                    $sql = "SELECT p.nome, p.categoria, SUM(iv.quantidade) as total_vendido, 
                                   COUNT(DISTINCT iv.venda_id) as num_vendas
                            FROM itens_venda iv 
                            JOIN produtos p ON iv.produto_id = p.id 
                            GROUP BY p.id, p.nome, p.categoria 
                            ORDER BY total_vendido DESC 
                            LIMIT 5";
                    
                    $produtos = $db->select($sql, "", []);
                    
                    if (!empty($produtos)) {
                        // Formatar dados como esperado
                        $produtosFormatados = [];
                        foreach ($produtos as $produto) {
                            $produtosFormatados[] = [
                                'nome' => $produto['nome'],
                                'categoria' => $produto['categoria'],
                                'total_vendido' => (int)$produto['total_vendido'],
                                'num_vendas' => (int)$produto['num_vendas']
                            ];
                        }
                        cleanJsonResponse(true, ['produtos' => $produtosFormatados], 'Produtos mais vendidos carregados');
                        return; // IMPORTANTE: sair aqui para evitar o else
                    } else {
                        // DEBUG: Vamos ver o que há nas tabelas
                        $debugVendas = $db->selectOne("SELECT COUNT(*) as total FROM vendas", "", []);
                        $debugItens = $db->selectOne("SELECT COUNT(*) as total FROM itens_venda", "", []);
                        $debugProdutos = $db->selectOne("SELECT COUNT(*) as total FROM produtos", "", []);
                        
                        cleanJsonResponse(false, [
                            'produtos' => [],
                            'debug' => [
                                'vendas' => $debugVendas['total'],
                                'itens_venda' => $debugItens['total'],
                                'produtos' => $debugProdutos['total']
                            ]
                        ], 'Nenhum produto vendido encontrado');
                    }
                } catch (Exception $e) {
                    cleanJsonResponse(false, ['produtos' => []], 'Erro ao buscar produtos: ' . $e->getMessage());
                }
                break;
                
            default:
                cleanJsonResponse(false, null, 'Ação GET inválida: ' . $action);
        }
    }
    else {
        cleanJsonResponse(false, null, 'Método HTTP não permitido');
    }
    
} catch (Exception $e) {
    // Limpar buffer em caso de erro
    if (ob_get_level()) {
        ob_clean();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>