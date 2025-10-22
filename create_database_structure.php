<?php
/**
 * Script para criar a estrutura completa do banco de dados
 * 
 * Cria todas as tabelas necessárias para o sistema de carrinho de praia,
 * incluindo a nova estrutura para códigos de funcionários.
 */

// Headers para evitar cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Configuração do banco de dados
$host = 'localhost';
$dbname = 'carrinho_praia';
$username = 'root';
$password = '';

try {
    // Conectar ao MySQL (sem especificar o banco)
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!DOCTYPE html>";
    echo "<html lang='pt-BR'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Criação da Estrutura do Banco</title>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css' rel='stylesheet'>";
    echo "<style>";
    echo "body { background: linear-gradient(135deg, #0066cc, #0099ff); min-height: 100vh; display: flex; align-items: center; }";
    echo ".container { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); max-width: 900px; }";
    echo ".table-sql { background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 0.9em; margin: 10px 0; }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    echo "<div class='container'>";
    echo "<h2 class='text-center mb-4'><i class='bi bi-database-gear'></i> Estrutura do Banco de Dados</h2>";
    
    // Criar banco de dados se não existir
    echo "<div class='alert alert-info'>";
    echo "<i class='bi bi-info-circle'></i> Verificando/criando banco de dados...";
    echo "</div>";
    
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<div class='alert alert-success'>";
        echo "<i class='bi bi-check-circle'></i> Banco de dados '$dbname' criado/verificado com sucesso!";
        echo "</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>";
        echo "<i class='bi bi-exclamation-circle'></i> Erro ao criar banco: " . $e->getMessage();
        echo "</div>";
    }
    
    // Conectar ao banco específico
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar tabelas existentes
    $stmt = $pdo->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'><strong>Tabelas Existentes</strong></div>";
    echo "<div class='card-body'>";
    if (empty($existingTables)) {
        echo "<p class='text-muted'>Nenhuma tabela encontrada. Será criada estrutura completa.</p>";
    } else {
        echo "<div class='row'>";
        foreach ($existingTables as $table) {
            echo "<div class='col-md-4 mb-2'>";
            echo "<span class='badge bg-info'>$table</span>";
            echo "</div>";
        }
        echo "</div>";
    }
    echo "</div>";
    echo "</div>";
    
    // Array com todas as tabelas e suas estruturas
    $tables = [
        'usuarios' => "
            CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                telefone VARCHAR(20),
                senha VARCHAR(255) NOT NULL,
                tipo_usuario ENUM('administrador', 'funcionario') DEFAULT 'administrador',
                funcao_funcionario ENUM('anotar_pedido', 'fazer_pedido', 'ambos') NULL,
                codigo_admin INT NULL,
                codigo_unico VARCHAR(10) NULL,
                data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ativo BOOLEAN DEFAULT TRUE,
                INDEX idx_email (email),
                INDEX idx_tipo_usuario (tipo_usuario)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'codigos_funcionarios' => "
            CREATE TABLE IF NOT EXISTS codigos_funcionarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                codigo VARCHAR(10) UNIQUE NOT NULL,
                admin_id INT NOT NULL,
                funcao VARCHAR(50) NULL,
                usado BOOLEAN DEFAULT FALSE,
                usado_por_usuario INT NULL,
                ativo BOOLEAN DEFAULT TRUE,
                data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                data_uso TIMESTAMP NULL,
                INDEX idx_codigo (codigo),
                INDEX idx_admin_id (admin_id),
                INDEX idx_usado (usado),
                FOREIGN KEY (admin_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                FOREIGN KEY (usado_por_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'produtos' => "
            CREATE TABLE IF NOT EXISTS produtos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL,
                descricao TEXT,
                preco DECIMAL(10,2) NOT NULL,
                categoria VARCHAR(100),
                estoque INT DEFAULT 0,
                estoque_minimo INT DEFAULT 5,
                ativo BOOLEAN DEFAULT TRUE,
                data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_categoria (categoria),
                INDEX idx_ativo (ativo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'vendas' => "
            CREATE TABLE IF NOT EXISTS vendas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                cliente_nome VARCHAR(255),
                cliente_telefone VARCHAR(20),
                total DECIMAL(10,2) NOT NULL,
                desconto DECIMAL(10,2) DEFAULT 0,
                forma_pagamento ENUM('dinheiro', 'cartao', 'pix') DEFAULT 'dinheiro',
                status ENUM('pendente', 'concluida', 'cancelada') DEFAULT 'pendente',
                observacoes TEXT,
                data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_usuario_id (usuario_id),
                INDEX idx_status (status),
                INDEX idx_data_venda (data_venda),
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'itens_venda' => "
            CREATE TABLE IF NOT EXISTS itens_venda (
                id INT AUTO_INCREMENT PRIMARY KEY,
                venda_id INT NOT NULL,
                produto_id INT NOT NULL,
                quantidade INT NOT NULL,
                preco_unitario DECIMAL(10,2) NOT NULL,
                subtotal DECIMAL(10,2) NOT NULL,
                INDEX idx_venda_id (venda_id),
                INDEX idx_produto_id (produto_id),
                FOREIGN KEY (venda_id) REFERENCES vendas(id) ON DELETE CASCADE,
                FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    // Criar cada tabela
    echo "<h4><i class='bi bi-table'></i> Criando/Verificando Tabelas</h4>";
    
    foreach ($tables as $tableName => $sql) {
        echo "<div class='card mb-3'>";
        echo "<div class='card-header d-flex justify-content-between align-items-center'>";
        echo "<strong>Tabela: $tableName</strong>";
        
        try {
            $pdo->exec($sql);
            echo "<span class='badge bg-success'>✓ OK</span>";
            echo "</div>";
            echo "<div class='card-body'>";
            echo "<p class='text-success mb-2'><i class='bi bi-check-circle'></i> Tabela criada/verificada com sucesso!</p>";
            
            // Mostrar estrutura da tabela
            $stmt = $pdo->query("DESCRIBE $tableName");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<div class='table-responsive'>";
            echo "<table class='table table-sm'>";
            echo "<thead><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr></thead>";
            echo "<tbody>";
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td><strong>" . $column['Field'] . "</strong></td>";
                echo "<td><code>" . $column['Type'] . "</code></td>";
                echo "<td>" . $column['Null'] . "</td>";
                echo "<td>" . $column['Key'] . "</td>";
                echo "<td>" . ($column['Default'] ?? '<em>NULL</em>') . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<span class='badge bg-danger'>✗ ERRO</span>";
            echo "</div>";
            echo "<div class='card-body'>";
            echo "<p class='text-danger'><i class='bi bi-exclamation-circle'></i> Erro: " . $e->getMessage() . "</p>";
        }
        
        echo "</div>";
        echo "</div>";
    }
    
    // Inserir dados de exemplo se necessário
    echo "<h4><i class='bi bi-person-plus'></i> Usuário Administrador Padrão</h4>";
    
    // Verificar se já existe admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE tipo_usuario = 'administrador'");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();
    
    if ($adminCount == 0) {
        echo "<div class='alert alert-info'>";
        echo "<i class='bi bi-info-circle'></i> Criando usuário administrador padrão...";
        echo "</div>";
        
        try {
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (nome, email, senha, tipo_usuario, codigo_unico) 
                VALUES ('Administrador', 'admin@carrinho.com', ?, 'administrador', '123456')
            ");
            $stmt->execute([$adminPassword]);
            
            echo "<div class='alert alert-success'>";
            echo "<i class='bi bi-check-circle'></i> Usuário administrador criado!";
            echo "<br><strong>Email:</strong> admin@carrinho.com";
            echo "<br><strong>Senha:</strong> admin123";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='alert alert-warning'>";
            echo "<i class='bi bi-exclamation-triangle'></i> Erro ao criar admin: " . $e->getMessage();
            echo "</div>";
        }
    } else {
        echo "<div class='alert alert-info'>";
        echo "<i class='bi bi-info-circle'></i> Já existe(m) $adminCount administrador(es) no sistema.";
        echo "</div>";
    }
    
    // Produtos de exemplo
    echo "<h4><i class='bi bi-box'></i> Produtos de Exemplo</h4>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos");
    $stmt->execute();
    $produtoCount = $stmt->fetchColumn();
    
    if ($produtoCount == 0) {
        echo "<div class='alert alert-info'>";
        echo "<i class='bi bi-info-circle'></i> Inserindo produtos de exemplo...";
        echo "</div>";
        
        $produtosExemplo = [
            ['Água 500ml', 'Água mineral natural', 2.50, 'Bebidas', 100],
            ['Refrigerante Lata', 'Refrigerante 350ml', 4.00, 'Bebidas', 50],
            ['Suco Natural', 'Suco de frutas natural', 6.00, 'Bebidas', 30],
            ['Sanduíche Natural', 'Sanduíche com peito de peru', 8.00, 'Lanches', 20],
            ['Batata Chips', 'Batata chips 100g', 5.50, 'Lanches', 40],
            ['Biscoito Wafer', 'Wafer chocolate 140g', 3.50, 'Lanches', 25],
            ['Protetor Solar', 'FPS 60 - 120ml', 25.00, 'Acessórios', 15],
            ['Boné', 'Boné com proteção UV', 15.00, 'Acessórios', 10],
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO produtos (nome, descricao, preco, categoria, estoque) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $inseridos = 0;
        foreach ($produtosExemplo as $produto) {
            try {
                $stmt->execute($produto);
                $inseridos++;
            } catch (Exception $e) {
                // Ignorar erro de duplicata
            }
        }
        
        echo "<div class='alert alert-success'>";
        echo "<i class='bi bi-check-circle'></i> $inseridos produtos de exemplo inseridos!";
        echo "</div>";
        
    } else {
        echo "<div class='alert alert-info'>";
        echo "<i class='bi bi-info-circle'></i> Já existem $produtoCount produtos no sistema.";
        echo "</div>";
    }
    
    echo "<div class='alert alert-success'>";
    echo "<i class='bi bi-check-circle'></i> <strong>Estrutura do banco criada com sucesso!</strong>";
    echo "<br><br>";
    echo "<strong>Sistema pronto para uso:</strong>";
    echo "<ul class='mt-2'>";
    echo "<li>✅ Todas as tabelas criadas</li>";
    echo "<li>✅ Relacionamentos configurados</li>";
    echo "<li>✅ Índices otimizados</li>";
    echo "<li>✅ Dados de exemplo inseridos</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='text-center'>";
    echo "<a href='public/index.php' class='btn btn-primary me-2'>";
    echo "<i class='bi bi-house'></i> Ir para Sistema Principal";
    echo "</a>";
    echo "<a href='public/login.php' class='btn btn-outline-primary'>";
    echo "<i class='bi bi-box-arrow-in-right'></i> Ir para Login";
    echo "</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<i class='bi bi-exclamation-circle'></i> <strong>Erro de conexão:</strong> " . $e->getMessage();
    echo "</div>";
    echo "<p class='text-muted'>Verifique se:</p>";
    echo "<ul>";
    echo "<li>O WAMP/XAMPP está rodando</li>";
    echo "<li>O MySQL está ativo</li>";
    echo "<li>As credenciais estão corretas (usuário: root, sem senha por padrão)</li>";
    echo "</ul>";
}

echo "</div>";
echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
echo "</body>";
echo "</html>";
?>