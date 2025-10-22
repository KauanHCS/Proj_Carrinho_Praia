<?php
require_once 'config/database.php';

try {
    echo "<h2>Debug - Funcionários e Códigos</h2>";
    
    $conn = getConnection();
    
    // Verificar tabela codigos_funcionarios
    echo "<h3>1. Tabela codigos_funcionarios:</h3>";
    $result = $conn->query("SELECT * FROM codigos_funcionarios ORDER BY data_criacao DESC LIMIT 10");
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Código</th><th>Admin ID</th><th>Ativo</th><th>Data Criação</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td><strong>{$row['codigo']}</strong></td>";
            echo "<td>{$row['admin_id']}</td>";
            echo "<td>{$row['ativo']}</td>";
            echo "<td>{$row['data_criacao']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum código encontrado</p>";
    }
    
    // Verificar estrutura da tabela usuarios
    echo "<hr><h3>2. Estrutura da tabela usuarios (colunas relacionadas):</h3>";
    $result = $conn->query("DESCRIBE usuarios");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    while ($row = $result->fetch_assoc()) {
        if (stripos($row['Field'], 'codigo') !== false || 
            stripos($row['Field'], 'tipo') !== false || 
            stripos($row['Field'], 'funcao') !== false) {
            echo "<tr>";
            echo "<td><strong>{$row['Field']}</strong></td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    // Verificar todos os usuários
    echo "<hr><h3>3. Todos os usuários cadastrados:</h3>";
    $result = $conn->query("SELECT id, nome, email, tipo, tipo_usuario, codigo_unico, funcao_funcionario FROM usuarios ORDER BY data_cadastro DESC LIMIT 10");
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th><th>Tipo Usuario</th><th>Código Único</th><th>Função</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nome']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>" . ($row['tipo'] ?? 'null') . "</td>";
            echo "<td>" . ($row['tipo_usuario'] ?? 'null') . "</td>";
            echo "<td><strong>" . ($row['codigo_unico'] ?? 'null') . "</strong></td>";
            echo "<td>" . ($row['funcao_funcionario'] ?? 'null') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum usuário encontrado</p>";
    }
    
    // Verificar funcionários especificamente
    echo "<hr><h3>4. Funcionários (tipo_usuario = 'funcionario'):</h3>";
    $result = $conn->query("SELECT id, nome, email, codigo_unico, funcao_funcionario FROM usuarios WHERE tipo_usuario = 'funcionario' ORDER BY data_cadastro DESC");
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Código Único</th><th>Função</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nome']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td><strong>" . ($row['codigo_unico'] ?? 'null') . "</strong></td>";
            echo "<td>" . ($row['funcao_funcionario'] ?? 'null') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Nenhum funcionário encontrado!</p>";
    }
    
    // Testar a query que está sendo usada
    echo "<hr><h3>5. Teste da query atual (listarCodigosFuncionarios):</h3>";
    
    // Buscar códigos
    $result = $conn->query("SELECT codigo, data_criacao FROM codigos_funcionarios WHERE ativo = 1 GROUP BY codigo ORDER BY data_criacao DESC");
    echo "<p>Códigos encontrados: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        while ($codigo = $result->fetch_assoc()) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<strong>Código: {$codigo['codigo']}</strong><br>";
            echo "Data: {$codigo['data_criacao']}<br>";
            
            // Buscar funcionários com este código
            $stmt = $conn->prepare("SELECT id, nome, email, funcao_funcionario FROM usuarios WHERE codigo_unico = ? AND tipo_usuario = 'funcionario' ORDER BY nome");
            $stmt->bind_param("s", $codigo['codigo']);
            $stmt->execute();
            $resultFunc = $stmt->get_result();
            
            echo "Funcionários com este código: " . $resultFunc->num_rows . "<br>";
            
            if ($resultFunc->num_rows > 0) {
                echo "<ul>";
                while ($func = $resultFunc->fetch_assoc()) {
                    echo "<li>{$func['nome']} - {$func['email']} - Função: " . ($func['funcao_funcionario'] ?? 'não definida') . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color: orange;'>⚠️ Nenhum funcionário cadastrado com este código ainda</p>";
            }
            
            echo "</div>";
        }
    }
    
    closeConnection($conn);
    
    echo "<hr><p><a href='public/index.php'>Voltar ao sistema</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>
