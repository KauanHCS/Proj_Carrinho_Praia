<?php
require_once 'config/database.php';

try {
    echo "<h2>Verificando estrutura do banco de dados...</h2>";
    
    $conn = getConnection();
    
    // Listar todas as tabelas
    $result = $conn->query("SHOW TABLES");
    echo "<h3>Tabelas no banco:</h3><ul>";
    $tabelas = [];
    while ($row = $result->fetch_row()) {
        $tabelas[] = $row[0];
        echo "<li>{$row[0]}</li>";
    }
    echo "</ul>";
    
    // Para cada tabela, mostrar estrutura
    foreach ($tabelas as $tabela) {
        echo "<hr><h3>Estrutura da tabela: $tabela</h3>";
        $result = $conn->query("DESCRIBE $tabela");
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td><strong>{$row['Field']}</strong></td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "<td>{$row['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    closeConnection($conn);
    
    echo "<hr><p><a href='public/index.php'>Voltar ao sistema</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>
