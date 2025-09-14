<?php
// Script para executar a correção dos triggers diretamente
require_once 'config/database.php';

echo "<h1>Executando Correção dos Triggers</h1>";

try {
    $conn = getConnection();
    echo "<p>✅ Conectado ao banco de dados</p>";
    
    // Lista de triggers para remover
    $triggers = [
        'tr_produto_inserted',
        'tr_produto_estoque_updated', 
        'tr_item_venda_inserted'
    ];
    
    echo "<h2>Removendo triggers que causam duplicação...</h2>";
    
    foreach ($triggers as $trigger) {
        try {
            $sql = "DROP TRIGGER IF EXISTS $trigger";
            if ($conn->query($sql)) {
                echo "<p>✅ Trigger '$trigger' removido com sucesso</p>";
            } else {
                echo "<p>⚠️ Trigger '$trigger' não encontrado (ok)</p>";
            }
        } catch (Exception $e) {
            echo "<p>⚠️ Erro ao remover trigger '$trigger': " . $e->getMessage() . "</p>";
        }
    }
    
    // Manter apenas o trigger de login
    echo "<h2>Recriando trigger de login do usuário...</h2>";
    
    $loginTriggerSQL = "
    DROP TRIGGER IF EXISTS tr_usuario_login;
    
    DELIMITER ;;
    CREATE TRIGGER tr_usuario_login
    BEFORE UPDATE ON usuarios
    FOR EACH ROW
    BEGIN
        IF NEW.ultimo_login != OLD.ultimo_login OR OLD.ultimo_login IS NULL THEN
            SET NEW.ultimo_login = CURRENT_TIMESTAMP;
        END IF;
    END;;
    DELIMITER ;
    ";
    
    // Dividir e executar comandos
    $commands = [
        "DROP TRIGGER IF EXISTS tr_usuario_login",
        "CREATE TRIGGER tr_usuario_login
        BEFORE UPDATE ON usuarios
        FOR EACH ROW
        BEGIN
            IF NEW.ultimo_login != OLD.ultimo_login OR OLD.ultimo_login IS NULL THEN
                SET NEW.ultimo_login = CURRENT_TIMESTAMP;
            END IF;
        END"
    ];
    
    foreach ($commands as $cmd) {
        if (trim($cmd)) {
            try {
                if ($conn->query($cmd)) {
                    echo "<p>✅ Comando executado: " . substr($cmd, 0, 50) . "...</p>";
                } else {
                    echo "<p>❌ Erro no comando: " . substr($cmd, 0, 50) . "... - " . $conn->error . "</p>";
                }
            } catch (Exception $e) {
                echo "<p>⚠️ Comando ignorado: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Verificar triggers restantes
    echo "<h2>Verificando triggers restantes...</h2>";
    $result = $conn->query("SHOW TRIGGERS FROM sistema_carrinho");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Nome</th><th>Evento</th><th>Tabela</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['Trigger']}</td>";
            echo "<td>{$row['Event']}</td>";
            echo "<td>{$row['Table']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>✅ Nenhum trigger encontrado (perfeito para evitar duplicação no histórico)</p>";
    }
    
    echo "<h2>✅ Correção Concluída!</h2>";
    echo "<p><strong>Agora você pode:</strong></p>";
    echo "<ul>";
    echo "<li>Fazer vendas sem duplicação no histórico</li>";
    echo "<li>Reabastecer estoque sem duplicação</li>";
    echo "<li>Ver alertas de estoque funcionando</li>";
    echo "</ul>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<p><a href='test_corrections.php'>🔍 Testar Correções</a> | <a href='index.php'>🏠 Voltar ao Sistema</a></p>";
?>
