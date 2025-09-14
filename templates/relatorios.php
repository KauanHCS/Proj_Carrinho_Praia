<div class="row">
    <div class="col-md-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="card-body">
                <h6><i class="bi bi-currency-dollar"></i> Vendas do Dia</h6>
                <h2 id="totalVendasDia">R$ <?php
                $conn = getConnection();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if ($usuarioId) {
                    $sql = "SELECT SUM(v.total) as total FROM vendas v 
                            JOIN itens_venda iv ON v.id = iv.venda_id 
                            JOIN produtos p ON iv.produto_id = p.id 
                            WHERE DATE(v.data) = CURDATE() AND p.usuario_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $usuarioId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    echo number_format($row['total'] ?? 0, 2, ',', '.');
                } else {
                    echo "0,00";
                }
                $conn->close();
                ?></h2>
                <small><span id="quantidadeItensDia">
                <?php
                $conn = getConnection();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if ($usuarioId) {
                    $sql = "SELECT SUM(iv.quantidade) as total_itens FROM itens_venda iv 
                            JOIN vendas v ON iv.venda_id = v.id 
                            JOIN produtos p ON iv.produto_id = p.id
                            WHERE DATE(v.data) = CURDATE() AND p.usuario_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $usuarioId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    echo $row['total_itens'] ?? 0;
                } else {
                    echo "0";
                }
                $conn->close();
                ?>
                </span> itens vendidos</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #0066cc, #0099ff);">
            <div class="card-body">
                <h6><i class="bi bi-graph-up"></i> Lucro Estimado</h6>
                <h2 id="lucroEstimado">R$ <?php
                $conn = getConnection();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if ($usuarioId) {
                    $sql = "SELECT SUM(v.total) as total FROM vendas v 
                            JOIN itens_venda iv ON v.id = iv.venda_id 
                            JOIN produtos p ON iv.produto_id = p.id 
                            WHERE DATE(v.data) = CURDATE() AND p.usuario_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $usuarioId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $lucro = ($row['total'] ?? 0) * 0.5; // Margem de 50%
                    echo number_format($lucro, 2, ',', '.');
                } else {
                    echo "0,00";
                }
                $conn->close();
                ?></h2>
                <small>Margem média estimada</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #dc3545, #c92066);">
            <div class="card-body">
                <h6><i class="bi bi-box"></i> Estoque Total</h6>
                <h2 id="totalEstoque">
                <?php
                $conn = getConnection();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if ($usuarioId) {
                    $sql = "SELECT SUM(quantidade) as total FROM produtos WHERE usuario_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $usuarioId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    echo $row['total'] ?? 0;
                } else {
                    echo "0";
                }
                $conn->close();
                ?>
                </h2>
                <small>
                <?php
                $conn = getConnection();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if ($usuarioId) {
                    $sql = "SELECT COUNT(*) as count FROM produtos WHERE quantidade <= limite_minimo AND usuario_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $usuarioId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    echo $row['count'] ?? 0;
                } else {
                    echo "0";
                }
                $conn->close();
                ?>
                produtos abaixo do limite</small>
            </div>
        </div>
    </div>
</div>

