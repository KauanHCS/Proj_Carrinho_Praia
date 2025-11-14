<?php
/**
 * SCRIPT DE MIGRAÇÃO: Sistema de Fiado/Caderneta
 * Execute este arquivo para criar as tabelas do sistema de fiado
 */

$host = 'localhost';
$dbname = 'sistema_carrinho';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão estabelecida com o banco de dados.\n\n";
    
    $sqlFile = __DIR__ . '/create_sistema_fiado.sql';
    
    if (!file_exists($sqlFile)) {
        die("❌ Erro: Arquivo create_sistema_fiado.sql não encontrado!\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remover comentários
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Separar por ponto e vírgula
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*$/', $stmt);
        }
    );
    
    echo "📝 Executando migração do Sistema de Fiado...\n\n";
    
    foreach ($statements as $index => $statement) {
        if (!empty(trim($statement))) {
            echo "▶️  Executando statement " . ($index + 1) . "...\n";
            try {
                $pdo->exec($statement);
                echo "   ✅ Sucesso!\n\n";
            } catch (PDOException $e) {
                // Ignorar erro de constraint já existente
                if (strpos($e->getMessage(), 'Duplicate') !== false || 
                    strpos($e->getMessage(), 'already exists') !== false) {
                    echo "   ⚠️  Já existe, pulando...\n\n";
                } else {
                    echo "   ⚠️  Aviso: " . $e->getMessage() . "\n\n";
                }
            }
        }
    }
    
    echo "═══════════════════════════════════════════\n";
    echo "✅ MIGRAÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo "═══════════════════════════════════════════\n\n";
    
    // Verificar tabelas criadas
    $stmt = $pdo->query("SHOW TABLES LIKE 'clientes_fiado'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'clientes_fiado' criada com sucesso!\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes_fiado");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   📊 Total de registros: " . $result['total'] . "\n\n";
    }
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'pagamentos_fiado'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'pagamentos_fiado' criada com sucesso!\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM pagamentos_fiado");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   📊 Total de registros: " . $result['total'] . "\n\n";
    }
    
    // Verificar view
    $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_sistema_carrinho = 'view_resumo_fiado'");
    if ($stmt->rowCount() > 0) {
        echo "✅ View 'view_resumo_fiado' criada com sucesso!\n\n";
    }
    
    echo "═══════════════════════════════════════════\n";
    echo "🎉 Sistema de Fiado/Caderneta instalado!\n";
    echo "═══════════════════════════════════════════\n\n";
    echo "Próximos passos:\n";
    echo "1. Acesse o sistema\n";
    echo "2. Vá na aba 'Fiado' (será adicionada ao menu)\n";
    echo "3. Cadastre seus primeiros clientes\n";
    echo "4. Comece a fazer vendas fiadas!\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "\nDetalhes:\n";
    echo "Código do erro: " . $e->getCode() . "\n";
    exit(1);
}
?>
