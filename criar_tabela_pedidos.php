<?php
require_once 'config/database.php';

try {
    echo "<h2>Verificando e criando tabela pedidos...</h2>";
    
    $conn = getConnection();
    
    // Verificar se a tabela pedidos existe
    $result = $conn->query("SHOW TABLES LIKE 'pedidos'");
    
    if ($result->num_rows == 0) {
        echo "<p style='color: orange;'>⚠️ Tabela 'pedidos' não existe. Criando...</p>";
        
        $createTable = "
        CREATE TABLE pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero_pedido VARCHAR(20) UNIQUE NOT NULL,
            cliente_nome VARCHAR(100),
            cliente_telefone VARCHAR(20),
            produtos_json TEXT NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            observacoes TEXT,
            status ENUM('pendente', 'em_preparo', 'pronto', 'entregue', 'cancelado') DEFAULT 'pendente',
            usuario_vendedor_id INT,
            data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_vendedor (usuario_vendedor_id),
            INDEX idx_data (data_pedido),
            CONSTRAINT fk_pedido_vendedor FOREIGN KEY (usuario_vendedor_id) 
                REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        if ($conn->query($createTable)) {
            echo "<p>✓ Tabela 'pedidos' criada com sucesso!</p>";
            
            // Criar índice adicional para número do pedido
            $conn->query("CREATE INDEX idx_numero_pedido ON pedidos(numero_pedido)");
            echo "<p>✓ Índices criados</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao criar tabela: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✓ Tabela 'pedidos' já existe</p>";
        
        // Verificar estrutura
        echo "<h3>Estrutura atual:</h3>";
        $result = $conn->query("DESCRIBE pedidos");
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    closeConnection($conn);
    
    echo "<hr><h3>✅ Verificação concluída!</h3>";
    echo "<p><a href='public/index.php'>Voltar ao sistema</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>
