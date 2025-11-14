<?php
/**
 * Script para executar migration de pagamento misto
 */

try {
    $pdo = new PDO('mysql:host=localhost;dbname=sistema_carrinho', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Iniciando migração...\n\n";
    
    // 1. Adicionar colunas para pagamento secundário
    echo "1. Adicionando colunas para pagamento secundário...\n";
    try {
        $pdo->exec("ALTER TABLE vendas ADD COLUMN forma_pagamento_secundaria VARCHAR(50) NULL");
        echo "   ✓ Coluna forma_pagamento_secundaria adicionada\n";
    } catch (Exception $e) {
        echo "   ⚠ forma_pagamento_secundaria: " . $e->getMessage() . "\n";
    }
    
    try {
        $pdo->exec("ALTER TABLE vendas ADD COLUMN valor_pago_secundario DECIMAL(10,2) NULL DEFAULT 0.00");
        echo "   ✓ Coluna valor_pago_secundario adicionada\n";
    } catch (Exception $e) {
        echo "   ⚠ valor_pago_secundario: " . $e->getMessage() . "\n";
    }
    
    // 2. Adicionar colunas para pagamento terciário
    echo "\n2. Adicionando colunas para pagamento terciário...\n";
    try {
        $pdo->exec("ALTER TABLE vendas ADD COLUMN forma_pagamento_terciaria VARCHAR(50) NULL");
        echo "   ✓ Coluna forma_pagamento_terciaria adicionada\n";
    } catch (Exception $e) {
        echo "   ⚠ forma_pagamento_terciaria: " . $e->getMessage() . "\n";
    }
    
    try {
        $pdo->exec("ALTER TABLE vendas ADD COLUMN valor_pago_terciario DECIMAL(10,2) NULL DEFAULT 0.00");
        echo "   ✓ Coluna valor_pago_terciario adicionada\n";
    } catch (Exception $e) {
        echo "   ⚠ valor_pago_terciario: " . $e->getMessage() . "\n";
    }
    
    // 3. Verificar estrutura final
    echo "\n3. Verificando estrutura da tabela vendas...\n";
    $stmt = $pdo->query("DESCRIBE vendas");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $foundColumns = [];
    foreach ($columns as $col) {
        if (in_array($col['Field'], ['forma_pagamento', 'valor_pago', 'forma_pagamento_secundaria', 'valor_pago_secundario', 'forma_pagamento_terciaria', 'valor_pago_terciario'])) {
            $foundColumns[] = $col['Field'];
            echo "   ✓ {$col['Field']} ({$col['Type']})\n";
        }
    }
    
    echo "\n✅ Migração concluída com sucesso!\n";
    echo "Total de colunas de pagamento: " . count($foundColumns) . "\n";
    
} catch (Exception $e) {
    echo "\n❌ Erro na migração: " . $e->getMessage() . "\n";
    exit(1);
}
?>