<!-- Export and Backup Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-download"></i> Exportar Dados e Backup
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Exportar Vendas</h6>
                        <p class="small text-muted">Baixar dados de vendas em formato CSV</p>
                        <div class="mb-2">
                            <input type="date" class="form-control form-control-sm mb-1" id="exportStartDate" placeholder="Data inicial">
                            <input type="date" class="form-control form-control-sm" id="exportEndDate" placeholder="Data final">
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="exportarVendas()">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Vendas
                        </button>
                    </div>
                    <div class="col-md-4">
                        <h6>Exportar Produtos</h6>
                        <p class="small text-muted">Baixar cadastro de produtos em CSV</p>
                        <br><br>
                        <button class="btn btn-success btn-sm" onclick="exportarProdutos()">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Produtos
                        </button>
                    </div>
                    <div class="col-md-4">
                        <h6>Backup dos Meus Dados</h6>
                        <p class="small text-muted">Fazer backup dos seus produtos, vendas e movimentações</p>
                        <br><br>
                        <button class="btn btn-warning btn-sm" onclick="criarBackup()">
                            <i class="bi bi-hdd"></i> Backup dos Meus Dados
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Produtos Mais Vendidos
            </div>
            <div class="card-body">
                <canvas id="graficoVendas" style="max-height: 400px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-trophy"></i> Top 5 Produtos
            </div>
            <div class="card-body" id="topProdutos">
                <?php
                $conn = getConnection();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if ($usuarioId) {
                    $sql = "SELECT p.nome, p.categoria, SUM(iv.quantidade) as total_vendido, 
                                   COUNT(DISTINCT iv.venda_id) as num_vendas
                            FROM itens_venda iv 
                            JOIN produtos p ON iv.produto_id = p.id 
                            WHERE p.usuario_id = ? 
                            GROUP BY p.id, p.nome, p.categoria 
                            ORDER BY total_vendido DESC 
                            LIMIT 5";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $usuarioId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    $position = 1;
                    while ($row = $result->fetch_assoc()) {
                        $badgeClass = $position == 1 ? 'bg-warning' : ($position == 2 ? 'bg-secondary' : 'bg-success');
                        $icon = $position == 1 ? 'bi-trophy' : ($position == 2 ? 'bi-award' : 'bi-star');
                        
                        echo '<div class="d-flex justify-content-between align-items-center mb-3">';
                        echo '<div>';
                        echo '<span class="badge ' . $badgeClass . ' me-2"><i class="bi ' . $icon . '"></i> #' . $position . '</span>';
                        echo '<strong>' . htmlspecialchars($row['nome']) . '</strong><br>';
                        echo '<small class="text-muted">' . ucfirst($row['categoria']) . '</small>';
                        echo '</div>';
                        echo '<div class="text-end">';
                        echo '<strong>' . $row['total_vendido'] . '</strong><br>';
                        echo '<small class="text-muted">' . $row['num_vendas'] . ' vendas</small>';
                        echo '</div>';
                        echo '</div>';
                        $position++;
                    }
                    
                    if ($result->num_rows == 0) {
                        echo '<p class="text-muted text-center">Nenhuma venda registrada ainda</p>';
                    }
                } else {
                    echo '<p class="text-muted text-center">Faça login para ver os dados</p>';
                }
                $conn->close();
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Script para gráfico -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Dados dos produtos mais vendidos (será carregado via AJAX)
function carregarGraficoProdutos() {
    fetch('actions.php?action=get_produtos_mais_vendidos')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                const ctx = document.getElementById('graficoVendas').getContext('2d');
                
                const chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.data.map(item => item.nome.length > 15 ? item.nome.substring(0, 15) + '...' : item.nome),
                        datasets: [{
                            label: 'Quantidade Vendida',
                            data: data.data.map(item => item.total_vendido),
                            backgroundColor: [
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(153, 102, 255, 0.8)',
                                'rgba(255, 159, 64, 0.8)',
                                'rgba(199, 199, 199, 0.8)',
                                'rgba(83, 102, 255, 0.8)',
                                'rgba(255, 99, 132, 0.8)'
                            ],
                            borderColor: [
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)',
                                'rgba(199, 199, 199, 1)',
                                'rgba(83, 102, 255, 1)',
                                'rgba(255, 99, 132, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const produto = data.data[context.dataIndex];
                                        return [
                                            'Produto: ' + produto.nome,
                                            'Quantidade: ' + produto.total_vendido,
                                            'Categoria: ' + produto.categoria,
                                            'N° de vendas: ' + produto.num_vendas
                                        ];
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                document.getElementById('graficoVendas').style.display = 'none';
                document.querySelector('#graficoVendas').parentElement.innerHTML = 
                    '<p class="text-center text-muted">Nenhum dado de vendas disponível para gráfico</p>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar gráfico:', error);
            document.querySelector('#graficoVendas').parentElement.innerHTML = 
                '<p class="text-center text-danger">Erro ao carregar dados do gráfico</p>';
        });
}

// Carregar gráfico quando a aba de relatórios for ativada
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se estamos na aba de relatórios
    if (document.getElementById('graficoVendas')) {
        carregarGraficoProdutos();
    }
});

// Recarregar gráfico quando a aba de relatórios for clicada
if (typeof showTab === 'function') {
    const originalShowTab = showTab;
    showTab = function(tabName) {
        originalShowTab(tabName);
        if (tabName === 'relatorios') {
            setTimeout(carregarGraficoProdutos, 100);
        }
    }
}
</script>
