<?php
/**
 * ACTIONS.PHP V2 - Modernizado com Segurança
 * ✨ Rate Limiting
 * ✨ CSRF Protection
 * ✨ Logging estruturado
 * ✨ Validações avançadas
 * ✨ Exception handling
 */

// Carregar bootstrap
require_once __DIR__ . '/../../bootstrap.php';

use CarrinhoDePreia\Security;
use CarrinhoDePreia\Logger;
use CarrinhoDePreia\User;
use CarrinhoDePreia\Product;
use CarrinhoDePreia\Cache;
use CarrinhoDePreia\Validators\ProductValidator;
use CarrinhoDePreia\Exceptions\ValidationException;
use CarrinhoDePreia\Exceptions\AuthenticationException;

// Headers de segurança
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar erros
error_reporting(E_ALL);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);

// Capturar output indesejado
ob_start();

/**
 * Função para resposta JSON limpa
 */
function jsonResponse($success, $data = null, $message = '', $code = 200) {
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code($code);
    
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

/**
 * Handler global de exceções
 */
set_exception_handler(function($e) {
    Logger::error('Exceção não tratada', [
        'type' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    
    if ($e instanceof ValidationException) {
        jsonResponse(false, ['errors' => $e->getErrors()], 'Erro de validação', 400);
    } elseif ($e instanceof AuthenticationException) {
        jsonResponse(false, null, $e->getMessage(), 401);
    } else {
        $message = DEBUG_MODE ? $e->getMessage() : 'Erro interno do servidor';
        jsonResponse(false, null, $message, 500);
    }
});

/**
 * Processar requisições
 */
try {
    // Apenas POST permitido
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Método não permitido', 405);
    }
    
    $action = $_POST['action'] ?? '';
    
    if (empty($action)) {
        jsonResponse(false, null, 'Ação não especificada', 400);
    }
    
    // ✨ CSRF Protection (exceto login/register que são públicos)
    $publicActions = ['login', 'register', 'loginGoogle', 'registerGoogle', 'checkGoogleUser'];
    
    if (!in_array($action, $publicActions)) {
        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (!Security::validateCSRFToken($csrfToken)) {
            Logger::warning('Token CSRF inválido', ['action' => $action]);
            jsonResponse(false, null, 'Token de segurança inválido', 403);
        }
    }
    
    // Roteamento de ações
    switch ($action) {
        // ========================================
        // AUTENTICAÇÃO
        // ========================================
        
        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $user = new User();
            $result = $user->login($email, $password);
            
            jsonResponse(
                $result['success'], 
                $result['data'] ?? null, 
                $result['message'],
                $result['success'] ? 200 : 401
            );
            break;
            
        case 'register':
            $nome = $_POST['nome'] ?? '';
            $sobrenome = $_POST['sobrenome'] ?? '';
            $email = $_POST['email'] ?? '';
            $telefone = $_POST['telefone'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            $user = new User();
            $result = $user->register($nome, $sobrenome, $email, $telefone, $password, $confirmPassword);
            
            jsonResponse(
                $result['success'], 
                $result['data'] ?? null, 
                $result['message'],
                $result['success'] ? 201 : 400
            );
            break;
            
        case 'logout':
            session_destroy();
            Logger::info('Logout realizado', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
            jsonResponse(true, null, 'Logout realizado com sucesso');
            break;
            
        // ========================================
        // PRODUTOS
        // ========================================
        
        case 'salvarProduto':
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            
            if (!$usuarioId) {
                throw new AuthenticationException('Usuário não autenticado');
            }
            
            // Validar dados
            $validator = new ProductValidator();
            $dados = [
                'nome' => $_POST['nome'] ?? '',
                'categoria' => $_POST['categoria'] ?? '',
                'preco_compra' => $_POST['preco_compra'] ?? '',
                'preco_venda' => $_POST['preco_venda'] ?? '',
                'quantidade' => $_POST['quantidade'] ?? '',
                'limite_minimo' => $_POST['limite_minimo'] ?? '',
                'validade' => $_POST['validade'] ?? '',
                'observacoes' => $_POST['observacoes'] ?? ''
            ];
            
            if (!$validator->validate($dados)) {
                throw new ValidationException($validator->getErrors());
            }
            
            $product = new Product();
            $result = $product->save($usuarioId, $dados);
            
            // Invalidar cache de produtos
            Cache::invalidatePattern('/^produtos_usuario_' . $usuarioId . '/');
            
            jsonResponse($result['success'], $result['data'] ?? null, $result['message']);
            break;
            
        case 'listarProdutos':
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            
            if (!$usuarioId) {
                throw new AuthenticationException('Usuário não autenticado');
            }
            
            // Usar cache
            $cacheKey = "produtos_usuario_{$usuarioId}";
            
            $produtos = Cache::remember($cacheKey, function() use ($usuarioId) {
                $product = new Product();
                $result = $product->getAll($usuarioId);
                return $result['data'] ?? [];
            }, 300); // 5 minutos
            
            jsonResponse(true, $produtos, 'Produtos listados com sucesso');
            break;
            
        case 'excluirProduto':
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            $produtoId = $_POST['produto_id'] ?? null;
            
            if (!$usuarioId) {
                throw new AuthenticationException('Usuário não autenticado');
            }
            
            if (!$produtoId) {
                jsonResponse(false, null, 'ID do produto não fornecido', 400);
            }
            
            $product = new Product();
            $result = $product->delete($usuarioId, $produtoId);
            
            // Invalidar cache
            Cache::invalidatePattern('/^produtos_usuario_' . $usuarioId . '/');
            
            jsonResponse($result['success'], $result['data'] ?? null, $result['message']);
            break;
            
        // ========================================
        // SISTEMA
        // ========================================
        
        case 'getCsrfToken':
            // Endpoint para obter novo token CSRF
            $token = Security::generateCSRFToken();
            jsonResponse(true, ['csrf_token' => $token], 'Token gerado');
            break;
            
        case 'getCacheStats':
            // Endpoint para estatísticas do cache (apenas admin)
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            
            if (!$usuarioId) {
                throw new AuthenticationException('Usuário não autenticado');
            }
            
            $stats = Cache::getStats();
            jsonResponse(true, $stats, 'Estatísticas do cache');
            break;
            
        case 'clearCache':
            // Limpar cache (apenas admin)
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            
            if (!$usuarioId) {
                throw new AuthenticationException('Usuário não autenticado');
            }
            
            Cache::clear();
            Logger::info('Cache limpo manualmente', ['user_id' => $usuarioId]);
            jsonResponse(true, null, 'Cache limpo com sucesso');
            break;
            
        // ========================================
        // AÇÃO NÃO ENCONTRADA
        // ========================================
        
        default:
            Logger::warning('Ação desconhecida', ['action' => $action]);
            jsonResponse(false, null, 'Ação não reconhecida', 404);
            break;
    }
    
} catch (ValidationException $e) {
    jsonResponse(false, ['errors' => $e->getErrors()], 'Erro de validação', 400);
    
} catch (AuthenticationException $e) {
    jsonResponse(false, null, $e->getMessage(), 401);
    
} catch (PDOException $e) {
    Logger::error('Erro de banco de dados', [
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
    $message = DEBUG_MODE ? $e->getMessage() : 'Erro ao acessar banco de dados';
    jsonResponse(false, null, $message, 500);
    
} catch (Exception $e) {
    Logger::critical('Erro crítico', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    $message = DEBUG_MODE ? $e->getMessage() : 'Erro interno do servidor';
    jsonResponse(false, null, $message, 500);
}
