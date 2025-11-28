<!-- Gerenciamento de Pedidos - Interface Moderna -->
<style>
    .pedidos-container {
        padding: 20px;
    }
    
    .pedidos-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 30px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .pedidos-header h2 {
        margin: 0;
        font-weight: 600;
        font-size: 28px;
    }
    
    .pedidos-header p {
        margin: 5px 0 0 0;
        opacity: 0.9;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    
    .stat-card.pendente { border-left-color: #ffc107; }
    .stat-card.em-preparo { border-left-color: #17a2b8; }
    .stat-card.pronto { border-left-color: #007bff; }
    .stat-card.entregue { border-left-color: #28a745; }
    
    .stat-card .icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 15px;
    }
    
    .stat-card.pendente .icon { background: #fff3cd; color: #ffc107; }
    .stat-card.em-preparo .icon { background: #d1ecf1; color: #17a2b8; }
    .stat-card.pronto .icon { background: #cce5ff; color: #007bff; }
    .stat-card.entregue .icon { background: #d4edda; color: #28a745; }
    
    .stat-card .number {
        font-size: 32px;
        font-weight: 700;
        margin: 0;
        line-height: 1;
    }
    
    .stat-card .label {
        color: #6c757d;
        font-size: 14px;
        margin-top: 5px;
    }
    
    .filters-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .filters-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .pedidos-list {
        display: grid;
        gap: 20px;
    }
    
    .pedido-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid;
    }
    
    .pedido-card:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    
    .pedido-card.pendente { border-left-color: #ffc107; }
    .pedido-card.em_preparo { border-left-color: #17a2b8; }
    .pedido-card.pronto { border-left-color: #007bff; }
    .pedido-card.entregue { border-left-color: #28a745; }
    .pedido-card.cancelado { border-left-color: #dc3545; }
    
    .pedido-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }
    
    .pedido-info h5 {
        margin: 0;
        font-size: 20px;
        color: #333;
    }
    
    .pedido-info .cliente {
        color: #6c757d;
        font-size: 14px;
        margin-top: 5px;
    }
    
    .pedido-status {
        text-align: right;
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-badge.pendente { background: #fff3cd; color: #856404; }
    .status-badge.em_preparo { background: #d1ecf1; color: #0c5460; }
    .status-badge.pronto { background: #cce5ff; color: #004085; }
    .status-badge.entregue { background: #d4edda; color: #155724; }
    .status-badge.cancelado { background: #f8d7da; color: #721c24; }
    
    .pedido-produtos {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .produto-item {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        font-size: 14px;
    }
    
    .produto-item:not(:last-child) {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 8px;
        margin-bottom: 8px;
    }
    
    .pedido-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 2px solid #e9ecef;
    }
    
    .pedido-total {
        font-size: 22px;
        font-weight: 700;
        color: #28a745;
    }
    
    .pedido-data {
        font-size: 13px;
        color: #6c757d;
    }
    
    .pedido-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-action {
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .btn-primary-custom {
        background: #667eea;
        color: white;
    }
    
    .btn-info-custom {
        background: #17a2b8;
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .loading-spinner {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 60px;
    }
    
    .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .btn-status {
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin: 2px;
    }
    
    .btn-status:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .btn-status.em-preparo {
        background: #17a2b8;
        color: white;
    }
    
    .btn-status.pronto {
        background: #007bff;
        color: white;
    }
    
    .btn-status.entregue {
        background: #28a745;
        color: white;
    }
    
    .btn-status i {
        font-size: 14px;
    }
</style>

<div class="pedidos-container">
    <!-- Header -->
    <div class="pedidos-header">
        <h2><i class="bi bi-clipboard-check"></i> Gerenciamento de Pedidos</h2>
        <p>Acompanhe e gerencie todos os pedidos em tempo real</p>
    </div>
    
    <!-- Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card pendente">
            <div class="icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <h3 class="number" id="countPendente">0</h3>
            <p class="label">Pendentes</p>
        </div>
        
        <div class="stat-card em-preparo">
            <div class="icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <h3 class="number" id="countEmPreparo">0</h3>
            <p class="label">Em Preparo</p>
        </div>
        
        <div class="stat-card pronto">
            <div class="icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <h3 class="number" id="countPronto">0</h3>
            <p class="label">Prontos</p>
        </div>
        
        <div class="stat-card entregue">
            <div class="icon">
                <i class="bi bi-check2-all"></i>
            </div>
            <h3 class="number" id="countEntregue">0</h3>
            <p class="label">Entregues Hoje</p>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="filters-card">
        <div class="filters-title">
            <i class="bi bi-funnel"></i> Filtros
        </div>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Status</label>
                <select class="form-select" id="filtroStatus" onchange="carregarPedidos()">
                    <option value="">Todos os Status</option>
                    <option value="pendente">Pendente</option>
                    <option value="em_preparo">Em Preparo</option>
                    <option value="pronto">Pronto</option>
                    <option value="entregue">Entregue</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Data</label>
                <input type="date" class="form-control" id="filtroData" onchange="carregarPedidos()">
            </div>
            <div class="col-md-6 d-flex align-items-end gap-2">
                <button class="btn btn-primary" onclick="carregarPedidos()">
                    <i class="bi bi-arrow-clockwise"></i> Atualizar
                </button>
                <button class="btn btn-outline-secondary" onclick="limparFiltros()">
                    <i class="bi bi-x-circle"></i> Limpar Filtros
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading -->
    <div id="loadingPedidos" class="loading-spinner" style="display: none;">
        <div class="spinner"></div>
    </div>
    
    <!-- Lista de Pedidos -->
    <div id="pedidosList" class="pedidos-list">
        <!-- Pedidos serão inseridos aqui dinamicamente -->
    </div>
</div>

<!-- Modal para visualizar detalhes do pedido -->
<div class="modal fade" id="modalDetalhesPedido" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 15px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title">
                    <i class="bi bi-receipt"></i> Detalhes do Pedido #<span id="modalPedidoNumero"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalPedidoBody">
                <!-- Conteúdo carregado dinamicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Variáveis globais
window.pedidosData = window.pedidosData || [];
let pedidosData = window.pedidosData;

// Carregar pedidos quando a página é carregada
document.addEventListener('DOMContentLoaded', function() {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const target = mutation.target;
                if (target.id === 'pedidos' && target.classList.contains('active')) {
                    carregarPedidos();
                }
            }
        });
    });
    
    const pedidosTab = document.getElementById('pedidos');
    if (pedidosTab) {
        observer.observe(pedidosTab, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        if (pedidosTab.classList.contains('active')) {
            carregarPedidos();
        }
    }
});

// Função para carregar pedidos
function carregarPedidos() {
    const loading = document.getElementById('loadingPedidos');
    const lista = document.getElementById('pedidosList');
    
    loading.style.display = 'flex';
    lista.innerHTML = '';
    
    const filtroStatus = document.getElementById('filtroStatus').value;
    const filtroData = document.getElementById('filtroData').value;
    
    let url = '../src/Controllers/actions.php?action=listarPedidos&_=' + Date.now();
    if (filtroStatus) url += `&status=${filtroStatus}`;
    if (filtroData) url += `&data=${filtroData}`;
    
    fetch(url, {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        loading.style.display = 'none';
        
        if (data.success) {
            pedidosData = Array.isArray(data.data) ? data.data : [];
            window.pedidosData = pedidosData;
            
            atualizarListaPedidos();
            atualizarEstatisticasPedidos();
        } else {
            mostrarEstadoVazio('Erro ao carregar pedidos: ' + data.message);
        }
    })
    .catch(error => {
        loading.style.display = 'none';
        console.error('Erro ao carregar pedidos:', error);
        pedidosData = [];
        window.pedidosData = [];
        mostrarEstadoVazio('Erro de conexão. Tente novamente.');
    });
}

// Função para atualizar a lista de pedidos
function atualizarListaPedidos() {
    const lista = document.getElementById('pedidosList');
    
    if (!pedidosData || !Array.isArray(pedidosData)) {
        pedidosData = [];
    }
    
    if (pedidosData.length === 0) {
        mostrarEstadoVazio('Nenhum pedido encontrado');
        return;
    }
    
    lista.innerHTML = '';
    
    pedidosData.forEach(pedido => {
        const card = criarCardPedido(pedido);
        lista.appendChild(card);
    });
}

// Função para criar card de pedido
function criarCardPedido(pedido) {
    const card = document.createElement('div');
    card.className = `pedido-card ${pedido.status}`;
    
    const produtos = JSON.parse(pedido.produtos || '[]');
    let produtosHtml = '';
    
    produtos.forEach(produto => {
        produtosHtml += `
            <div class="produto-item">
                <span>${produto.quantidade}x ${produto.nome}</span>
                <span class="fw-semibold">R$ ${(produto.quantidade * produto.preco).toFixed(2)}</span>
            </div>
        `;
    });
    
    const statusTexto = {
        'pendente': 'Pendente',
        'em_preparo': 'Em Preparo',
        'pronto': 'Pronto',
        'entregue': 'Entregue',
        'cancelado': 'Cancelado'
    };
    
    const podeAlterar = pedido.status !== 'entregue' && pedido.status !== 'cancelado';
    
    card.innerHTML = `
        <div class="pedido-header">
            <div class="pedido-info">
                <h5><i class="bi bi-receipt"></i> Pedido #${pedido.id}</h5>
                <p class="cliente"><i class="bi bi-person"></i> ${pedido.nome_cliente || 'Cliente Anônimo'}</p>
                <p class="pedido-data"><i class="bi bi-clock"></i> ${new Date(pedido.data_pedido).toLocaleString('pt-BR')}</p>
            </div>
            <div class="pedido-status">
                <span class="status-badge ${pedido.status}">${statusTexto[pedido.status] || 'N/A'}</span>
            </div>
        </div>
        
        <div class="pedido-produtos">
            ${produtosHtml}
        </div>
        
        <div class="pedido-footer">
            <div class="pedido-total">
                <i class="bi bi-currency-dollar"></i> R$ ${parseFloat(pedido.total).toFixed(2)}
            </div>
            <div class="pedido-actions">
                ${podeAlterar ? `
                    ${pedido.status === 'pendente' ? `
                        <button class="btn-status em-preparo" onclick="atualizarStatusPedido(${pedido.id}, 'em_preparo')">
                            <i class="bi bi-hourglass-split"></i> Em Preparo
                        </button>
                    ` : ''}
                    ${pedido.status === 'em_preparo' ? `
                        <button class="btn-status pronto" onclick="atualizarStatusPedido(${pedido.id}, 'pronto')">
                            <i class="bi bi-check-circle"></i> Pronto
                        </button>
                    ` : ''}
                    ${pedido.status === 'pronto' ? `
                        <button class="btn-status entregue" onclick="atualizarStatusPedido(${pedido.id}, 'entregue')">
                            <i class="bi bi-check2-all"></i> Entregue
                        </button>
                    ` : ''}
                ` : ''}
                <button class="btn-action btn-info-custom" onclick="visualizarDetalhesPedido(${pedido.id})">
                    <i class="bi bi-eye"></i> Detalhes
                </button>
            </div>
        </div>
    `;
    
    return card;
}

// Função para mostrar estado vazio
function mostrarEstadoVazio(mensagem) {
    const lista = document.getElementById('pedidosList');
    lista.innerHTML = `
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h5>${mensagem}</h5>
        </div>
    `;
}

// Função para atualizar estatísticas dos pedidos
function atualizarEstatisticasPedidos() {
    if (!pedidosData || !Array.isArray(pedidosData)) {
        pedidosData = [];
    }
    
    const hoje = new Date().toISOString().split('T')[0];
    
    const counts = {
        pendente: 0,
        em_preparo: 0,
        pronto: 0,
        entregue: 0
    };
    
    pedidosData.forEach(pedido => {
        if (counts.hasOwnProperty(pedido.status)) {
            if (pedido.status === 'entregue') {
                if (pedido.data_pedido.startsWith(hoje)) {
                    counts.entregue++;
                }
            } else {
                counts[pedido.status]++;
            }
        }
    });
    
    document.getElementById('countPendente').textContent = counts.pendente;
    document.getElementById('countEmPreparo').textContent = counts.em_preparo;
    document.getElementById('countPronto').textContent = counts.pronto;
    document.getElementById('countEntregue').textContent = counts.entregue;
}

// Função para atualizar status do pedido
function atualizarStatusPedido(pedidoId, novoStatus) {
    if (!novoStatus) return;
    
    const formData = new FormData();
    formData.append('action', 'atualizarStatusPedido');
    formData.append('pedido_id', pedidoId);
    formData.append('novo_status', novoStatus);
    
    fetch('../src/Controllers/actions.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const pedido = pedidosData.find(p => p.id == pedidoId);
            if (pedido) {
                pedido.status = novoStatus;
                atualizarListaPedidos();
                atualizarEstatisticasPedidos();
            }
            
            const statusTexto = {
                'em_preparo': 'Em Preparo',
                'pronto': 'Pronto',
                'entregue': 'Entregue',
                'cancelado': 'Cancelado'
            };
            
            alert(`✓ Status do pedido #${pedidoId} atualizado para: ${statusTexto[novoStatus]}`);
        } else {
            alert('Erro ao atualizar status: ' + data.message);
            carregarPedidos();
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de conexão: ' + error);
        carregarPedidos();
    });
}

// Função para visualizar detalhes do pedido
function visualizarDetalhesPedido(pedidoId) {
    const pedido = pedidosData.find(p => p.id == pedidoId);
    if (!pedido) return;
    
    document.getElementById('modalPedidoNumero').textContent = pedido.id;
    
    const produtos = JSON.parse(pedido.produtos || '[]');
    let produtosHtml = '';
    
    produtos.forEach(produto => {
        produtosHtml += `
            <div class="row mb-2 align-items-center">
                <div class="col-6">${produto.nome}</div>
                <div class="col-2 text-center">${produto.quantidade}x</div>
                <div class="col-2 text-end">R$ ${parseFloat(produto.preco).toFixed(2)}</div>
                <div class="col-2 text-end"><strong>R$ ${(produto.quantidade * produto.preco).toFixed(2)}</strong></div>
            </div>
        `;
    });
    
    const statusTexto = {
        'pendente': 'Pendente',
        'em_preparo': 'Em Preparo',
        'pronto': 'Pronto',
        'entregue': 'Entregue',
        'cancelado': 'Cancelado'
    };
    
    document.getElementById('modalPedidoBody').innerHTML = `
        <div class="row mb-3">
            <div class="col-md-6">
                <strong><i class="bi bi-person"></i> Cliente:</strong><br>
                ${pedido.nome_cliente || 'Cliente Anônimo'}
            </div>
            <div class="col-md-6">
                <strong><i class="bi bi-tag"></i> Status:</strong><br>
                <span class="status-badge ${pedido.status}">${statusTexto[pedido.status]}</span>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong><i class="bi bi-clock"></i> Data do Pedido:</strong><br>
                ${new Date(pedido.data_pedido).toLocaleString('pt-BR')}
            </div>
            <div class="col-md-6">
                <strong><i class="bi bi-currency-dollar"></i> Total:</strong><br>
                <span class="text-success fs-5 fw-bold">R$ ${parseFloat(pedido.total).toFixed(2)}</span>
            </div>
        </div>
        <hr>
        <h6><i class="bi bi-basket"></i> Produtos:</h6>
        <div class="row mb-2 fw-bold text-muted">
            <div class="col-6">Produto</div>
            <div class="col-2 text-center">Qtd</div>
            <div class="col-2 text-end">Preço</div>
            <div class="col-2 text-end">Subtotal</div>
        </div>
        ${produtosHtml}
        <hr>
        <div class="row">
            <div class="col-8 text-end"><strong>Total Geral:</strong></div>
            <div class="col-4 text-end"><h4 class="text-success mb-0">R$ ${parseFloat(pedido.total).toFixed(2)}</h4></div>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('modalDetalhesPedido')).show();
}

// Função para limpar filtros
function limparFiltros() {
    document.getElementById('filtroStatus').value = '';
    document.getElementById('filtroData').value = '';
    carregarPedidos();
}

// Expor funções globalmente
window.carregarPedidos = carregarPedidos;
window.atualizarListaPedidos = atualizarListaPedidos;
window.atualizarEstatisticasPedidos = atualizarEstatisticasPedidos;
</script>
