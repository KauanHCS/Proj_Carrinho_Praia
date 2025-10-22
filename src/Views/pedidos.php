<!-- Gerenciamento de Pedidos -->
<div class="content">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check"></i>
                        Gerenciamento de Pedidos
                    </h5>
                </div>
                <div class="card-body">
                    
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="filtroStatus" class="form-label">Filtrar por Status</label>
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
                            <label for="filtroData" class="form-label">Filtrar por Data</label>
                            <input type="date" class="form-control" id="filtroData" onchange="carregarPedidos()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button class="btn btn-outline-primary" onclick="carregarPedidos()">
                                    <i class="bi bi-arrow-clockwise"></i> Atualizar
                                </button>
                                <button class="btn btn-outline-secondary" onclick="limparFiltros()">
                                    <i class="bi bi-x-circle"></i> Limpar Filtros
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Estatísticas Rápidas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h3 class="text-warning" id="countPendente">0</h3>
                                    <small>Pendentes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h3 class="text-info" id="countEmPreparo">0</h3>
                                    <small>Em Preparo</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h3 class="text-primary" id="countPronto">0</h3>
                                    <small>Prontos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h3 class="text-success" id="countEntregue">0</h3>
                                    <small>Entregues Hoje</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lista de Pedidos -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-list-ul"></i> Lista de Pedidos</h6>
                        </div>
                        <div class="card-body">
                            <div id="loadingPedidos" class="text-center" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped" id="tabelaPedidos">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Pedido #</th>
                                            <th>Cliente</th>
                                            <th>Produtos</th>
                                            <th>Total</th>
                                            <th>Data/Hora</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="corpoPedidos">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="bi bi-inbox"></i> Nenhum pedido encontrado
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualizar detalhes do pedido -->
<div class="modal fade" id="modalDetalhesPedido" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-receipt"></i> Detalhes do Pedido #<span id="modalPedidoNumero"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
// Variáveis globais - garantir inicialização
window.pedidosData = window.pedidosData || [];
let pedidosData = window.pedidosData;

// Carregar pedidos quando a página é carregada
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se esta aba está ativa
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
        
        // Se já estiver ativa, carregar imediatamente
        if (pedidosTab.classList.contains('active')) {
            carregarPedidos();
        }
    }
});

