<?php
/**
 * Script para adicionar coluna cliente_fiado_id na tabela vendas
 * Executar via navegador: http://localhost/Proj_Carrinho_Praia/public/executar_fix_fiado.php
 */

header('Content-Type: text/html; charset=utf-8');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sistema_carrinho;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!DOCTYPE html><html><head><title>Fix Fiado</title></head><body>";
    echo "<h2>Executando correção do banco de dados...</h2>";
    
    // Verificar se a coluna já existe
    $stmt = $pdo->query("SHOW COLUMNS FROM vendas LIKE 'cliente_fiado_id'");
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "<p style='color: blue;'>✓ A coluna 'cliente_fiado_id' já existe na tabela 'vendas'.</p>";
    } else {
        echo "<p style='color: orange;'>➜ Adicionando coluna 'cliente_fiado_id' na tabela 'vendas'...</p>";
        
        $sql = "ALTER TABLE vendas 
                ADD COLUMN cliente_fiado_id INT NULL,
                ADD CONSTRAINT fk_venda_cliente_fiado 
                FOREIGN KEY (cliente_fiado_id) REFERENCES clientes_fiado(id) 
                ON DELETE SET NULL";
        
        $pdo->exec($sql);
        
        echo "<p style='color: green;'>✅ Coluna 'cliente_fiado_id' adicionada com sucesso!</p>";
    }
    
    // Verificar estrutura final
    echo "<h3>Estrutura da tabela 'vendas':</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th></tr>";
    
    $stmt = $pdo->query("DESCRIBE vendas");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p style='color: green; font-weight: bold; margin-top: 20px;'>✅ Correção concluída! O sistema de Fiado está pronto para uso.</p>";
    echo "<p><a href='index.php'>← Voltar ao Sistema</a></p>";
    
    echo "</body></html>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "</body></html>";
}
?>
