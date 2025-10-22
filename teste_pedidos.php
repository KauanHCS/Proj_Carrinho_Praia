<?php
header('Content-Type: text/html; charset=utf-8');

try {
    echo "<h2>Teste de Listagem de Pedidos</h2>";
    
    // Conexão PDO
    $host = 'localhost';
    $dbname = 'sistema_carrinho';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✓ Conexão PDO estabelecida</p>";
    
    // Verificar se a tabela pedidos existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'pedidos'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Tabela 'pedidos' não existe!</p>";
        echo "<p><a href='criar_tabela_pedidos.php'>Criar tabela agora</a></p>";
        exit;
    }
    
    echo "<p>✓ Tabela 'pedidos' existe</p>";
    
    // Testar query
    echo "<h3>Testando query de listagem:</h3>";
    
    $stmt = $pdo->prepare("
        SELECT p.*, u.nome as vendedor_nome 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_vendedor_id = u.id 
        ORDER BY p.data_pedido DESC
        LIMIT 10
    ");
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>✓ Query executada com sucesso</p>";
    echo "<p>Pedidos encontrados: " . count($pedidos) . "</p>";
    
    if (count($pedidos) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Número</th><th>Cliente</th><th>Total</th><th>Status</th><th>Data</th><th>Vendedor</th></tr>";
        foreach ($pedidos as $pedido) {
            echo "<tr>";
            echo "<td>{$pedido['id']}</td>";
            echo "<td>{$pedido['numero_pedido']}</td>";
            echo "<td>" . ($pedido['cliente_nome'] ?? 'N/A') . "</td>";
            echo "<td>R$ " . number_format($pedido['total'], 2, ',', '.') . "</td>";
            echo "<td>{$pedido['status']}</td>";
            echo "<td>{$pedido['data_pedido']}</td>";
            echo "<td>" . ($pedido['vendedor_nome'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum pedido cadastrado ainda.</p>";
    }
    
    // Testar a URL da API
    echo "<hr><h3>Testando URL da API:</h3>";
    echo "<p>Tentando acessar: ../src/Controllers/actions.php?action=listarPedidos</p>";
    
    $url = "http://localhost/Proj_Carrinho_Praia/src/Controllers/actions.php?action=listarPedidos";
    echo "<p><a href='$url' target='_blank'>Testar no navegador</a></p>";
    
    // Testar com cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p>HTTP Code: $httpCode</p>";
    echo "<pre>Resposta da API:\n" . htmlspecialchars($response) . "</pre>";
    
    // Tentar decodificar JSON
    $data = json_decode($response, true);
    if ($data === null) {
        echo "<p style='color: red;'>❌ Erro ao decodificar JSON: " . json_last_error_msg() . "</p>";
    } else {
        echo "<p>✓ JSON válido</p>";
        echo "<pre>" . print_r($data, true) . "</pre>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erro PDO: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}

echo "<hr><p><a href='public/index.php'>Voltar ao sistema</a></p>";
?>
