<?php
/**
 * Script de Otimização do Banco de Dados - CORRIGIDO
 * Versão adaptada para a nova estrutura do projeto
 */

// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

require_once '../config/database.php';

// Função para verificar se índice existe
function indiceExiste($conn, $tabela, $indice) {
    $result = $conn->query("SHOW INDEX FROM `$tabela` WHERE Key_name = '$indice'");
    return $result && $result->num_rows > 0;
}

// Função para executar criação de índice com verificação
function criarIndice($conn, $tabela, $nome_indice, $colunas, $descricao) {
    echo "<div style='margin: 10px 0; padding: 15px; border-left: 4px solid #0066cc; background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 100%); border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>";
    echo "<strong style='color: #0066cc; font-size: 1.1em;'>$descricao</strong><br>";
    
    try {
        // Verificar se a tabela existe
        $table_check = $conn->query("SHOW TABLES LIKE '$tabela'");
        if (!$table_check || $table_check->num_rows == 0) {
            echo "<span style='color: #ffc107; font-weight: 600; display: flex; align-items: center; margin-top: 8px;'>";
            echo "<i style='background: #ffc107; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; margin-right: 8px; font-size: 12px;'>!</i>";
            echo "Tabela '$tabela' não existe</span>";
            echo "</div>";
            return;
        }
        
        // Verificar se o índice já existe
        if (indiceExiste($conn, $tabela, $nome_indice)) {
            echo "<span style='color: #6c757d; font-weight: 600; display: flex; align-items: center; margin-top: 8px;'>";
            echo "<i style='background: #6c757d; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; margin-right: 8px; font-size: 12px;'>∃</i>";
            echo "Índice já existe</span>";
        } else {
            // Criar o índice SEM IF NOT EXISTS
            $sql = "CREATE INDEX `$nome_indice` ON `$tabela` ($colunas)";
            $result = $conn->query($sql);
            
            if ($result !== false) {
                echo "<span style='color: #28a745; font-weight: 600; display: flex; align-items: center; margin-top: 8px;'>";
                echo "<i style='background: #28a745; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; margin-right: 8px; font-size: 12px;'>✓</i>";
                echo "Índice criado com sucesso</span>";
            } else {
                throw new Exception($conn->error);
            }
        }
    } catch (Exception $e) {
        echo "<span style='color: #dc3545; font-weight: 600; display: flex; align-items: center; margin-top: 8px;'>";
        echo "<i style='background: #dc3545; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; margin-right: 8px; font-size: 12px;'>✕</i>";
        echo "Erro: " . htmlspecialchars($e->getMessage()) . "</span>";
    }
    
    echo "</div>";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otimizações do Sistema - Carrinho de Praia (CORRIGIDO)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .header-card {
            background: linear-gradient(135deg, #0066cc, #0099ff);
            color: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        .header-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }
        .header-card h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .section-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .btn-custom {
            background: linear-gradient(135deg, #0066cc, #0099ff);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 102, 204, 0.4);
            color: white;
        }
        .btn-secondary-custom {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        .btn-secondary-custom:hover {
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-card">
            <i class="bi bi-gear-fill mb-3" style="font-size: 3rem;"></i>
            <h1>Sistema de Otimização (CORRIGIDO)</h1>
            <p class="lead mb-0">Melhore a performance do seu banco de dados com índices otimizados</p>
        </div>

        <?php if (isset($_GET['apply'])): ?>
            <div class="section-card">
                <h2 class="text-center mb-4">
                    <i class="bi bi-lightning-charge text-warning"></i> 
                    Aplicando Otimizações
                </h2>
                
                <?php
                try {
                    $conn = getConnection();
                    
                    echo "<h3 style='color: #0066cc; border-bottom: 2px solid #0066cc; padding-bottom: 10px; margin: 30px 0 20px 0;'><i class='bi bi-database'></i> Criando Índices de Performance</h3>";
                    
                    // Índices para produtos
                    criarIndice($conn, 'produtos', 'idx_produtos_usuario_id', 'usuario_id', 'Índice para usuário em produtos');
                    criarIndice($conn, 'produtos', 'idx_produtos_quantidade', 'quantidade', 'Índice para quantidade de produtos');
                    criarIndice($conn, 'produtos', 'idx_produtos_categoria', 'categoria', 'Índice para categoria de produtos');
                    criarIndice($conn, 'produtos', 'idx_produtos_nome', 'nome', 'Índice para nome de produtos');
                    criarIndice($conn, 'produtos', 'idx_produtos_estoque_baixo', 'quantidade, limite_minimo', 'Índice composto para verificação de estoque baixo');
                    criarIndice($conn, 'produtos', 'idx_produtos_ativo', 'ativo', 'Índice para status ativo dos produtos');
                    
                    echo "<h3 style='color: #28a745; border-bottom: 2px solid #28a745; padding-bottom: 10px; margin: 30px 0 20px 0;'><i class='bi bi-receipt'></i> Índices para Sistema de Vendas</h3>";
                    
                    // Índices para vendas
                    criarIndice($conn, 'vendas', 'idx_vendas_usuario_id', 'usuario_id', 'Índice para usuário em vendas');
                    criarIndice($conn, 'vendas', 'idx_vendas_data', 'data', 'Índice para data de vendas');
                    criarIndice($conn, 'vendas', 'idx_vendas_forma_pagamento', 'forma_pagamento', 'Índice para forma de pagamento');
                    criarIndice($conn, 'vendas', 'idx_vendas_total', 'total', 'Índice para valor total das vendas');
                    
                    // Índices para itens de venda
                    criarIndice($conn, 'itens_venda', 'idx_itens_venda_produto', 'produto_id', 'Índice para produto em itens de venda');
                    criarIndice($conn, 'itens_venda', 'idx_itens_venda_venda', 'venda_id', 'Índice para venda em itens de venda');
                    criarIndice($conn, 'itens_venda', 'idx_itens_venda_quantidade', 'quantidade', 'Índice para quantidade vendida');
                    
                    echo "<h3 style='color: #17a2b8; border-bottom: 2px solid #17a2b8; padding-bottom: 10px; margin: 30px 0 20px 0;'><i class='bi bi-arrow-repeat'></i> Índices para Movimentações de Estoque</h3>";
                    
                    // Índices para movimentações
                    criarIndice($conn, 'movimentacoes', 'idx_movimentacoes_produto', 'produto_id', 'Índice para produto em movimentações');
                    criarIndice($conn, 'movimentacoes', 'idx_movimentacoes_usuario', 'usuario_id', 'Índice para usuário em movimentações');
                    criarIndice($conn, 'movimentacoes', 'idx_movimentacoes_data', 'data', 'Índice para data de movimentações');
                    criarIndice($conn, 'movimentacoes', 'idx_movimentacoes_tipo', 'tipo', 'Índice para tipo de movimentação');
                    
                    echo "<h3 style='color: #6f42c1; border-bottom: 2px solid #6f42c1; padding-bottom: 10px; margin: 30px 0 20px 0;'><i class='bi bi-people'></i> Índices para Usuários</h3>";
                    
                    // Índices para usuários
                    criarIndice($conn, 'usuarios', 'idx_usuarios_email', 'email', 'Índice para email de usuários');
                    criarIndice($conn, 'usuarios', 'idx_usuarios_google_id', 'google_id', 'Índice para Google ID');
                    criarIndice($conn, 'usuarios', 'idx_usuarios_ativo', 'ativo', 'Índice para status ativo dos usuários');
                    criarIndice($conn, 'usuarios', 'idx_usuarios_data_cadastro', 'data_cadastro', 'Índice para data de cadastro');
                    
                    echo "<div style='background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 30px; border-radius: 15px; text-align: center; margin: 40px 0;'>";
                    echo "<i class='bi bi-check-circle-fill' style='font-size: 3rem; margin-bottom: 15px;'></i>";
                    echo "<h3>✅ Otimizações Aplicadas com Sucesso!</h3>";
                    echo "<p class='mb-0'>Seu banco de dados foi otimizado. As consultas agora serão significativamente mais rápidas!</p>";
                    echo "</div>";
                    
                    closeConnection($conn);
                    
                } catch (Exception $e) {
                    echo "<div style='color: white; padding: 25px; background: linear-gradient(135deg, #dc3545, #c92066); border-radius: 15px; text-align: center;'>";
                    echo "<i class='bi bi-exclamation-triangle-fill' style='font-size: 2.5rem; margin-bottom: 15px;'></i>";
                    echo "<h4>Erro ao aplicar otimizações</h4>";
                    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "</div>";
                }
                ?>
                
                <div class="text-center mt-4">
                    <a href="optimize_database_fixed.php" class="btn-custom">
                        <i class="bi bi-arrow-left"></i>
                        Voltar ao Menu
                    </a>
                    <a href="index.php" class="btn-custom btn-secondary-custom">
                        <i class="bi bi-house"></i>
                        Ir para o Sistema
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <div class="section-card text-center">
                <h2 class="mb-4">🚀 Aplicar Otimizações (VERSÃO CORRIGIDA)</h2>
                <p class="lead text-muted mb-4">Pronto para acelerar seu sistema?</p>
                <a href="optimize_database_fixed.php?apply=1" class="btn-custom me-3">
                    <i class="bi bi-rocket-takeoff"></i>
                    Aplicar Otimizações
                </a>
                <a href="index.php" class="btn-custom btn-secondary-custom">
                    <i class="bi bi-arrow-left"></i>
                    Voltar ao Sistema
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>