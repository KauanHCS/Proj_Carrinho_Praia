<?php
/**
 * SCRIPT DE MIGRAÇÃO: Criar tabela vendas_itens
 * Execute este arquivo uma única vez para criar a tabela
 */

// Configurações do banco de dados
$host = 'localhost';
$dbname = 'sistema_carrinho';
$username = 'root';
$password = '';

try {
    // Conectar ao banco
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão estabelecida com o banco de dados.\n\n";
    
    // Ler o arquivo SQL
    $sqlFile = __DIR__ . '/create_vendas_itens.sql';
    
    if (!file_exists($sqlFile)) {
        die("❌ Erro: Arquivo create_vendas_itens.sql não encontrado!\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remover comentários e dividir por ponto e vírgula
    $sql = preg_replace('/--.*$/m', '', $sql); // Remover comentários de linha
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt);
        }
    );
    
    echo "📝 Executando migração...\n\n";
    
    // Executar statements sem transação (CREATE TABLE não suporta rollback)
    foreach ($statements as $index => $statement) {
        if (!empty(trim($statement))) {
            echo "▶️  Executando statement " . ($index + 1) . "...\n";
            try {
                $pdo->exec($statement);
                echo "   ✅ Sucesso!\n\n";
            } catch (PDOException $e) {
                // Se der erro, continuar (pode ser que a tabela já exista)
                if (strpos($e->getMessage(), 'already exists') !== false || 
                    strpos($e->getMessage(), 'Table') !== false && strpos($e->getMessage(), 'already exists') !== false) {
                    echo "   ⚠️  Tabela já existe, pulando...\n\n";
                } else {
                    throw $e;
                }
            }
        }
    }
    
    echo "═══════════════════════════════════════════\n";
    echo "✅ MIGRAÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo "═══════════════════════════════════════════\n\n";
        
        // Verificar se a tabela foi criada
        $stmt = $pdo->query("SHOW TABLES LIKE 'vendas_itens'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabela 'vendas_itens' criada com sucesso!\n";
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM vendas_itens");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "📊 Total de registros: " . $result['total'] . "\n\n";
        } else {
            echo "⚠️  Tabela 'vendas_itens' não foi encontrada (pode já existir).\n\n";
        }
        
        echo "Estrutura da tabela:\n";
        echo "═══════════════════════════════════════════\n";
        $stmt = $pdo->query("DESCRIBE vendas_itens");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            echo sprintf(
                "%-20s %-20s %s\n",
                $column['Field'],
                $column['Type'],
                $column['Null'] === 'NO' ? 'NOT NULL' : 'NULL'
            );
        }
        
    echo "\n═══════════════════════════════════════════\n";
    echo "🎉 TUDO PRONTO! O Dashboard agora funcionará corretamente.\n";
    echo "═══════════════════════════════════════════\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "\nDetalhes:\n";
    echo "Código do erro: " . $e->getCode() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    exit(1);
}
?>
