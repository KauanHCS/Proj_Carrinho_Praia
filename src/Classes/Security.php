<?php

namespace CarrinhoDePreia;

/**
 * Classe Security - Gerencia segurança do sistema
 * 
 * Funcionalidades:
 * - Rate limiting para prevenir brute force
 * - CSRF token generation e validação
 * - Validação de força de senha
 * - Sanitização de inputs
 */
class Security
{
    /**
     * Verificar rate limit para prevenir brute force
     * 
     * @param string $identifier Email, IP ou outro identificador único
     * @param int $maxAttempts Máximo de tentativas permitidas
     * @param int $timeWindow Janela de tempo em segundos (padrão 5 minutos)
     * @return bool True se está dentro do limite, False se excedeu
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = "rate_limit_" . md5($identifier);
        $now = time();
        
        // Inicializar se não existir
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => $now,
                'last_attempt' => $now
            ];
            return true;
        }
        
        $data = $_SESSION[$key];
        
        // Verificar se a janela de tempo expirou
        if ($now - $data['first_attempt'] > $timeWindow) {
            // Reset do contador
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => $now,
                'last_attempt' => $now
            ];
            return true;
        }
        
        // Verificar se excedeu o limite
        if ($data['attempts'] >= $maxAttempts) {
            $timeRemaining = $timeWindow - ($now - $data['first_attempt']);
            $_SESSION[$key]['blocked_until'] = $now + $timeRemaining;
            return false;
        }
        
        // Incrementar tentativas
        $_SESSION[$key]['attempts']++;
        $_SESSION[$key]['last_attempt'] = $now;
        
        return true;
    }
    
    /**
     * Verificar rate limit usando tabela `login_attempts` (persistente).
     *
     * Esta versão resiste a limpeza de cookies/sessão e é a recomendada
     * para fluxos sensíveis como login.
     *
     * Cria automaticamente a tabela se não existir (defensivo — a migração
     * oficial está em database/migrations/create_login_attempts.sql).
     *
     * @return bool true se está dentro do limite, false se excedeu
     */
    public static function checkRateLimitDb(\PDO $pdo, $identifier, $maxAttempts = 5, $timeWindow = 300)
    {
        try {
            self::ensureLoginAttemptsTable($pdo);

            $now = new \DateTimeImmutable('now');
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;

            $stmt = $pdo->prepare('SELECT attempts, first_attempt, blocked_until FROM login_attempts WHERE identifier = ?');
            $stmt->execute([$identifier]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                $insert = $pdo->prepare(
                    'INSERT INTO login_attempts (identifier, ip, attempts, first_attempt, last_attempt) VALUES (?, ?, 1, ?, ?)'
                );
                $insert->execute([$identifier, $ip, $now->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s')]);
                return true;
            }

            // Já bloqueado e ainda dentro do bloqueio?
            if (!empty($row['blocked_until']) && strtotime($row['blocked_until']) > $now->getTimestamp()) {
                return false;
            }

            // Janela expirou? Reseta o contador.
            if ($now->getTimestamp() - strtotime($row['first_attempt']) > $timeWindow) {
                $update = $pdo->prepare(
                    'UPDATE login_attempts SET attempts = 1, first_attempt = ?, last_attempt = ?, blocked_until = NULL WHERE identifier = ?'
                );
                $update->execute([$now->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'), $identifier]);
                return true;
            }

            // Excedeu o limite? Marca bloqueio.
            if ((int) $row['attempts'] + 1 > $maxAttempts) {
                $blockedUntil = $now->getTimestamp() + ($timeWindow - ($now->getTimestamp() - strtotime($row['first_attempt'])));
                $update = $pdo->prepare(
                    'UPDATE login_attempts SET attempts = attempts + 1, last_attempt = ?, blocked_until = ? WHERE identifier = ?'
                );
                $update->execute([$now->format('Y-m-d H:i:s'), date('Y-m-d H:i:s', $blockedUntil), $identifier]);
                return false;
            }

            // Incrementa.
            $update = $pdo->prepare(
                'UPDATE login_attempts SET attempts = attempts + 1, last_attempt = ?, ip = ? WHERE identifier = ?'
            );
            $update->execute([$now->format('Y-m-d H:i:s'), $ip, $identifier]);
            return true;
        } catch (\Throwable $e) {
            // Em caso de qualquer erro de DB no rate-limit, falhar aberto evita
            // bloquear o login legítimo. Logamos para investigação.
            error_log('checkRateLimitDb error: ' . $e->getMessage());
            return true;
        }
    }

    /**
     * Reset persistente do rate limit (chamado após login bem-sucedido).
     */
    public static function resetRateLimitDb(\PDO $pdo, $identifier)
    {
        try {
            $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE identifier = ?');
            $stmt->execute([$identifier]);
        } catch (\Throwable $e) {
            error_log('resetRateLimitDb error: ' . $e->getMessage());
        }
    }

    /**
     * Tempo restante de bloqueio para o identificador (segundos).
     */
    public static function getRateLimitWaitTimeDb(\PDO $pdo, $identifier): int
    {
        try {
            $stmt = $pdo->prepare('SELECT blocked_until FROM login_attempts WHERE identifier = ?');
            $stmt->execute([$identifier]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row || empty($row['blocked_until'])) {
                return 0;
            }
            return max(0, strtotime($row['blocked_until']) - time());
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private static function ensureLoginAttemptsTable(\PDO $pdo): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS login_attempts ('
            . ' id INT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' identifier VARCHAR(190) NOT NULL,'
            . ' ip VARCHAR(45) NULL,'
            . ' attempts INT UNSIGNED NOT NULL DEFAULT 0,'
            . ' first_attempt DATETIME NOT NULL,'
            . ' last_attempt DATETIME NOT NULL,'
            . ' blocked_until DATETIME NULL,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_login_attempts_identifier (identifier),'
            . ' KEY idx_login_attempts_blocked_until (blocked_until)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $checked = true;
    }

    /**
     * Resetar rate limit para um identificador
     * 
     * @param string $identifier Identificador a resetar
     */
    public static function resetRateLimit($identifier)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = "rate_limit_" . md5($identifier);
        unset($_SESSION[$key]);
    }
    
    /**
     * Obter tempo restante de bloqueio
     * 
     * @param string $identifier Identificador
     * @return int Segundos restantes de bloqueio, 0 se não bloqueado
     */
    public static function getRateLimitWaitTime($identifier)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = "rate_limit_" . md5($identifier);
        
        if (!isset($_SESSION[$key]['blocked_until'])) {
            return 0;
        }
        
        $waitTime = $_SESSION[$key]['blocked_until'] - time();
        return max(0, $waitTime);
    }
    
    /**
     * Gerar token CSRF
     * 
     * @return string Token CSRF
     */
    public static function generateCSRFToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) || 
            (time() - $_SESSION['csrf_token_time'] > 3600)) { // Token expira em 1 hora
            
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validar token CSRF
     * 
     * @param string $token Token a validar
     * @return bool True se válido, False caso contrário
     */
    public static function validateCSRFToken($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Verificar se token não expirou (1 hora)
        if (isset($_SESSION['csrf_token_time']) && 
            (time() - $_SESSION['csrf_token_time'] > 3600)) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Validar força de senha
     * 
     * @param string $password Senha a validar
     * @param array $options Opções de validação
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validatePasswordStrength($password, $options = [])
    {
        $defaults = [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_number' => true,
            'require_special' => false
        ];
        
        $options = array_merge($defaults, $options);
        $errors = [];
        
        // Comprimento mínimo
        if (strlen($password) < $options['min_length']) {
            $errors[] = "Senha deve ter no mínimo {$options['min_length']} caracteres";
        }
        
        // Letra maiúscula
        if ($options['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Senha deve conter ao menos uma letra maiúscula";
        }
        
        // Letra minúscula
        if ($options['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Senha deve conter ao menos uma letra minúscula";
        }
        
        // Número
        if ($options['require_number'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Senha deve conter ao menos um número";
        }
        
        // Caractere especial
        if ($options['require_special'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Senha deve conter ao menos um caractere especial";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Sanitizar input de forma segura
     * 
     * @param mixed $input Input a sanitizar
     * @param string $type Tipo de sanitização ('string', 'email', 'int', 'float', 'url')
     * @return mixed Input sanitizado
     */
    public static function sanitizeInput($input, $type = 'string')
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::sanitizeInput($value, $type);
            }
            return $input;
        }
        
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
                
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
                
            case 'string':
            default:
                if (is_string($input)) {
                    // Remove tags HTML e PHP
                    $input = strip_tags($input);
                    // Converte caracteres especiais em entidades HTML
                    $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    // Remove espaços extras
                    $input = trim($input);
                }
                return $input;
        }
    }
    
    /**
     * Validar email
     * 
     * @param string $email Email a validar
     * @return bool True se válido
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar URL
     * 
     * @param string $url URL a validar
     * @return bool True se válido
     */
    public static function validateUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Hash de senha seguro
     * 
     * @param string $password Senha a hashear
     * @return string Hash da senha
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verificar senha contra hash
     * 
     * @param string $password Senha em texto plano
     * @param string $hash Hash armazenado
     * @return bool True se senha corresponde ao hash
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Gerar código aleatório seguro
     * 
     * @param int $length Comprimento do código
     * @return string Código gerado
     */
    public static function generateSecureCode($length = 6)
    {
        $bytes = random_bytes(ceil($length / 2));
        $code = substr(bin2hex($bytes), 0, $length);
        return strtoupper($code);
    }
    
    /**
     * Validar parâmetros de tipos esperados
     * 
     * @param array $params Parâmetros a validar
     * @param array $rules Regras de validação ['param' => 'type']
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validateParamTypes($params, $rules)
    {
        $errors = [];
        
        foreach ($rules as $param => $type) {
            if (!isset($params[$param])) {
                $errors[$param] = "Parâmetro '{$param}' é obrigatório";
                continue;
            }
            
            $value = $params[$param];
            $valid = false;
            
            switch ($type) {
                case 'int':
                    $valid = is_numeric($value) && intval($value) == $value;
                    break;
                case 'float':
                case 'double':
                    $valid = is_numeric($value);
                    break;
                case 'string':
                    $valid = is_string($value);
                    break;
                case 'email':
                    $valid = self::validateEmail($value);
                    break;
                case 'array':
                    $valid = is_array($value);
                    break;
                case 'bool':
                case 'boolean':
                    $valid = is_bool($value) || in_array($value, ['0', '1', 'true', 'false'], true);
                    break;
            }
            
            if (!$valid) {
                $errors[$param] = "Parâmetro '{$param}' deve ser do tipo '{$type}'";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
