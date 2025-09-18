<?php
require_once '../config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>🔍 TESTE DO ENDPOINT</h2>";
    
    // Testar a consulta diretamente
    echo "<h3>📊 Consulta Direta:</h3>";
    
    $sql = "SELECT p.nome, p.categoria, SUM(iv.quantidade) as total_vendido, 
                   COUNT(DISTINCT iv.venda_id) as num_vendas
            FROM itens_venda iv 
            JOIN produtos p ON iv.produto_id = p.id 
            GROUP BY p.id, p.nome, p.categoria 
            ORDER BY total_vendido DESC 
            LIMIT 5";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<strong>✅ Dados encontrados na consulta SQL direta:</strong><br>";
        while($row = $result->fetch_assoc()) {
            echo "• " . $row['nome'] . " (" . $row['categoria'] . "): " . $row['total_vendido'] . " vendidos<br>";
        }
    } else {
        echo "❌ Nenhum resultado na consulta SQL direta<br>";
    }
    
    echo "<hr>";
    
    // Testar endpoint via cURL simulado
    echo "<h3>🌐 Teste do Endpoint (simulação):</h3>";
    
    // Simular a chamada do endpoint
    $_GET['action'] = 'get_produtos_mais_vendidos';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    // Capturar output
    ob_start();
    
    // Incluir lógica do endpoint
    try {
        require_once '../autoload.php';
        
        $db = \CarrinhoDePreia\Database::getInstance();
        $sql = "SELECT p.nome, p.categoria, SUM(iv.quantidade) as total_vendido, 
                       COUNT(DISTINCT iv.venda_id) as num_vendas
                FROM itens_venda iv 
                JOIN produtos p ON iv.produto_id = p.id 
                GROUP BY p.id, p.nome, p.categoria 
                ORDER BY total_vendido DESC 
                LIMIT 5";
        
        $produtos = $db->select($sql, "", []);
        
        if (!empty($produtos)) {
            // Formatar dados como esperado
            $produtosFormatados = [];
            foreach ($produtos as $produto) {
                $produtosFormatados[] = [
                    'nome' => $produto['nome'],
                    'categoria' => $produto['categoria'],
                    'total_vendido' => (int)$produto['total_vendido'],
                    'num_vendas' => (int)$produto['num_vendas']
                ];
            }
            
            $response = [
                'success' => true,
                'produtos' => $produtosFormatados,
                'message' => 'Produtos mais vendidos carregados'
            ];
        } else {
            $response = [
                'success' => false,
                'produtos' => [],
                'message' => 'Nenhum produto vendido encontrado'
            ];
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'produtos' => [],
            'message' => 'Erro: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    
    $endpointOutput = ob_get_clean();
    
    echo "<strong>Resposta do endpoint:</strong><br>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars($endpointOutput);
    echo "</pre>";
    
    // Decodificar JSON para análise
    $decoded = json_decode($endpointOutput, true);
    if ($decoded) {
        echo "<br><strong>JSON decodificado:</strong><br>";
        if ($decoded['success']) {
            echo "✅ Sucesso: " . $decoded['message'] . "<br>";
            echo "📦 Produtos encontrados: " . count($decoded['produtos']) . "<br>";
            foreach ($decoded['produtos'] as $produto) {
                echo "• " . $produto['nome'] . ": " . $produto['total_vendido'] . " vendidos<br>";
            }
        } else {
            echo "❌ Falha: " . $decoded['message'] . "<br>";
        }
    }
    
    $conn->close();
    
    echo "<br><br><a href='index.php'>← Voltar ao Sistema</a>";
    
} catch (Exception $e) {
    echo "❌ <strong>Erro:</strong> " . $e->getMessage();
}
?>