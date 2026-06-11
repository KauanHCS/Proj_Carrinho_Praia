<?php

$bootstrap = __DIR__ . '/../bootstrap.php';
if (is_file($bootstrap)) {
    require_once $bootstrap;
}

if (!function_exists('getConnection')) {
    /**
     * Retorna uma conexão mysqli configurada (mantida para retrocompatibilidade).
     */
    function getConnection(): mysqli
    {
        $host = function_exists('env') ? env('DB_HOST', 'localhost') : 'localhost';
        $user = function_exists('env') ? env('DB_USER', 'root') : 'root';
        $pass = function_exists('env') ? env('DB_PASS', '') : '';
        $name = function_exists('env') ? env('DB_NAME', 'sistema_carrinho') : 'sistema_carrinho';
        $port = function_exists('env') ? (int) env('DB_PORT', 3306) : 3306;

        try {
            $conn = new mysqli($host, $user, $pass, $name, $port);

            if ($conn->connect_error) {
                error_log('Database connection failed: ' . $conn->connect_error);
                throw new Exception('Erro de conexão com o banco de dados');
            }

            if (!$conn->set_charset('utf8mb4')) {
                error_log('Error setting charset: ' . $conn->error);
                throw new Exception('Erro ao configurar charset do banco');
            }

            $conn->query("SET time_zone = '-03:00'");

            return $conn;
        } catch (Exception $e) {
            error_log('Database error: ' . $e->getMessage());
            throw $e;
        }
    }
}

if (!function_exists('closeConnection')) {
    function closeConnection($conn): void
    {
        if ($conn instanceof mysqli && !$conn->connect_error) {
            $conn->close();
        }
    }
}
