<?php
require_once 'config/database.php';

try {
    echo "<h2>Atualizando banco de dados para função financeiro...</h2>";
    
    $conn = getConnection();
    
    // Modify the enum to include 'financeiro'
    $alterEnum = "ALTER TABLE usuarios MODIFY COLUMN funcao_funcionario ENUM('anotar_pedido', 'fazer_pedido', 'ambos', 'financeiro', 'financeiro_e_anotar') DEFAULT NULL";
    if ($conn->query($alterEnum)) {
        echo "<p>✓ Enum de função atualizado com 'financeiro' e 'financeiro_e_anotar'</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Enum já pode estar atualizado: " . $conn->error . "</p>";
    }
    
    // Add payment status to vendas table if not exists
    $checkPaymentStatus = "SHOW COLUMNS FROM vendas LIKE 'status_pagamento'";
    $result = $conn->query($checkPaymentStatus);
    
    if ($result->num_rows == 0) {
        $addPaymentStatus = "ALTER TABLE vendas ADD COLUMN status_pagamento ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente'";
        $conn->query($addPaymentStatus);
        echo "<p>✓ Coluna 'status_pagamento' adicionada à tabela vendas</p>";
    } else {
        echo "<p>✓ Coluna 'status_pagamento' já existe</p>";
    }
    
    // Check if forma_pagamento exists (original column) or metodo_pagamento
    $checkFormaPagamento = "SHOW COLUMNS FROM vendas LIKE 'forma_pagamento'";
    $resultForma = $conn->query($checkFormaPagamento);
    
    $checkMetodoPagamento = "SHOW COLUMNS FROM vendas LIKE 'metodo_pagamento'";
    $resultMetodo = $conn->query($checkMetodoPagamento);
    
    if ($resultForma->num_rows > 0) {
        echo "<p>✓ Coluna 'forma_pagamento' já existe (será usada como metodo_pagamento)</p>";
    } else if ($resultMetodo->num_rows == 0) {
        $addPaymentMethod = "ALTER TABLE vendas ADD COLUMN metodo_pagamento ENUM('dinheiro', 'cartao', 'pix') DEFAULT NULL";
        $conn->query($addPaymentMethod);
        echo "<p>✓ Coluna 'metodo_pagamento' adicionada à tabela vendas</p>";
    } else {
        echo "<p>✓ Coluna 'metodo_pagamento' já existe</p>";
    }
    
    // Add processed_by_financeiro to vendas table if not exists
    $checkProcessedBy = "SHOW COLUMNS FROM vendas LIKE 'processado_por_financeiro'";
    $result = $conn->query($checkProcessedBy);
    
    if ($result->num_rows == 0) {
        $addProcessedBy = "ALTER TABLE vendas ADD COLUMN processado_por_financeiro INT DEFAULT NULL";
        $conn->query($addProcessedBy);
        echo "<p>✓ Coluna 'processado_por_financeiro' adicionada à tabela vendas</p>";
        
        // Add foreign key separately (safer)
        $addForeignKey = "ALTER TABLE vendas ADD FOREIGN KEY (processado_por_financeiro) REFERENCES usuarios(id)";
        if ($conn->query($addForeignKey)) {
            echo "<p>✓ Foreign key adicionada para processado_por_financeiro</p>";
        }
    } else {
        echo "<p>✓ Coluna 'processado_por_financeiro' já existe</p>";
    }
    
    // Add observacoes_pagamento to vendas table if not exists
    $checkObservacoes = "SHOW COLUMNS FROM vendas LIKE 'observacoes_pagamento'";
    $result = $conn->query($checkObservacoes);
    
    if ($result->num_rows == 0) {
        $addObservacoes = "ALTER TABLE vendas ADD COLUMN observacoes_pagamento TEXT DEFAULT NULL";
        $conn->query($addObservacoes);
        echo "<p>✓ Coluna 'observacoes_pagamento' adicionada à tabela vendas</p>";
    } else {
        echo "<p>✓ Coluna 'observacoes_pagamento' já existe</p>";
    }
    
    // Add data_pagamento to vendas table if not exists
    $checkDataPagamento = "SHOW COLUMNS FROM vendas LIKE 'data_pagamento'";
    $result = $conn->query($checkDataPagamento);
    
    if ($result->num_rows == 0) {
        $addDataPagamento = "ALTER TABLE vendas ADD COLUMN data_pagamento DATETIME DEFAULT NULL";
        $conn->query($addDataPagamento);
        echo "<p>✓ Coluna 'data_pagamento' adicionada à tabela vendas</p>";
    } else {
        echo "<p>✓ Coluna 'data_pagamento' já existe</p>";
    }
    
    // Check if cliente_nome exists (original column) or nome_cliente
    $checkClienteNome = "SHOW COLUMNS FROM vendas LIKE 'cliente_nome'";
    $resultCliente = $conn->query($checkClienteNome);
    
    $checkNomeCliente = "SHOW COLUMNS FROM vendas LIKE 'nome_cliente'";
    $resultNome = $conn->query($checkNomeCliente);
    
    if ($resultCliente->num_rows > 0) {
        echo "<p>✓ Coluna 'cliente_nome' já existe (será usada como nome_cliente)</p>";
    } else if ($resultNome->num_rows == 0) {
        $addNomeCliente = "ALTER TABLE vendas ADD COLUMN nome_cliente VARCHAR(100) DEFAULT NULL";
        $conn->query($addNomeCliente);
        echo "<p>✓ Coluna 'nome_cliente' adicionada à tabela vendas</p>";
    } else {
        echo "<p>✓ Coluna 'nome_cliente' já existe</p>";
    }
    
    closeConnection($conn);
    
    echo "<h3>Atualização concluída com sucesso!</h3>";
    echo "<p><a href='public/index.php'>Voltar ao sistema</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro na atualização: " . $e->getMessage() . "</p>";
}
?>
