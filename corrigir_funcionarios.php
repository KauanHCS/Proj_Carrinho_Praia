<?php
require_once 'config/database.php';

try {
    echo "<h2>Corrigindo funcionários sem código_unico...</h2>";
    
    $conn = getConnection();
    
    // Buscar funcionários sem codigo_unico
    $result = $conn->query("
        SELECT u.id, u.nome, u.email, u.codigo_admin, cf.codigo 
        FROM usuarios u
        INNER JOIN usos_codigo uc ON u.id = uc.usuario_id
        INNER JOIN codigos_funcionarios cf ON uc.codigo_id = cf.id
        WHERE u.tipo_usuario = 'funcionario' 
        AND (u.codigo_unico IS NULL OR u.codigo_unico = '')
    ");
    
    if ($result->num_rows > 0) {
        echo "<p>Encontrados {$result->num_rows} funcionário(s) para corrigir:</p>";
        
        while ($row = $result->fetch_assoc()) {
            $stmt = $conn->prepare("UPDATE usuarios SET codigo_unico = ? WHERE id = ?");
            $stmt->bind_param("si", $row['codigo'], $row['id']);
            
            if ($stmt->execute()) {
                echo "<p>✓ Funcionário <strong>{$row['nome']}</strong> ({$row['email']}) atualizado com código <code>{$row['codigo']}</code></p>";
            } else {
                echo "<p style='color: red;'>❌ Erro ao atualizar funcionário {$row['nome']}: " . $conn->error . "</p>";
            }
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhum funcionário encontrado na tabela usos_codigo</p>";
        
        // Tentar corrigir manualmente baseado no codigo_admin
        echo "<hr><h3>Tentando corrigir manualmente...</h3>";
        
        $result = $conn->query("
            SELECT u.id, u.nome, u.email, u.codigo_admin
            FROM usuarios u
            WHERE u.tipo_usuario = 'funcionario' 
            AND (u.codigo_unico IS NULL OR u.codigo_unico = '')
        ");
        
        if ($result->num_rows > 0) {
            echo "<p>Funcionários sem código_unico:</p><ul>";
            while ($row = $result->fetch_assoc()) {
                // Buscar códigos do admin
                $stmt = $conn->prepare("SELECT codigo FROM codigos_funcionarios WHERE admin_id = ? ORDER BY data_criacao DESC LIMIT 1");
                $stmt->bind_param("i", $row['codigo_admin']);
                $stmt->execute();
                $codigoResult = $stmt->get_result();
                
                if ($codigoRow = $codigoResult->fetch_assoc()) {
                    $stmt2 = $conn->prepare("UPDATE usuarios SET codigo_unico = ? WHERE id = ?");
                    $stmt2->bind_param("si", $codigoRow['codigo'], $row['id']);
                    
                    if ($stmt2->execute()) {
                        echo "<li>✓ <strong>{$row['nome']}</strong> ({$row['email']}) atualizado com código mais recente: <code>{$codigoRow['codigo']}</code></li>";
                    }
                } else {
                    echo "<li>❌ <strong>{$row['nome']}</strong> ({$row['email']}) - Admin sem códigos cadastrados</li>";
                }
            }
            echo "</ul>";
        }
    }
    
    closeConnection($conn);
    
    echo "<hr><h3>✅ Correção concluída!</h3>";
    echo "<p><a href='debug_funcionarios.php'>Verificar resultado</a> | <a href='public/index.php'>Voltar ao sistema</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>
