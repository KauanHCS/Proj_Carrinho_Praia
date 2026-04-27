<?php

namespace CarrinhoDePreia\Controllers;

use CarrinhoDePreia\Database;
use CarrinhoDePreia\Security;

/**
 * BaseController - infraestrutura compartilhada por todos os controllers.
 *
 * Todos os métodos são estáticos para manter o estilo do código existente
 * (handlers de ação) sem precisar instanciar controllers.
 */
abstract class BaseController
{
    /**
     * Retorna a conexão PDO via classe centralizada.
     */
    public static function getPdo(): \PDO
    {
        return Database::getInstance()->getPDOConnection();
    }

    /**
     * Resposta JSON limpa (descarta qualquer output bufferizado anterior).
     */
    public static function json(bool $success, $data = null, string $message = ''): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $payload = ['success' => $success, 'message' => $message];
        if ($data !== null) {
            $payload['data'] = $data;
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Resposta de erro padronizada.
     */
    public static function error(string $message, $data = null): void
    {
        self::json(false, $data, $message);
    }

    /**
     * Garante que existe um usuário autenticado em sessão.
     * Encerra a requisição com 401 se não houver.
     */
    public static function requireAuth(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['usuario_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            self::error('Usuário não autenticado');
        }

        return (int) $userId;
    }

    /**
     * Valida o token CSRF para ações que mudam estado.
     *
     * O token é aceito via header `X-CSRF-Token` (preferencial) ou via campo
     * `csrf_token` no corpo POST. Encerra a requisição com 419 se inválido.
     *
     * Aceita uma whitelist de ações que dispensam CSRF (ex.: login, register,
     * onde o cliente ainda não tem sessão).
     */
    public static function validateCsrf(string $action, array $whitelist = []): void
    {
        if (in_array($action, $whitelist, true)) {
            return;
        }

        // Em ambiente de DEV podemos pular se não houver header configurado ainda,
        // para não travar workflows enquanto o front se ajusta. Em produção é
        // obrigatório.
        $token = self::extractCsrfToken();

        if (function_exists('is_debug') && is_debug() && $token === '') {
            return;
        }

        if ($token === '' || !Security::validateCSRFToken($token)) {
            http_response_code(419);
            self::error('Token CSRF inválido ou ausente');
        }
    }

    /**
     * Sanitiza/normaliza um valor de input.
     */
    public static function input(array $bag, string $key, $default = null)
    {
        return $bag[$key] ?? $default;
    }

    /**
     * Log de erro padronizado (em logs/php_errors.log).
     */
    public static function logError(string $message, array $context = []): void
    {
        $logFile = (defined('PROJECT_ROOT') ? PROJECT_ROOT : __DIR__ . '/../..') . '/logs/php_errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        @file_put_contents($logFile, "[$timestamp] ERROR: $message$contextStr\n", FILE_APPEND);
    }

    private static function extractCsrfToken(): string
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        foreach ($headers as $name => $value) {
            if (strcasecmp($name, 'X-CSRF-Token') === 0) {
                return (string) $value;
            }
        }

        if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        return (string) ($_POST['csrf_token'] ?? '');
    }
}
