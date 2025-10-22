<?php
// Configuração da conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sistema_carrinho";

// Função para conectar ao banco de dados
function getConnection() {
    global $servername, $username, $password, $dbname;
    
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            throw new Exception("Erro de conexão com o banco de dados");
        }
        
        // Definir charset para UTF-8
        if (!$conn->set_charset("utf8mb4")) {
            error_log("Error setting charset: " . $conn->error);
            throw new Exception("Erro ao configurar charset do banco");
        }
        
        // Configurar timezone
        $conn->query("SET time_zone = '-03:00'");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        throw $e;
    }
}

// Função para fechar conexão de forma segura
function closeConnection($conn) {
    if ($conn && !$conn->connect_error) {
        $conn->close();
    }
}
?>
