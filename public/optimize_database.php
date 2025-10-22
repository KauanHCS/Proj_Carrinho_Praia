<?php
/**
 * Script de Otimização do Banco de Dados
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
            // Criar o índice
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

// Função para executar SQL com feedback
function executarSQL($conn, $sql, $descricao) {
    echo "<div style='margin: 10px 0; padding: 15px; border-left: 4px solid #0066cc; background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 100%); border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>";
    echo "<strong style='color: #0066cc; font-size: 1.1em;'>$descricao</strong><br>";
    
    try {
        $result = $conn->query($sql);
        if ($result !== false) {
            echo "<span style='color: #28a745; font-weight: 600; display: flex; align-items: center; margin-top: 8px;'>";
            echo "<i style='background: #28a745; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; margin-right: 8px; font-size: 12px;'>✓</i>";
            echo "Executado com sucesso</span>";
        } else {
            echo "<span style='color: #ffc107; font-weight: 600; display: flex; align-items: center; margin-top: 8px;'>";
            echo "<i style='background: #ffc107; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; margin-right: 8px; font-size: 12px;'>!</i>";
            echo "Já existe ou não aplicável</span>";
            if ($conn->error) {
                echo "<small style='display: block; margin-top: 4px; color: #6c757d; font-style: italic;'>" . htmlspecialchars($conn->error) . "</small>";
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
    <title>Otimizações do Sistema - Carrinho de Praia</title>
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
        .warning-card {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 2px solid #ffeb3b;
            color: #856404;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .status-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #0066cc;
        }
        .progress-custom {
            height: 6px;
            border-radius: 10px;
            overflow: hidden;
            background: #e9ecef;
        }
        .progress-bar-custom {
            background: linear-gradient(90deg, #0066cc, #0099ff);
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-card">
            <i class="bi bi-gear-fill mb-3" style="font-size: 3rem;"></i>
            <h1>Sistema de Otimização</h1>
            <p class="lead mb-0">Melhore a performance do seu banco de dados com índices otimizados</p>
        </div>

        <?php if (isset($_GET['apply'])): ?>
            <div class="section-card">
                <h2 class="text-center mb-4">
                    <i class="bi bi-lightning-charge text-warning"></i> 
                    Aplicando Otimizações
                </h2>
                
                <div class="progress-custom mb-4">
                    <div class="progress-bar-custom" style="width: 0%" id="progressBar"></div>
                </div>
                
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
                    <a href="optimize_database.php" class="btn-custom">
                        <i class="bi bi-arrow-left"></i>
                        Voltar ao Menu
                    </a>
                    <a href="index.php" class="btn-custom btn-secondary-custom">
                        <i class="bi bi-house"></i>
                        Ir para o Sistema
                    </a>
                </div>
            </div>
            
            <script>
                // Animação da barra de progresso
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 100) progress = 100;
                    document.getElementById('progressBar').style.width = progress + '%';
                    if (progress >= 100) clearInterval(interval);
                }, 200);
            </script>
            
        <?php else: ?>
            <div class="section-card">
                <h2 class="mb-4">
                    <i class="bi bi-info-circle text-primary"></i> 
                    Sobre as Otimizações
                </h2>
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="bi bi-lightning text-warning"></i> Melhorias de Performance</h5>
                        <ul>
                            <li>Índices para acelerar consultas de produtos</li>
                            <li>Otimização de busca e filtros</li>
                            <li>Índices para relatórios de vendas</li>
                            <li>Verificação rápida de estoque baixo</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="bi bi-shield-check text-success"></i> Segurança e Organização</h5>
                        <ul>
                            <li>Índices por usuário para multi-tenancy</li>
                            <li>Organização otimizada dos dados</li>
                            <li>Consultas mais eficientes</li>
                            <li>Melhor performance geral</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="warning-card">
                <h5><i class="bi bi-exclamation-triangle"></i> Importante</h5>
                <ul class="mb-0">
                    <li>✅ <strong>Seguro:</strong> Apenas adiciona índices, não modifica dados</li>
                    <li>✅ <strong>Reversível:</strong> Pode ser executado múltiplas vezes</li>
                    <li>✅ <strong>Inteligente:</strong> Verifica automaticamente quais tabelas existem</li>
                    <li>⚠️ <strong>Backup:</strong> Recomendamos fazer backup antes de aplicar</li>
                </ul>
            </div>
            
            <div class="section-card text-center">
                <h2 class="mb-4">🚀 Aplicar Otimizações</h2>
                <p class="lead text-muted mb-4">Pronto para acelerar seu sistema?</p>
                <a href="optimize_database.php?apply=1" class="btn-custom me-3">
                    <i class="bi bi-rocket-takeoff"></i>
                    Aplicar Otimizações
                </a>
                <a href="index.php" class="btn-custom btn-secondary-custom">
                    <i class="bi bi-arrow-left"></i>
                    Voltar ao Sistema
                </a>
            </div>
        <?php endif; ?>
        
        <div class="section-card">
            <h3><i class="bi bi-clipboard-data text-info"></i> Status Atual do Sistema</h3>
            <div class="status-grid">
                <?php
                try {
                    $conn = getConnection();
                    
                    // Verificar tabelas existentes
                    $result = $conn->query("SHOW TABLES");
                    $tabelas = [];
                    while ($row = $result->fetch_array()) {
                        $tabelas[] = $row[0];
                    }
                    
                    echo "<div class='status-item'>";
                    echo "<h6><i class='bi bi-table'></i> Tabelas do Sistema</h6>";
                    echo "<div class='mb-2'><strong>" . count($tabelas) . "</strong> tabelas encontradas</div>";
                    echo "<small>" . implode(', ', $tabelas) . "</small>";
                    echo "</div>";
                    
                    // Verificar produtos
                    if (in_array('produtos', $tabelas)) {
                        $result = $conn->query("SELECT COUNT(*) as total FROM produtos");
                        $produtos = $result->fetch_assoc();
                        
                        $result = $conn->query("SELECT COUNT(*) as baixo FROM produtos WHERE quantidade <= limite_minimo");
                        $produtosBaixo = $result->fetch_assoc();
                        
                        echo "<div class='status-item'>";
                        echo "<h6><i class='bi bi-box'></i> Produtos</h6>";
                        echo "<div><strong>" . $produtos['total'] . "</strong> produtos cadastrados</div>";
                        if ($produtosBaixo['baixo'] > 0) {
                            echo "<small class='text-warning'>⚠️ " . $produtosBaixo['baixo'] . " com estoque baixo</small>";
                        } else {
                            echo "<small class='text-success'>✅ Estoques OK</small>";
                        }
                        echo "</div>";
                    }
                    
                    // Verificar usuários
                    if (in_array('usuarios', $tabelas)) {
                        $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
                        $usuarios = $result->fetch_assoc();
                        
                        echo "<div class='status-item'>";
                        echo "<h6><i class='bi bi-people'></i> Usuários</h6>";
                        echo "<div><strong>" . $usuarios['total'] . "</strong> usuários registrados</div>";
                        echo "<small class='text-muted'>Sistema multi-usuário ativo</small>";
                        echo "</div>";
                    }
                    
                    // Verificar vendas (se existir)
                    if (in_array('vendas', $tabelas)) {
                        $result = $conn->query("SELECT COUNT(*) as total FROM vendas WHERE DATE(data) = CURDATE()");
                        $vendasHoje = $result->fetch_assoc();
                        
                        echo "<div class='status-item'>";
                        echo "<h6><i class='bi bi-graph-up'></i> Vendas Hoje</h6>";
                        echo "<div><strong>" . $vendasHoje['total'] . "</strong> vendas realizadas</div>";
                        echo "<small class='text-info'>📊 Sistema de vendas ativo</small>";
                        echo "</div>";
                    }
                    
                    closeConnection($conn);
                    
                } catch (Exception $e) {
                    echo "<div class='status-item'>";
                    echo "<h6 class='text-danger'>Erro ao verificar status</h6>";
                    echo "<small>" . htmlspecialchars($e->getMessage()) . "</small>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>