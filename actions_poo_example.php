<?php
/**
 * EXEMPLO: actions.php refatorado com POO
 * 
 * Este arquivo mostra como o actions.php ficaria após a migração POO
 * mantendo EXATAMENTE as mesmas rotas, parâmetros e respostas JSON
 */

// Incluir autoloader PSR-4
require_once 'autoload.php';

use CarrinhoDePreia\Database;
use CarrinhoDePreia\User;
use CarrinhoDePreia\Product;
// use CarrinhoDePreia\Sale;    // Próximas classes
// use CarrinhoDePreia\Stock;   // Próximas classes
// use CarrinhoDePreia\Report;  // Próximas classes

// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            handleLogin();
            break;
        case 'register':
            handleRegister();
            break;
        case 'check_google_user':
            handleCheckGoogleUser();
            break;
        case 'register_google':
            handleRegisterGoogle();
            break;
        case 'login_google':
            handleLoginGoogle();
            break;
        case 'finalizar_venda':
            handleFinalizarVenda();
            break;
        case 'salvar_produto':
            handleSalvarProduto();
            break;
        case 'atualizar_produto':
            handleAtualizarProduto();
            break;
        case 'excluir_produto':
            handleExcluirProduto();
            break;
        case 'reabastecer':
            handleReabastecerEstoque();
            break;
        default:
            jsonResponse(false, null, 'Ação inválida');
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'verificar_estoque_baixo':
            handleVerificarEstoqueBaixo();
            break;
        case 'get_produto':
            handleGetProduto();
            break;
        case 'get_produtos_mais_vendidos':
            handleGetProdutosMaisVendidos();
            break;
        default:
            jsonResponse(false, null, 'Ação inválida');
    }
}

// ========================================
// FUNÇÕES DE AUTENTICAÇÃO (Refatoradas com POO)
// ========================================

function handleLogin()
{
    // ✅ MESMA INTERFACE - parâmetros e resposta idênticos
    $user = new User();
    $result = $user->login(
        $_POST['email'] ?? '',
        $_POST['password'] ?? ''
    );
    
    // ✅ MESMA RESPOSTA JSON
    jsonResponse($result['success'], $result['data'], $result['message']);
}

function handleRegister()
{
    // ✅ MESMA INTERFACE - parâmetros e resposta idênticos  
    $user = new User();
    $result = $user->register(
        $_POST['nome'] ?? '',
        $_POST['sobrenome'] ?? '',
        $_POST['email'] ?? '',
        $_POST['telefone'] ?? '',
        $_POST['password'] ?? '',
        $_POST['confirm_password'] ?? ''
    );
    
    // ✅ MESMA RESPOSTA JSON
    jsonResponse($result['success'], $result['data'], $result['message']);
}

function handleCheckGoogleUser()
{
    // ✅ MESMA INTERFACE - parâmetros e resposta idênticos
    $user = new User();
    $result = $user->checkGoogleUser($_POST['email'] ?? '');
    
    jsonResponse($result['success'], $result['data'], $result['message'] ?? '');
}

function handleRegisterGoogle()
{
    // ✅ MESMA INTERFACE - parâmetros e resposta idênticos
    $user = new User();
    $result = $user->registerGoogle(
        $_POST['name'] ?? '',
        $_POST['email'] ?? '',
        $_POST['imageUrl'] ?? '',
        $_POST['googleId'] ?? ''
    );
    
    jsonResponse($result['success'], $result['data'], $result['message']);
}

function handleLoginGoogle()
{
    // ✅ MESMA INTERFACE - parâmetros e resposta idênticos
    $user = new User();
    $result = $user->loginGoogle($_POST['email'] ?? '');
    
    jsonResponse($result['success'], $result['data'], $result['message']);
}

// ========================================
// FUNÇÕES DE PRODUTOS (Refatoradas com POO)
// ========================================

