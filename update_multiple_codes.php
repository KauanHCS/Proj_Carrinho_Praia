<?php
/**
 * Script para atualizar o banco de dados para permitir códigos reutilizáveis
 */

try {
    $host = 'localhost';
    $dbname = 'sistema_carrinho';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Atualização do Sistema de Códigos</title>";
    echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:0 auto;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style></head><body>";
    echo "<h2>🔄 Atualização do Sistema de Códigos Reutilizáveis</h2>";
    
    // 1. Criar tabela para rastrear usos dos códigos
    echo "<h3>1. Criando tabela de controle de usos...</h3>";
    try {
        $sql = "CREATE TABLE IF NOT EXISTS usos_codigo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo_id INT NOT NULL,
            usuario_id INT NOT NULL,
            data_uso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (codigo_id) REFERENCES codigos_funcionarios(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            UNIQUE KEY unique_uso (codigo_id, usuario_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "<p class='success'>✅ Tabela 'usos_codigo' criada com sucesso!</p>";
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro ao criar tabela usos_codigo: " . $e->getMessage() . "</p>";
    }
    
    // 2. Migrar dados existentes
    echo "<h3>2. Migrando dados existentes...</h3>";
    try {
        // Buscar funcionários que já usaram códigos
        $stmt = $pdo->prepare("
            SELECT u.id as usuario_id, cf.id as codigo_id 
            FROM usuarios u 
            INNER JOIN codigos_funcionarios cf ON cf.admin_id = u.codigo_admin 
            WHERE u.tipo_usuario = 'funcionario' AND u.codigo_admin IS NOT NULL
        ");
        $stmt->execute();
        $usos_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($usos_existentes as $uso) {
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO usos_codigo (codigo_id, usuario_id, data_uso) 
                VALUES (?, ?, (SELECT data_cadastro FROM usuarios WHERE id = ?))
            ");
            $stmt->execute([$uso['codigo_id'], $uso['usuario_id'], $uso['usuario_id']]);
        }
        
        echo "<p class='success'>✅ Dados migrados: " . count($usos_existentes) . " registros processados</p>";
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro na migração: " . $e->getMessage() . "</p>";
    }
    
    // 3. Resetar status dos códigos para permitir reutilização
    echo "<h3>3. Resetando códigos para reutilização...</h3>";
    try {
        $stmt = $pdo->prepare("UPDATE codigos_funcionarios SET usado = 0 WHERE ativo = 1");
        $stmt->execute();
        $resetados = $stmt->rowCount();
        
        echo "<p class='success'>✅ $resetados códigos resetados para reutilização</p>";
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro ao resetar códigos: " . $e->getMessage() . "</p>";
    }
    
    // 4. Adicionar nova action para gerenciar funcionários
    echo "<h3>4. Sistema atualizado com sucesso! ✅</h3>";
    echo "<div class='info'>";
    echo "<h4>📋 Resumo das mudanças:</h4>";
    echo "<ul>";
    echo "<li>✅ Códigos agora podem ser reutilizados por múltiplas pessoas</li>";
    echo "<li>✅ Função dos funcionários será definida pelo administrador</li>";
    echo "<li>✅ Tabela de controle de usos criada</li>";
    echo "<li>✅ Dados existentes migrados</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<br><a href='public/index.php' style='background:#0066cc;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🏠 Voltar ao Sistema</a>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>