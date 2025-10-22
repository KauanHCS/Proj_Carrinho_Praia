<?php
require_once 'config/database.php';

try {
    echo "<h2>Adicionando coluna numero_pedido...</h2>";
    
    $conn = getConnection();
    
    // Verificar se a coluna numero_pedido existe
    $result = $conn->query("SHOW COLUMNS FROM pedidos LIKE 'numero_pedido'");
    
    if ($result->num_rows == 0) {
        echo "<p>Coluna 'numero_pedido' não existe. Adicionando...</p>";
        
        // Adicionar coluna
        $conn->query("ALTER TABLE pedidos ADD COLUMN numero_pedido VARCHAR(20) AFTER id");
        echo "<p>✓ Coluna adicionada</p>";
        
        // Gerar números para pedidos existentes
        $result = $conn->query("SELECT id FROM pedidos ORDER BY id");
        $contador = 1;
        while ($row = $result->fetch_assoc()) {
            $numeroPedido = 'PED-' . str_pad($contador, 6, '0', STR_PAD_LEFT);
            $stmt = $conn->prepare("UPDATE pedidos SET numero_pedido = ? WHERE id = ?");
            $stmt->bind_param("si", $numeroPedido, $row['id']);
            $stmt->execute();
            $contador++;
        }
        echo "<p>✓ Números de pedido gerados para {$result->num_rows} pedido(s)</p>";
        
        // Adicionar índice único
        $conn->query("ALTER TABLE pedidos ADD UNIQUE KEY idx_numero_pedido (numero_pedido)");
        echo "<p>✓ Índice único criado</p>";
        
    } else {
        echo "<p>✓ Coluna 'numero_pedido' já existe</p>";
    }
    
    closeConnection($conn);
    
    echo "<hr><h3>✅ Atualização concluída!</h3>";
    echo "<p><a href='teste_pedidos.php'>Testar novamente</a> | <a href='public/index.php'>Voltar ao sistema</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>
