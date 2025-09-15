<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎉 Atualizações do Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .feature { 
            background: #f8f9fa; 
            border-left: 4px solid #007bff; 
            padding: 15px; 
            margin: 10px 0;
            border-radius: 0 5px 5px 0;
        }
        .update-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .update-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .emoji { font-size: 2rem; }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="text-center mb-5">
            <h1 class="display-4">🎉 Sistema Atualizado!</h1>
            <p class="lead text-muted">Confira todas as melhorias implementadas</p>
        </div>

        <div class="row">
            <!-- Google Maps -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 update-card">
                    <div class="card-body text-center">
                        <div class="emoji">🗺️</div>
                        <h5 class="card-title">Google Maps</h5>
                        <p class="card-text">Substituído Leaflet por Google Maps para melhor desempenho e precisão.</p>
                        <ul class="list-unstyled text-start small">
                            <li>✅ Carregamento assíncrono otimizado</li>
                            <li>✅ Rotas reais com tempo e distância</li>
                            <li>✅ Marcadores interativos</li>
                            <li>✅ Sem erros no console</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Preços de Compra e Venda -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 update-card">
                    <div class="card-body text-center">
                        <div class="emoji">💰</div>
                        <h5 class="card-title">Controle de Lucro</h5>
                        <p class="card-text">Novos campos para preço de compra e venda com cálculo automático de margem.</p>
                        <ul class="list-unstyled text-start small">
                            <li>✅ Preço de compra e venda</li>
                            <li>✅ Margem calculada em tempo real</li>
                            <li>✅ Validação de lucro</li>
                            <li>✅ Relatórios de lucro real</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Relatórios Melhorados -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 update-card">
                    <div class="card-body text-center">
                        <div class="emoji">📊</div>
                        <h5 class="card-title">Relatórios</h5>
                        <p class="card-text">Relatórios simplificados com foco em exportação e análise de lucro.</p>
                        <ul class="list-unstyled text-start small">
                            <li>✅ Backup removido (simplificado)</li>
                            <li>✅ Exportação melhorada</li>
                            <li>✅ Lucro real calculado</li>
                            <li>✅ Interface mais limpa</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Outras Melhorias -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-check-circle"></i> Outras Melhorias</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="feature">
                                    <h6><i class="bi bi-trash"></i> Exclusão de Produtos</h6>
                                    <p>Agora é possível excluir produtos mesmo que tenham vendas, com botão "Voltar" na confirmação.</p>
                                </div>
                                <div class="feature">
                                    <h6><i class="bi bi-calendar3"></i> Footer Atualizado</h6>
                                    <p>Copyright atualizado para 2025.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="feature">
                                    <h6><i class="bi bi-box"></i> Produtos de Exemplo</h6>
                                    <p>Base de produtos contextualizados para carrinho de praia adicionada.</p>
                                </div>
                                <div class="feature">
                                    <h6><i class="bi bi-bug"></i> Correções Gerais</h6>
                                    <p>Diversos bugs corrigidos e melhorias de performance implementadas.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h6>🔧 Atualizar Banco de Dados</h6>
                        <p class="small text-muted">Execute para adicionar os novos campos de preço</p>
                        <a href="update_database.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-database-gear"></i> Atualizar BD
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h6>📦 Adicionar Produtos</h6>
                        <p class="small text-muted">Populate o sistema com produtos de exemplo</p>
                        <a href="add_products.php" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-circle"></i> Adicionar Produtos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volta ao Sistema -->
        <div class="text-center mt-5">
            <a href="./" class="btn btn-lg btn-primary">
                <i class="bi bi-house"></i> Voltar ao Sistema
            </a>
        </div>

        <!-- Footer -->
        <div class="text-center mt-5 text-muted">
            <small>
                Sistema de Gestão para Carrinhos de Praia | Atualizado em <?= date('d/m/Y H:i') ?>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>