// Função para carregar pedidos
function carregarPedidos() {
    const loading = document.getElementById('loadingPedidos');
    const tabela = document.getElementById('corpoPedidos');
    
    loading.style.display = 'block';
    
    const filtroStatus = document.getElementById('filtroStatus').value;
    const filtroData = document.getElementById('filtroData').value;
    
    let url = '../src/Controllers/actions.php?action=listarPedidos&_=' + Date.now();
    if (filtroStatus) url += `&status=${filtroStatus}`;
    if (filtroData) url += `&data=${filtroData}`;
    
    fetch(url, {
        credentials: 'same-origin' // Incluir cookies/sessão
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.text(); // Pegar como texto primeiro
    })
    .then(text => {
        console.log('Response text:', text);
        
        let data;
        try {
            data = JSON.parse(text);
            console.log('Parsed data:', data);
        } catch (e) {
            throw new Error('Resposta não é JSON válido: ' + e.message);
        }
        
        return data;
    })
    .then(data => {
        loading.style.display = 'none';
        
        if (data.success) {
            // Garantir que data.data é um array
            pedidosData = Array.isArray(data.data) ? data.data : [];
            window.pedidosData = pedidosData; // Atualizar global
            
            console.log('Pedidos carregados:', pedidosData.length);
            
            atualizarTabelaPedidos();
            atualizarEstatisticasPedidos();
        } else {
            tabela.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        <i class="bi bi-exclamation-circle"></i> Erro ao carregar pedidos: ${data.message}
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        loading.style.display = 'none';
        console.error('Erro ao carregar pedidos:', error);
        
        // Garantir que pedidosData é array mesmo com erro
        pedidosData = [];
        window.pedidosData = [];
        
        tabela.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger">
                    <i class="bi bi-exclamation-circle"></i> Erro de conexão: ${error.message}
                </td>
            </tr>
        `;
    });
}

// Função para atualizar a tabela de pedidos
function atualizarTabelaPedidos() {
    const tabela = document.getElementById('corpoPedidos');
    
    // Validar se pedidosData existe e é um array
    if (!pedidosData || !Array.isArray(pedidosData)) {
        console.warn('atualizarTabelaPedidos: pedidosData não é um array válido');
        pedidosData = [];
    }
    
    if (pedidosData.length === 0) {
        tabela.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted">
                    <i class="bi bi-inbox"></i> Nenhum pedido encontrado
                </td>
            </tr>
        `;
        return;
    }
    
    tabela.innerHTML = '';
    
    pedidosData.forEach(pedido => {
        const row = document.createElement('tr');
        
        const statusBadges = {
            'pendente': '<span class="badge bg-warning">Pendente</span>',
            'em_preparo': '<span class="badge bg-info">Em Preparo</span>',
            'pronto': '<span class="badge bg-primary">Pronto</span>',
            'entregue': '<span class="badge bg-success">Entregue</span>',
            'cancelado': '<span class="badge bg-danger">Cancelado</span>'
        };
        
        const produtos = JSON.parse(pedido.produtos || '[]');
        const produtosTexto = produtos.map(p => `${p.quantidade}x ${p.nome}`).join(', ');
        
        row.innerHTML = `
            <td><strong>#${pedido.id}</strong></td>
            <td>${pedido.nome_cliente || 'Cliente Anônimo'}</td>
            <td>
                <small>${produtosTexto.length > 50 ? produtosTexto.substring(0, 50) + '...' : produtosTexto}</small>
            </td>
            <td><strong>R$ ${parseFloat(pedido.total).toFixed(2)}</strong></td>
            <td>
                <small>${new Date(pedido.data_pedido).toLocaleString('pt-BR')}</small>
            </td>
            <td>${statusBadges[pedido.status] || '<span class="badge bg-secondary">N/A</span>'}</td>
            <td>
                <div class="btn-group" role="group">
                    ${pedido.status !== 'entregue' && pedido.status !== 'cancelado' ? 
                        `<select class="form-select form-select-sm" onchange="atualizarStatusPedido(${pedido.id}, this.value)" style="width: 120px;">
                            <option value="">Alterar Status</option>
                            ${pedido.status !== 'em_preparo' ? '<option value="em_preparo">Em Preparo</option>' : ''}
                            ${pedido.status !== 'pronto' ? '<option value="pronto">Pronto</option>' : ''}
                            ${pedido.status !== 'entregue' ? '<option value="entregue">Entregue</option>' : ''}
                            <option value="cancelado">Cancelar</option>
                        </select>` : 
                        '<small class="text-muted">Finalizado</small>'
                    }
                    <button class="btn btn-sm btn-outline-info" onclick="visualizarDetalhesPedido(${pedido.id})" title="Ver Detalhes">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </td>
        `;
        
        tabela.appendChild(row);
    });
}

// Função para atualizar estatísticas dos pedidos
function atualizarEstatisticasPedidos() {
    // Validar se pedidosData existe e é um array
    if (!pedidosData || !Array.isArray(pedidosData)) {
        console.warn('pedidosData não é um array válido');
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
            // Para entregues, só contar os de hoje
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
        credentials: 'same-origin', // Incluir cookies/sessão
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar o pedido na lista local
            const pedido = pedidosData.find(p => p.id == pedidoId);
            if (pedido) {
                pedido.status = novoStatus;
                atualizarTabelaPedidos();
                atualizarEstatisticasPedidos();
            }
            
            const statusTexto = {
                'em_preparo': 'Em Preparo',
                'pronto': 'Pronto',
                'entregue': 'Entregue',
                'cancelado': 'Cancelado'
            };
            
            alert(`Status do pedido #${pedidoId} atualizado para: ${statusTexto[novoStatus]}`);
        } else {
            alert('Erro ao atualizar status: ' + data.message);
            carregarPedidos(); // Recarregar para reverter mudança
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de conexão: ' + error);
        carregarPedidos(); // Recarregar para reverter mudança
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
            <div class="row mb-2">
                <div class="col-6">${produto.nome}</div>
                <div class="col-2">${produto.quantidade}x</div>
                <div class="col-2">R$ ${parseFloat(produto.preco).toFixed(2)}</div>
                <div class="col-2"><strong>R$ ${(produto.quantidade * produto.preco).toFixed(2)}</strong></div>
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
                <strong>Cliente:</strong> ${pedido.nome_cliente || 'Cliente Anônimo'}
            </div>
            <div class="col-md-6">
                <strong>Status:</strong> ${statusTexto[pedido.status]}
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Data do Pedido:</strong> ${new Date(pedido.data_pedido).toLocaleString('pt-BR')}
            </div>
            <div class="col-md-6">
                <strong>Total:</strong> R$ ${parseFloat(pedido.total).toFixed(2)}
            </div>
        </div>
        <hr>
        <h6>Produtos:</h6>
        <div class="row mb-2">
            <div class="col-6"><strong>Produto</strong></div>
            <div class="col-2"><strong>Qtd</strong></div>
            <div class="col-2"><strong>Preço</strong></div>
            <div class="col-2"><strong>Subtotal</strong></div>
        </div>
        ${produtosHtml}
        <hr>
        <div class="row">
            <div class="col-8"><strong>Total Geral:</strong></div>
            <div class="col-4"><h5>R$ ${parseFloat(pedido.total).toFixed(2)}</h5></div>
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

// Expor funções globalmente para debug
window.carregarPedidos = carregarPedidos;
window.atualizarTabelaPedidos = atualizarTabelaPedidos;
window.atualizarEstatisticasPedidos = atualizarEstatisticasPedidos;
</script>