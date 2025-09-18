<?php
/**
 * TESTE DE CONEXÃO COM BANCO DE DADOS
 * Para debuggar problemas de login com contas não-demo
 */

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    // Incluir configuração
    require_once '../autoload.php';
    require_once '../config/database.php';
    
    // Testar conexão tradicional primeiro
    echo "Testando conexão tradicional...\n";
    $conn = getConnection();
    echo "Conexão tradicional: OK\n";
    
    // Testar classe Database
    echo "Testando classe Database...\n";
    use CarrinhoDePreia\Database;
    $db = Database::getInstance();
    echo "Classe Database: OK\n";
    
    // Testar query simples
    echo "Testando query simples...\n";
    $result = $db->selectOne("SELECT COUNT(*) as total FROM usuarios");
    echo "Total de usuários: " . ($result['total'] ?? 0) . "\n";
    
    // Testar classe User
    echo "Testando classe User...\n";
    use CarrinhoDePreia\User;
    $user = new User();
    echo "Classe User: OK\n";
    
    // Simular login com dados inválidos (para testar resposta)
    echo "Testando login com dados inválidos...\n";
    $loginResult = $user->login('test@test.com', 'wrongpassword');
    echo "Resultado do login: " . json_encode($loginResult) . "\n";
    
    // Limpar buffer
    if (ob_get_level()) {
        ob_clean();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Todos os testes passaram!',
        'data' => [
            'conexao_tradicional' => 'OK',
            'classe_database' => 'OK',
            'classe_user' => 'OK',
            'total_usuarios' => $result['total'] ?? 0,
            'login_test' => $loginResult
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Limpar buffer
    if (ob_get_level()) {
        ob_clean();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro no teste: ' . $e->getMessage(),
        'error_line' => $e->getLine(),
        'error_file' => $e->getFile()
    ], JSON_UNESCAPED_UNICODE);
}
?>