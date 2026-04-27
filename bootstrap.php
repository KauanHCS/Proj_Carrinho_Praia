<?php
/**
 * bootstrap.php - Inicialização central da aplicação.
 *
 * Responsabilidades:
 *  - Definir PROJECT_ROOT
 *  - Registrar autoload PSR-4 (sem depender do composer install)
 *  - Carregar variáveis de ambiente do .env
 *  - Configurar timezone e error reporting com base em APP_ENV
 *  - Expor helpers globais (env(), is_debug())
 */

if (defined('CARRINHO_BOOTSTRAPPED')) {
    return;
}
define('CARRINHO_BOOTSTRAPPED', true);

if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', __DIR__);
}

// Autoload manual para o namespace CarrinhoDePreia
spl_autoload_register(function ($class) {
    $prefix = 'CarrinhoDePreia\\';
    $baseDir = PROJECT_ROOT . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

    // Locais candidatos (PSR-4 e fallback para layout legado)
    $candidates = [
        $baseDir . $relativePath,
        $baseDir . 'Classes' . DIRECTORY_SEPARATOR . $relativePath,
    ];

    foreach ($candidates as $file) {
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

// Se o composer foi instalado, usa o autoload dele também (não obrigatório)
$composerAutoload = PROJECT_ROOT . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

// Carregar .env via classe Env
\CarrinhoDePreia\Config\Env::load(PROJECT_ROOT . '/.env');

// Helper global para acessar variáveis
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return \CarrinhoDePreia\Config\Env::get($key, $default);
    }
}

if (!function_exists('is_debug')) {
    function is_debug(): bool
    {
        $debug = env('APP_DEBUG', 'false');
        return in_array(strtolower((string) $debug), ['1', 'true', 'yes'], true);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Retorna o token CSRF da sessão atual (gerando se necessário).
     */
    function csrf_token(): string
    {
        return \CarrinhoDePreia\Security::generateCSRFToken();
    }
}

// Timezone
$tz = env('TIMEZONE', 'America/Sao_Paulo');
@date_default_timezone_set($tz);

// Error reporting
if (is_debug()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

// Garantir pasta de logs e arquivo de log padrão
$logDir = PROJECT_ROOT . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/php_errors.log');
