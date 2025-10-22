<?php
/**
 * Script para criar tabela de pedidos no banco de dados
 */

try {
    $host = 'localhost';
    $dbname = 'sistema_carrinho';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Criação da Tabela de Pedidos</title>";
    echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:0 auto;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style></head><body>";
    echo "<h2>🛒 Criação da Tabela de Pedidos</h2>";
    
    // 1. Criar tabela de vendas
    echo "<h3>1. Criando tabela de vendas...</h3>";
    try {
        $sql = "CREATE TABLE IF NOT EXISTS vendas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            total DECIMAL(10, 2) NOT NULL,
            forma_pagamento ENUM('dinheiro', 'pix', 'cartao') DEFAULT 'dinheiro',
            valor_pago DECIMAL(10, 2) DEFAULT 0,
            data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            INDEX idx_usuario_id (usuario_id),
            INDEX idx_data_venda (data_venda)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "<p class='success'>✅ Tabela 'vendas' criada com sucesso!</p>";
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro ao criar tabela vendas: " . $e->getMessage() . "</p>";
    }
    
    // 2. Criar tabela de pedidos
    echo "<h3>2. Criando tabela de pedidos...</h3>";
    try {
        $sql = "CREATE TABLE IF NOT EXISTS pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome_cliente VARCHAR(255),
            telefone_cliente VARCHAR(20),
            produtos JSON NOT NULL,
            total DECIMAL(10, 2) NOT NULL,
            usuario_vendedor_id INT,
            status ENUM('pendente', 'em_preparo', 'pronto', 'entregue', 'cancelado') DEFAULT 'pendente',
            data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            observacoes TEXT,
            FOREIGN KEY (usuario_vendedor_id) REFERENCES usuarios(id) ON DELETE SET NULL,
            INDEX idx_status (status),
            INDEX idx_data_pedido (data_pedido),
            INDEX idx_usuario_vendedor (usuario_vendedor_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "<p class='success'>✅ Tabela 'pedidos' criada com sucesso!</p>";
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro ao criar tabela pedidos: " . $e->getMessage() . "</p>";
    }
    
    // 3. Inserir alguns pedidos de exemplo para teste
    echo "<h3>3. Inserindo pedidos de exemplo...</h3>";
    try {
        $pedidosExemplo = [
            [
                'nome_cliente' => 'João Silva',
                'telefone_cliente' => '(11) 99999-1111',
                'produtos' => json_encode([
                    ['id' => 1, 'nome' => 'Água Gelada', 'quantidade' => 2, 'preco' => 3.00],
                    ['id' => 2, 'nome' => 'Refrigerante', 'quantidade' => 1, 'preco' => 5.00]
                ]),
                'total' => 11.00,
                'status' => 'pendente'
            ],
            [
                'nome_cliente' => 'Maria Santos',
                'telefone_cliente' => '(11) 99999-2222',
                'produtos' => json_encode([
                    ['id' => 3, 'nome' => 'Sanduíche', 'quantidade' => 1, 'preco' => 12.00],
                    ['id' => 1, 'nome' => 'Água Gelada', 'quantidade' => 1, 'preco' => 3.00]
                ]),
                'total' => 15.00,
                'status' => 'em_preparo'
            ],
            [
                'nome_cliente' => 'Pedro Costa',
                'telefone_cliente' => '(11) 99999-3333',
                'produtos' => json_encode([
                    ['id' => 4, 'nome' => 'Açaí', 'quantidade' => 1, 'preco' => 8.00]
                ]),
                'total' => 8.00,
                'status' => 'pronto'
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO pedidos (nome_cliente, telefone_cliente, produtos, total, status, data_pedido) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        foreach ($pedidosExemplo as $pedido) {
            $stmt->execute([
                $pedido['nome_cliente'],
                $pedido['telefone_cliente'],
                $pedido['produtos'],
                $pedido['total'],
                $pedido['status']
            ]);
        }
        
        echo "<p class='success'>✅ " . count($pedidosExemplo) . " pedidos de exemplo inseridos!</p>";
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro ao inserir pedidos de exemplo: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>4. Sistema de vendas e pedidos criado com sucesso! ✅</h3>";
    echo "<div class='info'>";
    echo "<h4>📋 Funcionalidades disponíveis:</h4>";
    echo "<ul>";
    echo "<li>✅ Tabela de vendas criada para registro de vendas</li>";
    echo "<li>✅ Tabela de pedidos criada com campos completos</li>";
    echo "<li>✅ Status de pedido: pendente, em_preparo, pronto, entregue, cancelado</li>";
    echo "<li>✅ Relacionamento com usuário vendedor</li>";
    echo "<li>✅ Armazenamento de produtos em JSON</li>";
    echo "<li>✅ Índices para melhor performance</li>";
    echo "<li>✅ Pedidos de exemplo para teste</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<br><a href='public/index.php' style='background:#0066cc;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🏠 Voltar ao Sistema</a>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>