<!DOCTYPE html>
<html>
<head>
    <title>Verificação da Tabela Usuários</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Verificação da Tabela Usuários</h1>
    <?php
    require_once 'config/database.php';

    try {
        $conn = getConnection();

        // Verificar estrutura da tabela usuarios
        echo "<h2>Estrutura da tabela usuarios</h2>";
        echo "<pre>";
        $result = $conn->query('DESCRIBE usuarios');
        while($row = $result->fetch_assoc()) {
            echo "{$row['Field']} - {$row['Type']} - Null:{$row['Null']} - Key:{$row['Key']} - Default:{$row['Default']}\n";
        }
        echo "</pre>";

        // Verificar se há usuários cadastrados
        echo "<h2>Usuários cadastrados</h2>";
        echo "<pre>";
        $result = $conn->query('SELECT id, nome, email, google_id, imagem_url FROM usuarios LIMIT 10');
        $count = 0;
        while($row = $result->fetch_assoc()) {
            echo "ID: {$row['id']} | Nome: {$row['nome']} | Email: {$row['email']} | Google ID: {$row['google_id']} | Imagem: {$row['imagem_url']}\n";
            $count++;
        }
        echo "\nTotal de usuários encontrados: $count\n";
        echo "</pre>";

        $conn->close();
        
        echo "<p class='success'>✅ Verificação concluída com sucesso!</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <p><a href="login.php">← Voltar para o Login</a></p>
</body>
</html>
