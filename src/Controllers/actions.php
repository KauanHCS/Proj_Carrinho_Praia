<?php
/**
 * actions.php - Router fino.
 *
 * Mapeia o parâmetro `action` (POST ou GET) para um método estático em um
 * controller de domínio. Toda a lógica de negócio vive nos controllers; este
 * arquivo cuida apenas de:
 *   - inicialização (bootstrap, sessão)
 *   - CORS configurado via .env
 *   - validação de CSRF para POSTs (exceto whitelist pública)
 *   - dispatch para o handler
 *   - tratamento de erros não capturados
 */

require_once __DIR__ . '/../../bootstrap.php';

use CarrinhoDePreia\Controllers\BaseController;
use CarrinhoDePreia\Controllers\AuthController;
use CarrinhoDePreia\Controllers\ProductController;
use CarrinhoDePreia\Controllers\SaleController;
use CarrinhoDePreia\Controllers\FiadoController;
use CarrinhoDePreia\Controllers\GuardasolController;
use CarrinhoDePreia\Controllers\OrderController;
use CarrinhoDePreia\Controllers\DashboardController;
use CarrinhoDePreia\Controllers\UserController;

// CORS dinâmico baseado em .env
$allowedOrigins = array_filter(array_map('trim', explode(',', (string) env('ALLOWED_ORIGINS', ''))));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && (in_array('*', $allowedOrigins, true) || in_array($origin, $allowedOrigins, true))) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();

/**
 * Mapa de rotas POST: action => [Controller::class, 'method'].
 * Lista de actions que dispensam CSRF está em $csrfWhitelist.
 */
$postRoutes = [
    'login'                       => [AuthController::class, 'login'],
    'register'                    => [AuthController::class, 'register'],
    'alterarSenha'                => [AuthController::class, 'alterarSenha'],
    'excluirConta'                => [AuthController::class, 'excluirConta'],

    'salvar_produto'              => [ProductController::class, 'salvar'],
    'atualizar_produto'           => [ProductController::class, 'atualizar'],
    'excluir_produto'             => [ProductController::class, 'excluir'],
    'reabastecer'                 => [ProductController::class, 'reabastecer'],

    'finalizar_venda'             => [SaleController::class, 'finalizarVenda'],

    'cadastrarClienteFiado'       => [FiadoController::class, 'cadastrarCliente'],
    'registrarPagamentoFiado'     => [FiadoController::class, 'registrarPagamento'],

    'cadastrarGuardasol'          => [GuardasolController::class, 'cadastrar'],
    'ocuparGuardasol'             => [GuardasolController::class, 'ocupar'],
    'adicionarComanda'            => [GuardasolController::class, 'adicionarComanda'],
    'finalizarGuardasol'          => [GuardasolController::class, 'finalizar'],
    'removerGuardasol'            => [GuardasolController::class, 'remover'],
    'fecharComanda'               => [GuardasolController::class, 'fecharComanda'],
    'finalizarPagamentoComanda'   => [GuardasolController::class, 'finalizarPagamento'],

    'atualizarStatusPedido'       => [OrderController::class, 'atualizarStatus'],
];

/** Mapa de rotas GET. */
$getRoutes = [
    'listar_produtos'             => [ProductController::class, 'listar'],
    'getDashboardMetrics'         => [DashboardController::class, 'metrics'],

    'getDashboardFiado'           => [FiadoController::class, 'dashboard'],
    'listarClientesFiado'         => [FiadoController::class, 'listarClientes'],
    'obterHistoricoCliente'       => [FiadoController::class, 'historicoCliente'],

    'listarGuardasois'            => [GuardasolController::class, 'listar'],
    'obterComandasGuardasol'      => [GuardasolController::class, 'obterComandas'],

    'listarPedidos'               => [OrderController::class, 'listar'],
    'listarVendasFinanceiro'      => [SaleController::class, 'listarVendasFinanceiro'],

    'listarCodigosFuncionarios'   => [UserController::class, 'listarCodigosFuncionarios'],
    'estatisticasPerfil'          => [UserController::class, 'estatisticasPerfil'],
    'atividadeRecente'            => [UserController::class, 'atividadeRecente'],
];

/** Actions que dispensam CSRF (login/register não têm sessão prévia). */
$csrfWhitelist = ['login', 'register'];

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $method === 'POST' ? ($_POST['action'] ?? '') : ($_GET['action'] ?? '');

    if ($action === '') {
        BaseController::error('Ação não especificada');
    }

    if ($method === 'POST') {
        BaseController::validateCsrf($action, $csrfWhitelist);
        $handler = $postRoutes[$action] ?? null;
    } elseif ($method === 'GET') {
        $handler = $getRoutes[$action] ?? null;
    } else {
        http_response_code(405);
        BaseController::error('Método não permitido');
    }

    if (!$handler) {
        BaseController::error("Ação inválida: {$action}");
    }

    [$class, $func] = $handler;
    $class::$func();
} catch (\Throwable $e) {
    BaseController::logError('Erro não tratado em actions.php', [
        'error' => $e->getMessage(),
        'file'  => $e->getFile(),
        'line'  => $e->getLine(),
    ]);
    $msg = (function_exists('is_debug') && is_debug())
        ? 'Erro interno: ' . $e->getMessage()
        : 'Erro interno do servidor';
    BaseController::error($msg);
}
