<?php
// Script temporário para adicionar produtos via navegador
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Adicionar Produtos de Exemplo</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .product { margin: 5px 0; padding: 5px; background: #f8f9fa; border-left: 3px solid #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🏖️ Adicionar Produtos de Exemplo</h2>
        <p>Este script irá adicionar produtos contextualizados para carrinho de praia nos usuários ID 1 e 2.</p>
        
        <?php if (!isset($_GET['run'])): ?>
            <a href="?run=1" class="btn">▶️ Executar Script</a>
        <?php else: 
            try {
                $conn = getConnection();
                
                // Lista de produtos
                $produtos = [
                    // Usuário 1 - Produtos básicos
                    ['nome' => 'Água Mineral 500ml', 'categoria' => 'bebida', 'preco' => 2.50, 'quantidade' => 100, 'limite_minimo' => 20, 'validade' => null, 'observacoes' => 'Água gelada sempre disponível', 'usuario_id' => 1],
                    ['nome' => 'Refrigerante Lata 350ml', 'categoria' => 'bebida', 'preco' => 4.00, 'quantidade' => 80, 'limite_minimo' => 15, 'validade' => '2025-12-31', 'observacoes' => 'Coca-Cola, Pepsi, Guaraná - Bem gelados', 'usuario_id' => 1],
                    ['nome' => 'Cerveja Lata 350ml', 'categoria' => 'bebida', 'preco' => 6.50, 'quantidade' => 60, 'limite_minimo' => 10, 'validade' => '2025-10-15', 'observacoes' => 'Somente para maiores de 18 anos', 'usuario_id' => 1],
                    ['nome' => 'Suco Natural 300ml', 'categoria' => 'bebida', 'preco' => 5.00, 'quantidade' => 40, 'limite_minimo' => 8, 'validade' => '2025-09-20', 'observacoes' => 'Laranja, Acerola, Maracujá', 'usuario_id' => 1],
                    ['nome' => 'Água de Coco 500ml', 'categoria' => 'bebida', 'preco' => 4.50, 'quantidade' => 50, 'limite_minimo' => 10, 'validade' => '2025-09-25', 'observacoes' => 'Natural gelada', 'usuario_id' => 1],
                    
                    // Comidas básicas
                    ['nome' => 'Sanduíche Natural', 'categoria' => 'comida', 'preco' => 8.00, 'quantidade' => 30, 'limite_minimo' => 5, 'validade' => '2025-09-16', 'observacoes' => 'Presunto, queijo, peru, frango', 'usuario_id' => 1],
                    ['nome' => 'Pipoca Doce 100g', 'categoria' => 'comida', 'preco' => 3.50, 'quantidade' => 45, 'limite_minimo' => 10, 'validade' => null, 'observacoes' => 'Feita na hora', 'usuario_id' => 1],
                    ['nome' => 'Picolé Fruta', 'categoria' => 'comida', 'preco' => 4.50, 'quantidade' => 70, 'limite_minimo' => 15, 'validade' => '2025-12-31', 'observacoes' => 'Morango, limão, uva, coco', 'usuario_id' => 1],
                    ['nome' => 'Biscoito de Polvilho', 'categoria' => 'comida', 'preco' => 5.00, 'quantidade' => 25, 'limite_minimo' => 5, 'validade' => '2025-11-30', 'observacoes' => 'Crocante tradicional', 'usuario_id' => 1],
                    
                    // Acessórios
                    ['nome' => 'Protetor Solar FPS 30', 'categoria' => 'acessorio', 'preco' => 18.00, 'quantidade' => 15, 'limite_minimo' => 3, 'validade' => '2026-08-15', 'observacoes' => 'À prova d\'água, 120ml', 'usuario_id' => 1],
                    ['nome' => 'Chapéu de Praia', 'categoria' => 'acessorio', 'preco' => 12.00, 'quantidade' => 20, 'limite_minimo' => 5, 'validade' => null, 'observacoes' => 'Várias cores disponíveis', 'usuario_id' => 1],
                    ['nome' => 'Óculos de Sol', 'categoria' => 'acessorio', 'preco' => 25.00, 'quantidade' => 12, 'limite_minimo' => 2, 'validade' => null, 'observacoes' => 'Proteção UV400', 'usuario_id' => 1],
                    ['nome' => 'Boia Infantil', 'categoria' => 'acessorio', 'preco' => 15.00, 'quantidade' => 8, 'limite_minimo' => 2, 'validade' => null, 'observacoes' => 'Para crianças de 3 a 8 anos', 'usuario_id' => 1],
                    
                    // Usuário 2 - Produtos premium
                    ['nome' => 'Caipirinha 400ml', 'categoria' => 'bebida', 'preco' => 8.00, 'quantidade' => 30, 'limite_minimo' => 5, 'validade' => null, 'observacoes' => 'Tradicional de limão - Somente maiores', 'usuario_id' => 2],
                    ['nome' => 'Batida de Coco 400ml', 'categoria' => 'bebida', 'preco' => 9.00, 'quantidade' => 25, 'limite_minimo' => 5, 'validade' => null, 'observacoes' => 'Cremosa - Somente maiores', 'usuario_id' => 2],
                    ['nome' => 'Espetinho de Camarão', 'categoria' => 'comida', 'preco' => 12.00, 'quantidade' => 20, 'limite_minimo' => 3, 'validade' => '2025-09-16', 'observacoes' => 'Grelhado na hora', 'usuario_id' => 2],
                    ['nome' => 'Porção de Camarão', 'categoria' => 'comida', 'preco' => 25.00, 'quantidade' => 10, 'limite_minimo' => 2, 'validade' => '2025-09-16', 'observacoes' => 'Empanado com molho especial', 'usuario_id' => 2],
                    ['nome' => 'Guarda-Sol', 'categoria' => 'acessorio', 'preco' => 10.00, 'quantidade' => 5, 'limite_minimo' => 1, 'validade' => null, 'observacoes' => 'Aluguel por dia', 'usuario_id' => 2],
                    ['nome' => 'Cadeira de Praia', 'categoria' => 'acessorio', 'preco' => 15.00, 'quantidade' => 6, 'limite_minimo' => 1, 'validade' => null, 'observacoes' => 'Dobrável - Aluguel', 'usuario_id' => 2]
                ];
                
                $stmt = $conn->prepare("INSERT INTO produtos (nome, categoria, preco, quantidade, limite_minimo, validade, observacoes, usuario_id, data_cadastro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $produtosAdicionados = 0;
                
                echo "<h3>🔄 Executando...</h3>";
                
                foreach ($produtos as $produto) {
                    // Verificar se produto já existe
                    $checkStmt = $conn->prepare("SELECT id FROM produtos WHERE nome = ? AND usuario_id = ?");
                    $checkStmt->bind_param("si", $produto['nome'], $produto['usuario_id']);
                    $checkStmt->execute();
                    $result = $checkStmt->get_result();
                    
                    if ($result->num_rows == 0) {
                        $stmt->bind_param("ssdiiisi", 
                            $produto['nome'], $produto['categoria'], $produto['preco'],
                            $produto['quantidade'], $produto['limite_minimo'], $produto['validade'],
                            $produto['observacoes'], $produto['usuario_id']
                        );
                        
                        if ($stmt->execute()) {
                            echo "<div class='product'>✅ {$produto['nome']} (Usuário {$produto['usuario_id']})</div>";
                            $produtosAdicionados++;
                        } else {
                            echo "<div class='alert alert-danger'>❌ Erro ao adicionar {$produto['nome']}: " . $stmt->error . "</div>";
                        }
                    } else {
                        echo "<div class='product' style='border-left-color: #ffc107;'>ℹ️ {$produto['nome']} já existe (Usuário {$produto['usuario_id']})</div>";
                    }
                }
                
                echo "<div class='alert alert-success'>";
                echo "<h3>🎉 Concluído!</h3>";
                echo "📊 Produtos adicionados: <strong>$produtosAdicionados</strong><br>";
                echo "👥 Usuários contemplados: ID 1 e 2<br>";
                echo "🏖️ Todos os produtos são contextualizados para carrinho de praia!";
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>❌ Erro: " . $e->getMessage() . "</div>";
            }
        ?>
        
        <br>
        <a href="../" class="btn">🏠 Voltar ao Sistema</a>
        
        <?php endif; ?>
    </div>
</body>
</html>