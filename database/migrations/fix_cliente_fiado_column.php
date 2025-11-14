<?php
$pdo = new PDO('mysql:host=localhost;dbname=sistema_carrinho;charset=utf8mb4', 'root', '');
try {
    $pdo->exec('ALTER TABLE vendas ADD COLUMN cliente_fiado_id INT(11) DEFAULT NULL AFTER usuario_id');
    echo "✅ Coluna cliente_fiado_id adicionada!\n";
} catch(PDOException $e) {
    if(strpos($e->getMessage(), 'Duplicate') !== false) {
        echo "✅ Coluna já existe!\n";
    } else {
        echo "Erro: " . $e->getMessage() . "\n";
    }
}
try {
    $pdo->exec('ALTER TABLE vendas ADD KEY idx_cliente_fiado (cliente_fiado_id)');
    echo "✅ Índice adicionado!\n";
} catch(PDOException $e) {
    if(strpos($e->getMessage(), 'Duplicate') !== false) {
        echo "✅ Índice já existe!\n";
    }
}
?>
