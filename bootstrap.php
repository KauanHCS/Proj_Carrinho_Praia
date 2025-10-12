<?php
/**
 * BOOTSTRAP - Sistema Carrinho de Praia
 * Inicialização moderna com estrutura organizada
 */

// Prevenir acesso direto
if (!defined('CARRINHO_INIT')) {
    define('CARRINHO_INIT', true);
}

// Iniciar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir autoloader
require_once __DIR__ . '/autoload.php';

// Definir modo de depuração (DEBUG_MODE)
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true); // Altere para false em produção
}

// Incluir configuração do banco de dados
require_once CONFIG_PATH . '/database.php';

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de erro (apenas em desenvolvimento)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Headers de segurança básicos
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Função helper para incluir views
function renderView($viewName, $data = []) {
    $viewFile = VIEWS_PATH . '/' . $viewName . '.php';
    if (file_exists($viewFile)) {
        extract($data);
        include $viewFile;
    } else {
        throw new Exception("View não encontrada: $viewName");
    }
}

// Função helper para redirecionar
function redirect($url, $permanent = false) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . $host . dirname($_SERVER['SCRIPT_NAME']);
    
    $fullUrl = $url[0] === '/' ? $baseUrl . $url : $url;
    
    header('Location: ' . $fullUrl, true, $permanent ? 301 : 302);
    exit;
}

// Função helper para resposta JSON padronizada
function jsonResponse($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Registrar handler de erros global
set_exception_handler(function($exception) {
    error_log($exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
    
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        jsonResponse(false, null, $exception->getMessage(), 500);
    } else {
        jsonResponse(false, null, 'Erro interno do servidor', 500);
    }
});

// Sistema inicializado
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log('Sistema Carrinho de Praia inicializado com sucesso');
}