function handleSalvarProduto()
{
    try {
        // ✅ MESMA VERIFICAÇÃO DE LOGIN
        $user = new User();
        $usuarioId = $user->verificarLogin();
        
        // ✅ MESMA INTERFACE - todos os parâmetros idênticos
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
        
        // ✅ MESMA RESPOSTA JSON
        jsonResponse($result['success'], $result['data'], $result['message']);
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
}

function handleAtualizarProduto()
{
    try {
        // ✅ MESMA VERIFICAÇÃO DE LOGIN
        $user = new User();
        $usuarioId = $user->verificarLogin();
        
        // ✅ MESMA INTERFACE - todos os parâmetros idênticos
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
        
        // ✅ MESMA RESPOSTA JSON
        jsonResponse($result['success'], $result['data'], $result['message']);
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
}

function handleExcluirProduto()
{
    try {
        // ✅ MESMA VERIFICAÇÃO DE LOGIN
        $user = new User();
        $usuarioId = $user->verificarLogin();
        
        // ✅ MESMA INTERFACE
        $product = new Product();
        $result = $product->delete($usuarioId, $_POST['id']);
        
        // ✅ MESMA RESPOSTA JSON
        jsonResponse($result['success'], $result['data'], $result['message']);
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
}

function handleVerificarEstoqueBaixo()
{
    try {
        // ✅ MESMA VERIFICAÇÃO DE LOGIN
        $user = new User();
        $usuarioId = $user->getUsuarioLogado();
        
        if (!$usuarioId) {
            jsonResponse(false, null, 'Usuário não logado');
        }
        
        // ✅ MESMA INTERFACE
        $product = new Product();
        $result = $product->checkLowStock($usuarioId);
        
        // ✅ MESMA RESPOSTA JSON
        jsonResponse($result['success'], $result['data'], $result['message']);
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
}

function handleGetProduto()
{
    try {
        // ✅ MESMA VERIFICAÇÃO DE LOGIN
        $user = new User();
        $usuarioId = $user->getUsuarioLogado();
        
        if (!$usuarioId) {
            jsonResponse(false, null, 'Usuário não está logado');
        }
        
        // ✅ MESMA INTERFACE
        $product = new Product();
        $result = $product->getById($usuarioId, $_GET['id']);
        
        // ✅ MESMA RESPOSTA JSON
        jsonResponse($result['success'], $result['data'], $result['message']);
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
}

function handleGetProdutosMaisVendidos()
{
    try {
        // ✅ MESMA VERIFICAÇÃO DE LOGIN
        $user = new User();
        $usuarioId = $user->getUsuarioLogado();
        
        if (!$usuarioId) {
            jsonResponse(true, []); // ✅ Mesmo comportamento
        }
        
        // ✅ MESMA INTERFACE E RESPOSTA
        $product = new Product();
        $result = $product->getMostSold($usuarioId);
        
        // ✅ RESPOSTA FORMATADA EXATAMENTE IGUAL AO ORIGINAL
        if ($result['success']) {
            jsonResponse(true, $result['data']);
        } else {
            jsonResponse(false, null, $result['message']);
        }
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
}

// ========================================
// FUNÇÕES TEMPORÁRIAS (até completar outras classes)
// ========================================

function handleFinalizarVenda()
{
    // TODO: Implementar com classe Sale
    // Por enquanto, mantém lógica original
    finalizarVenda_legacy();
}

function handleReabastecerEstoque()
{
    // TODO: Implementar com classe Stock  
    // Por enquanto, mantém lógica original
    reabastecerEstoque_legacy();
}

// ========================================
// FUNÇÕES LEGADAS TEMPORÁRIAS
// ========================================

function finalizarVenda_legacy()
{
    // Código original da função finalizarVenda()
    // Mantido temporariamente até implementar classe Sale
    $conn = getConnection();
    
    try {
        $carrinho = json_decode($_POST['carrinho'], true);
        $formaPagamento = $_POST['forma_pagamento'];
        $valorPago = $_POST['valor_pago'] ?? 0;
        
        // ... resto da lógica original
        
        jsonResponse(true, ['venda_id' => 1], 'Venda finalizada com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function reabastecerEstoque_legacy()
{
    // Código original da função reabastecerEstoque()  
    // Mantido temporariamente até implementar classe Stock
    $conn = getConnection();
    
    try {
        $produtoId = $_POST['produto_id'];
        $quantidade = $_POST['quantidade'];
        
        // ... resto da lógica original
        
        jsonResponse(true, [], 'Estoque reabastecido com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

/* 
========================================
🎯 RESUMO DA MIGRAÇÃO POO
========================================

✅ O QUE MUDA:
- Código mais organizado em classes
- Melhor separação de responsabilidades  
- Padrões modernos (PSR-4, namespaces)
- Maior facilidade de manutenção
- Melhor testabilidade

✅ O QUE NÃO MUDA:
- Rotas da API (POST/GET actions)
- Parâmetros recebidos ($_POST, $_GET)
- Estrutura das respostas JSON
- Interface do usuário (HTML/CSS/JS)
- Funcionalidades existentes
- Performance (na verdade melhora)

✅ COMPATIBILIDADE:
- 100% mantida durante e após migração
- Funções antigas continuam funcionando
- Zero downtime na migração
- Zero impacto na interface do usuário

✅ BENEFÍCIOS:
- Código mais limpo e organizados
- Facilita correção de bugs
- Facilita adição de novas features
- Melhor segurança e validação
- Padrões modernos de desenvolvimento

*/
?>