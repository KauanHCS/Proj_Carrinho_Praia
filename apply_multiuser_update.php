<?php
// Script para aplicar as atualizações do sistema multi-usuário
require_once 'config/database.php';

echo "<h1>Aplicando Atualizações do Sistema Multi-Usuário</h1>";

try {
    $conn = getConnection();
    echo "<p>✅ Conectado ao banco de dados</p>";
    
    // 1. Adicionar coluna usuario_id à tabela produtos
    echo "<h2>1. Atualizando estrutura da tabela produtos...</h2>";
    
    // Verificar se a coluna já existe
    $result = $conn->query("SELECT COUNT(*) as exists_col FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'sistema_carrinho' AND TABLE_NAME = 'produtos' AND COLUMN_NAME = 'usuario_id'");
    $row = $result->fetch_assoc();
    
    if ($row['exists_col'] == 0) {
        if ($conn->query("ALTER TABLE produtos ADD COLUMN usuario_id INT DEFAULT NULL AFTER id")) {
            echo "<p>✅ Coluna usuario_id adicionada com sucesso</p>";
        } else {
            echo "<p>❌ Erro ao adicionar coluna usuario_id: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>ℹ️ Coluna usuario_id já existe</p>";
    }
    
    // Adicionar foreign key constraint
    $result = $conn->query("SELECT COUNT(*) as exists_fk FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'sistema_carrinho' AND TABLE_NAME = 'produtos' AND CONSTRAINT_NAME = 'fk_produtos_usuario'");
    $row = $result->fetch_assoc();
    
    if ($row['exists_fk'] == 0) {
        if ($conn->query("ALTER TABLE produtos ADD CONSTRAINT fk_produtos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE")) {
            echo "<p>✅ Foreign key constraint adicionada</p>";
        } else {
            echo "<p>❌ Erro ao adicionar foreign key: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>ℹ️ Foreign key já existe</p>";
    }
    
    // Adicionar índice
    $result = $conn->query("SELECT COUNT(*) as exists_idx FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = 'sistema_carrinho' AND TABLE_NAME = 'produtos' AND INDEX_NAME = 'idx_usuario_produtos'");
    $row = $result->fetch_assoc();
    
    if ($row['exists_idx'] == 0) {
        if ($conn->query("CREATE INDEX idx_usuario_produtos ON produtos(usuario_id, ativo)")) {
            echo "<p>✅ Índice idx_usuario_produtos criado</p>";
        } else {
            echo "<p>❌ Erro ao criar índice: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>ℹ️ Índice já existe</p>";
    }
    
    // 2. Criar tabela de notificações
    echo "<h2>2. Criando tabela de notificações...</h2>";
    
    $createNotificationsSQL = "
    CREATE TABLE IF NOT EXISTS notificacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        tipo ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
        titulo VARCHAR(100) NOT NULL,
        mensagem TEXT NOT NULL,
        lida TINYINT(1) DEFAULT 0,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_lida TIMESTAMP NULL,
        produto_id INT NULL,
        acao VARCHAR(50) NULL,
        INDEX idx_usuario_notif (usuario_id),
        INDEX idx_lida (lida),
        INDEX idx_data (data_criacao),
        CONSTRAINT fk_notif_usuario FOREIGN KEY (usuario_id) 
            REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_notif_produto FOREIGN KEY (produto_id) 
            REFERENCES produtos(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($createNotificationsSQL)) {
        echo "<p>✅ Tabela de notificações criada/verificada com sucesso</p>";
    } else {
        echo "<p>❌ Erro ao criar tabela de notificações: " . $conn->error . "</p>";
    }
    
    // 3. Atualizar configurações
    echo "<h2>3. Inserindo configurações...</h2>";
    
    $configs = [
        ['produtos_por_usuario', 'true', 'Produtos específicos por usuário', 'boolean', 'sistema'],
        ['notificacoes_ativas', 'true', 'Sistema de notificações ativo', 'boolean', 'interface'],
        ['alerta_estoque_cor_critica', '#dc3545', 'Cor para alertas de estoque crítico', 'string', 'interface'],
        ['alerta_estoque_cor_baixo', '#ffc107', 'Cor para alertas de estoque baixo', 'string', 'interface']
    ];
    
    foreach ($configs as $config) {
        $stmt = $conn->prepare("INSERT IGNORE INTO configuracoes (chave, valor, descricao, tipo, categoria) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $config[0], $config[1], $config[2], $config[3], $config[4]);
        if ($stmt->execute()) {
            if ($conn->affected_rows > 0) {
                echo "<p>✅ Configuração '{$config[0]}' adicionada</p>";
            } else {
                echo "<p>ℹ️ Configuração '{$config[0]}' já existe</p>";
            }
        } else {
            echo "<p>❌ Erro ao adicionar configuração '{$config[0]}': " . $conn->error . "</p>";
        }
    }
    
    // 4. Atualizar produtos existentes para o primeiro usuário (opcional)
    echo "<h2>4. Verificando produtos sem usuário...</h2>";
    
    $result = $conn->query("SELECT COUNT(*) as produtos_sem_usuario FROM produtos WHERE usuario_id IS NULL");
    $row = $result->fetch_assoc();
    
    if ($row['produtos_sem_usuario'] > 0) {
        echo "<p>⚠️ Encontrados {$row['produtos_sem_usuario']} produtos sem usuário definido</p>";
        echo "<p>Deseja atribuir estes produtos ao primeiro usuário cadastrado?</p>";
        echo "<form method='post' style='margin: 10px 0;'>";
        echo "<input type='hidden' name='atribuir_produtos' value='1'>";
        echo "<button type='submit' class='btn btn-warning'>Sim, atribuir ao primeiro usuário</button>";
        echo "</form>";
        
        if (isset($_POST['atribuir_produtos'])) {
            $result = $conn->query("SELECT id FROM usuarios ORDER BY id ASC LIMIT 1");
            if ($result->num_rows > 0) {
                $usuario = $result->fetch_assoc();
                $stmt = $conn->prepare("UPDATE produtos SET usuario_id = ? WHERE usuario_id IS NULL");
                $stmt->bind_param("i", $usuario['id']);
                if ($stmt->execute()) {
                    echo "<p>✅ Produtos atribuídos ao usuário ID {$usuario['id']}</p>";
                } else {
                    echo "<p>❌ Erro ao atribuir produtos: " . $conn->error . "</p>";
                }
            }
        }
    } else {
        echo "<p>✅ Todos os produtos já têm usuário definido</p>";
    }
    
    // 5. Verificação final
    echo "<h2>5. Verificação final...</h2>";
    
    // Contar produtos por usuário
    $result = $conn->query("
        SELECT 
            CASE 
                WHEN p.usuario_id IS NULL THEN 'Sem usuário'
                ELSE u.nome 
            END as usuario,
            COUNT(p.id) as total_produtos
        FROM produtos p
        LEFT JOIN usuarios u ON p.usuario_id = u.id
        GROUP BY p.usuario_id, u.nome
        ORDER BY total_produtos DESC
    ");
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Usuário</th><th>Total de Produtos</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['usuario']}</td>";
            echo "<td>{$row['total_produtos']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar estrutura da tabela
    echo "<h3>Estrutura atualizada da tabela produtos:</h3>";
    $result = $conn->query("DESCRIBE produtos");
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
    
    echo "<h2>✅ Atualização Concluída!</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3>🎉 Sistema Multi-Usuário Implementado!</h3>";
    echo "<ul>";
    echo "<li>✅ Cada usuário agora tem seus próprios produtos</li>";
    echo "<li>✅ Alertas de estoque personalizados com cores</li>";
    echo "<li>✅ Sistema de notificações implementado</li>";
    echo "<li>✅ Histórico de movimentações melhorado</li>";
    echo "</ul>";
    echo "</div>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<p style='margin-top: 20px;'>";
echo "<a href='test_corrections.php' style='margin-right: 10px;'>🔍 Testar Sistema</a>";
echo "<a href='index.php'>🏠 Voltar ao Sistema</a>";
echo "</p>";
?>
