<?php
require_once '../config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>🔍 VERIFICANDO BANCO: sistema_carrinho</h2>";
    
    // Listar tabelas
    echo "<h3>📋 Tabelas existentes:</h3>";
    $result = $conn->query("SHOW TABLES");
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array()) {
            echo "• " . $row[0] . "<br>";
        }
    } else {
        echo "❌ Nenhuma tabela encontrada<br>";
    }
    
    echo "<hr>";
    
    // Verificar dados das tabelas principais
    $tabelas = ['produtos', 'vendas', 'itens_venda'];
    
    foreach ($tabelas as $tabela) {
        echo "<h3>📊 Tabela: $tabela</h3>";
        
        $result = $conn->query("SELECT COUNT(*) as total FROM $tabela");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "Total de registros: " . $row['total'] . "<br>";
            
            // Mostrar alguns registros se houver
            if ($row['total'] > 0) {
                echo "<strong>Primeiros registros:</strong><br>";
                $result2 = $conn->query("SELECT * FROM $tabela LIMIT 3");
                if ($result2 && $result2->num_rows > 0) {
                    while($registro = $result2->fetch_assoc()) {
                        echo "<small>" . json_encode($registro) . "</small><br>";
                    }
                }
            }
        } else {
            echo "❌ Erro ao consultar tabela: " . $conn->error . "<br>";
        }
        echo "<br>";
    }
    
    echo "<hr>";
    
    // Testar consulta dos produtos mais vendidos
    echo "<h3>🏆 Teste: Produtos Mais Vendidos</h3>";
    $sql = "SELECT p.nome, p.categoria, SUM(iv.quantidade) as total_vendido, 
                   COUNT(DISTINCT iv.venda_id) as num_vendas
            FROM itens_venda iv 
            JOIN produtos p ON iv.produto_id = p.id 
            GROUP BY p.id, p.nome, p.categoria 
            ORDER BY total_vendido DESC 
            LIMIT 5";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<strong>✅ Consulta funcionou! Produtos encontrados:</strong><br>";
        while($row = $result->fetch_assoc()) {
            echo "🥇 " . $row['nome'] . " (" . $row['categoria'] . "): " . $row['total_vendido'] . " vendidos<br>";
        }
    } else {
        echo "❌ Nenhum resultado na consulta: " . $conn->error . "<br>";
        echo "<strong>Possíveis causas:</strong><br>";
        echo "• Não há produtos cadastrados<br>";
        echo "• Não há vendas registradas<br>";  
        echo "• Não há itens_venda relacionados<br>";
    }
    
    $conn->close();
    
    echo "<br><br>";
    echo "<a href='criar_dados.php' style='margin-right: 10px;'>🗂️ Criar Dados de Exemplo</a>";
    echo "<a href='teste_endpoint.php' style='margin-right: 10px;'>🧪 Testar Endpoint</a>";
    echo "<a href='index.php'>🏠 Voltar ao Sistema</a>";
    
} catch (Exception $e) {
    echo "❌ <strong>Erro de conexão:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Verificar:</strong><br>";
    echo "• WAMP está rodando?<br>";
    echo "• Banco 'sistema_carrinho' existe?<br>";
    echo "• Credenciais estão corretas?<br>";
}
?>