<?php

/**
 * Autoloader PSR-4 para o Sistema Carrinho de Praia
 * Mantém compatibilidade total com o código existente
 */

// Prevenir múltiplas inclusões
if (defined('CARRINHO_AUTOLOADER_LOADED')) {
    return;
}
define('CARRINHO_AUTOLOADER_LOADED', true);

// Registrar autoloader
spl_autoload_register(function ($className) {
    // Namespace base do projeto
    $baseNamespace = 'CarrinhoDePreia\\';
    
    // Verificar se a classe pertence ao nosso namespace
    if (strpos($className, $baseNamespace) !== 0) {
        return;
    }
    
    // Remover o namespace base
    $relativeClass = substr($className, strlen($baseNamespace));
    
    // Substituir namespace separators por directory separators
    $relativeClass = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass);
    
    // Construir caminho para o arquivo da classe
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $relativeClass . '.php';
    
    // Incluir arquivo se existir
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});

// Incluir funções de compatibilidade se necessário
// Isso garante que as funções antigas continuem funcionando
if (!function_exists('jsonResponse')) {
    /**
     * Função de compatibilidade para resposta JSON
     */
    function jsonResponse($success, $data = null, $message = '') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message
        ]);
        exit;
    }
}

if (!function_exists('sanitizeInput')) {
    /**
     * Função de compatibilidade para sanitização
     */
    function sanitizeInput($input) {
        return \CarrinhoDePreia\User::sanitizeInput($input);
    }
}

if (!function_exists('validateEmail')) {
    /**
     * Função de compatibilidade para validação de email
     */
    function validateEmail($email) {
        return \CarrinhoDePreia\User::validateEmail($email);
    }
}

if (!function_exists('validatePrice')) {
    /**
     * Função de compatibilidade para validação de preço
     */
    function validatePrice($price) {
        return is_numeric($price) && $price > 0;
    }
}

if (!function_exists('validateQuantity')) {
    /**
     * Função de compatibilidade para validação de quantidade
     */
    function validateQuantity($quantity) {
        return is_numeric($quantity) && intval($quantity) == $quantity && $quantity > 0;
    }
}

// Funções de sessão - compatibilidade com actions.php
if (!function_exists('getUsuarioLogado')) {
    /**
     * Função de compatibilidade para obter usuário logado
     */
    function getUsuarioLogado() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['usuario_id'] ?? null;
    }
}

if (!function_exists('verificarLogin')) {
    /**
     * Função de compatibilidade para verificar login
     */
    function verificarLogin() {
        $usuarioId = getUsuarioLogado();
        if (!$usuarioId) {
            jsonResponse(false, null, 'Usuário não está logado');
        }
        return $usuarioId;
    }
}

// Inicializar namespace aliases para facilitar o uso
class_alias('CarrinhoDePreia\\Database', 'DB');
class_alias('CarrinhoDePreia\\User', 'UserAuth');

// Debug information (apenas em desenvolvimento)
if (defined('DEBUG_AUTOLOADER') && constant('DEBUG_AUTOLOADER')) {
    echo "<!-- Autoloader PSR-4 carregado com sucesso -->\n";
}