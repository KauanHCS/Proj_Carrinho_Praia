<?php
// Script para atualizar banco de dados - adicionar campos de preço
require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "🔧 Atualizando estrutura do banco de dados...<br><br>";
    
    // Verificar se as colunas já existem
    $checkColumns = $conn->query("SHOW COLUMNS FROM produtos LIKE 'preco_compra'");
    
    if ($checkColumns->num_rows == 0) {
        // Adicionar coluna preco_compra
        $sql1 = "ALTER TABLE produtos ADD COLUMN preco_compra DECIMAL(10,2) DEFAULT 0.00 AFTER preco";
        if ($conn->query($sql1)) {
            echo "✅ Coluna 'preco_compra' adicionada com sucesso<br>";
        } else {
            throw new Exception("Erro ao adicionar coluna preco_compra: " . $conn->error);
        }
        
        // Renomear coluna preco para preco_venda
        $sql2 = "ALTER TABLE produtos CHANGE preco preco_venda DECIMAL(10,2) NOT NULL";
        if ($conn->query($sql2)) {
            echo "✅ Coluna 'preco' renomeada para 'preco_venda'<br>";
        } else {
            throw new Exception("Erro ao renomear coluna: " . $conn->error);
        }
        
        // Atualizar produtos existentes - assumir que preço atual é de venda
        // e calcular preço de compra baseado em margem de 60% (preço_compra = preco_venda * 0.6)
        $sql3 = "UPDATE produtos SET preco_compra = ROUND(preco_venda * 0.6, 2) WHERE preco_compra = 0.00";
        if ($conn->query($sql3)) {
            $affected = $conn->affected_rows;
            echo "✅ Atualizados $affected produtos com preço de compra calculado (60% do preço de venda)<br>";
        } else {
            throw new Exception("Erro ao atualizar produtos: " . $conn->error);
        }
        
        // Adicionar coluna para margem de lucro (calculada)
        $sql4 = "ALTER TABLE produtos ADD COLUMN margem_lucro DECIMAL(5,2) AS (
            CASE 
                WHEN preco_compra > 0 THEN ROUND(((preco_venda - preco_compra) / preco_compra) * 100, 2)
                ELSE 0 
            END
        ) STORED AFTER preco_compra";
        if ($conn->query($sql4)) {
            echo "✅ Coluna calculada 'margem_lucro' adicionada<br>";
        } else {
            throw new Exception("Erro ao adicionar coluna margem_lucro: " . $conn->error);
        }
        
        echo "<br><div style='background:#d4edda;padding:15px;border-radius:5px;border:1px solid #c3e6cb;color:#155724;'>";
        echo "<h3>🎉 Atualização Concluída!</h3>";
        echo "<strong>Alterações realizadas:</strong><br>";
        echo "• Campo 'preco_compra' adicionado<br>";
        echo "• Campo 'preco' renomeado para 'preco_venda'<br>";
        echo "• Campo 'margem_lucro' calculado automaticamente<br>";
        echo "• Produtos existentes atualizados com preço de compra estimado<br>";
        echo "</div>";
        
    } else {
        echo "<div style='background:#fff3cd;padding:15px;border-radius:5px;border:1px solid #ffecb5;color:#856404;'>";
        echo "ℹ️ <strong>Banco de dados já está atualizado!</strong><br>";
        echo "As colunas de preço já existem na tabela.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;border:1px solid #f5c6cb;color:#721c24;'>";
    echo "❌ <strong>Erro:</strong> " . $e->getMessage();
    echo "</div>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
</style>

<br><br>
<a href="./" style="background:#007bff;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;">🏠 Voltar ao Sistema</a>