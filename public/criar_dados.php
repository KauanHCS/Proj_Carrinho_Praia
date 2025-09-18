<?php
// Configurações do banco
require_once '../config/database.php';

echo "<h2>🔧 USANDO BANCO: sistema_carrinho</h2>";

try {
    $conn = getConnection();
    
    echo "<h2>🗂️ CRIANDO DADOS DE EXEMPLO</h2>";
    
    // Verificar dados existentes
    echo "<h3>📊 Dados Atuais:</h3>";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM produtos");
    $row = $result->fetch_assoc();
    echo "Produtos: " . $row['total'] . "<br>";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM vendas");
    $row = $result->fetch_assoc();
    echo "Vendas: " . $row['total'] . "<br>";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM itens_venda");
    $row = $result->fetch_assoc();
    echo "Itens vendidos: " . $row['total'] . "<br><br>";
    
    // Inserir produtos de exemplo
    echo "<h3>🛍️ Criando Produtos:</h3>";
    
    $produtos = [
        [1, 'Coca-Cola Lata', 'bebida', 2.50, 5.00, 50, 10, 1],
        [2, 'Água Mineral', 'bebida', 1.00, 3.00, 30, 5, 1], 
        [3, 'Sanduíche Natural', 'comida', 3.00, 8.00, 20, 5, 1],
        [4, 'Protetor Solar', 'acessorio', 15.00, 30.00, 15, 3, 1],
        [5, 'Biscoito Água e Sal', 'comida', 1.50, 4.00, 25, 5, 1]
    ];
    
    foreach ($produtos as $produto) {
        $stmt = $conn->prepare("INSERT INTO produtos (id, nome, categoria, preco_compra, preco_venda, quantidade, limite_minimo, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE nome = VALUES(nome)");
        $stmt->bind_param("issddiid", ...$produto);
        if ($stmt->execute()) {
            echo "✅ " . $produto[1] . " criado<br>";
        }
    }
    
    // Inserir vendas de exemplo
    echo "<h3>💰 Criando Vendas:</h3>";
    
    $vendas = [
        [1, 'pix', 16.00, 1, 'concluida'],
        [2, 'dinheiro', 11.00, 1, 'concluida'], 
        [3, 'cartao', 8.00, 1, 'concluida'],
        [4, 'pix', 22.00, 1, 'concluida'],
        [5, 'dinheiro', 14.00, 1, 'concluida']
    ];
    
    foreach ($vendas as $venda) {
        $stmt = $conn->prepare("INSERT INTO vendas (id, data, forma_pagamento, total, usuario_id, status) VALUES (?, NOW(), ?, ?, ?, ?) ON DUPLICATE KEY UPDATE total = VALUES(total)");
        $stmt->bind_param("isdis", $venda[0], $venda[1], $venda[2], $venda[3], $venda[4]);
        if ($stmt->execute()) {
            echo "✅ Venda #" . $venda[0] . " - R$ " . number_format($venda[2], 2, ',', '.') . "<br>";
        }
    }
    
    // Inserir itens de venda
    echo "<h3>🛒 Criando Itens de Venda:</h3>";
    
    $itens = [
        [1, 1, 2, 5.00], // Venda 1: 2 Coca-Colas
        [1, 2, 2, 3.00], // Venda 1: 2 Águas
        [2, 1, 1, 5.00], // Venda 2: 1 Coca-Cola
        [2, 2, 2, 3.00], // Venda 2: 2 Águas
        [3, 3, 1, 8.00], // Venda 3: 1 Sanduíche
        [4, 1, 3, 5.00], // Venda 4: 3 Coca-Colas
        [4, 5, 2, 4.00], // Venda 4: 2 Biscoitos
        [5, 2, 1, 3.00], // Venda 5: 1 Água
        [5, 3, 1, 8.00], // Venda 5: 1 Sanduíche
        [5, 5, 1, 4.00]  // Venda 5: 1 Biscoito
    ];
    
    foreach ($itens as $item) {
        $stmt = $conn->prepare("INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE quantidade = VALUES(quantidade)");
        $stmt->bind_param("iiid", ...$item);
        if ($stmt->execute()) {
            echo "✅ Item venda #" . $item[0] . " produto #" . $item[1] . " - " . $item[2] . "x<br>";
        }
    }
    
    // Verificar resultado final
    echo "<h3>📈 Dados Finais:</h3>";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM produtos");
    $row = $result->fetch_assoc();
    echo "Total produtos: " . $row['total'] . "<br>";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM vendas");
    $row = $result->fetch_assoc();
    echo "Total vendas: " . $row['total'] . "<br>";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM itens_venda");
    $row = $result->fetch_assoc();
    echo "Total itens vendidos: " . $row['total'] . "<br><br>";
    
    // Testar consulta dos produtos mais vendidos
    echo "<h3>🏆 Produtos Mais Vendidos:</h3>";
    $sql = "SELECT p.nome, p.categoria, SUM(iv.quantidade) as total_vendido, 
                   COUNT(DISTINCT iv.venda_id) as num_vendas
            FROM itens_venda iv 
            JOIN produtos p ON iv.produto_id = p.id 
            GROUP BY p.id, p.nome, p.categoria 
            ORDER BY total_vendido DESC 
            LIMIT 5";
    
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "🥇 " . $row['nome'] . " (" . $row['categoria'] . "): " . $row['total_vendido'] . " vendidos<br>";
        }
    } else {
        echo "❌ Nenhum produto encontrado na consulta";
    }
    
    $conn->close();
    
    echo "<br><br>✅ <strong>Dados criados com sucesso!</strong><br>";
    echo "<a href='index.php' class='btn btn-primary'>Voltar ao Sistema</a>";
    
} catch (Exception $e) {
    echo "❌ <strong>Erro:</strong> " . $e->getMessage();
}
?>