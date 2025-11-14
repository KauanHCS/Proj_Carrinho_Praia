// ============================================
// SISTEMA DE FIADO/CADERNETA - JavaScript
// ============================================

let clientesFiado = [];
let filtroAtual = 'todos';

// ============================================
// INICIALIZAÇÃO
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema de Fiado inicializado');
    carregarDashboard();
    carregarClientes();
});

// ============================================
// DASHBOARD E KPIs
// ============================================

async function carregarDashboard() {
    try {
        const response = await fetch('../src/Controllers/actions.php?action=getDashboardFiado');
        const data = await response.json();
        
        if (data.success) {
            const dash = data.data;
            
            document.getElementById('totalReceber').textContent = formatarMoeda(dash.total_receber);
            document.getElementById('qtdClientes').textContent = dash.qtd_clientes;
            
            document.getElementById('clientesInadimplentes').textContent = dash.clientes_inadimplentes;
            document.getElementById('valorInadimplente').textContent = dash.valor_inadimplente.toFixed(2).replace('.', ',');
            
            document.getElementById('recebidoHoje').textContent = formatarMoeda(dash.recebido_hoje);
            document.getElementById('qtdPagamentosHoje').textContent = dash.qtd_pagamentos_hoje;
            
            document.getElementById('vendasMes').textContent = formatarMoeda(dash.vendas_mes);
            document.getElementById('qtdVendasMes').textContent = dash.qtd_vendas_mes;
        }
    } catch (error) {
        console.error('Erro ao carregar dashboard:', error);
    }
}

// ============================================
// LISTAGEM DE CLIENTES
// ============================================

async function carregarClientes() {
    try {
        const response = await fetch('../src/Controllers/actions.php?action=listarClientesFiado');
        const data = await response.json();
        
        if (data.success) {
            clientesFiado = data.data;
            renderizarClientes();
        } else {
            document.getElementById('listaClientes').innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">${data.message}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro ao carregar clientes:', error);
        document.getElementById('listaClientes').innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">Erro ao carregar clientes</p>
            </div>
        `;
    }
}

function renderizarClientes() {
    const container = document.getElementById('listaClientes');
    const busca = document.getElementById('buscaCliente').value.toLowerCase();
    
    let clientesFiltrados = clientesFiado.filter(cliente => {
        // Filtro de busca
        const matchBusca = cliente.nome.toLowerCase().includes(busca) || 
                          (cliente.telefone && cliente.telefone.includes(busca));
        
        if (!matchBusca) return false;
        
        // Filtros de status
        const saldo = parseFloat(cliente.saldo_devedor);
        const diasSemComprar = parseInt(cliente.dias_sem_comprar) || 0;
        
        switch(filtroAtual) {
            case 'devedores':
                return saldo > 0;
            case 'inadimplentes':
                return saldo > 0 && diasSemComprar > 30;
            case 'quitados':
                return saldo === 0;
            default:
                return true;
        }
    });
    
    if (clientesFiltrados.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="text-muted mt-3">Nenhum cliente encontrado</p>
                <button class="btn btn-primary mt-2" onclick="abrirModalNovoCliente()">
                    <i class="bi bi-person-plus"></i>
                    Cadastrar Primeiro Cliente
                </button>
            </div>
        `;
        return;
    }
    
    let html = '';
    clientesFiltrados.forEach(cliente => {
        const saldo = parseFloat(cliente.saldo_devedor);
        const limite = parseFloat(cliente.limite_credito);
        const percentualLimite = limite > 0 ? (saldo / limite) * 100 : 0;
        const diasSemComprar = parseInt(cliente.dias_sem_comprar) || 0;
        
        let badgeStatus = '';
        let corCard = '';
        
        if (saldo === 0) {
            badgeStatus = '<span class="badge bg-success">Quitado</span>';
        } else if (diasSemComprar > 30) {
            badgeStatus = '<span class="badge bg-danger">Inadimplente</span>';
            corCard = 'border-danger';
        } else if (percentualLimite > 80) {
            badgeStatus = '<span class="badge bg-warning">Próximo ao limite</span>';
            corCard = 'border-warning';
        } else {
            badgeStatus = '<span class="badge bg-info">Ativo</span>';
        }
        
        html += `
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card cliente-card ${corCard}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-circle text-primary"></i>
                                ${cliente.nome}
                            </h5>
                            ${badgeStatus}
                        </div>
                        
                        <div class="cliente-info mb-3">
                            ${cliente.telefone ? `<small class="text-muted"><i class="bi bi-telephone"></i> ${cliente.telefone}</small><br>` : ''}
                            <small class="text-muted"><i class="bi bi-calendar"></i> Cliente desde ${formatarData(cliente.data_cadastro)}</small>
                        </div>
                        
                        <div class="cliente-saldo mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Saldo Devedor:</span>
                                <strong class="text-danger fs-5">${formatarMoeda(saldo)}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Limite:</span>
                                <span class="small">${formatarMoeda(limite)}</span>
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar ${percentualLimite > 80 ? 'bg-danger' : percentualLimite > 50 ? 'bg-warning' : 'bg-success'}" 
                                     style="width: ${Math.min(percentualLimite, 100)}%"></div>
                            </div>
                        </div>
                        
                        <div class="cliente-acoes">
                            <button class="btn btn-sm btn-success flex-fill" onclick="abrirModalPagamento(${cliente.id}, '${cliente.nome}', ${saldo})" ${saldo === 0 ? 'disabled' : ''}>
                                <i class="bi bi-cash-coin"></i>
                                Receber
                            </button>
                            <button class="btn btn-sm btn-info flex-fill" onclick="verHistorico(${cliente.id})">
                                <i class="bi bi-clock-history"></i>
                                Histórico
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="editarCliente(${cliente.id})" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ============================================
// FILTROS
// ============================================

function filtrarClientes() {
    renderizarClientes();
}

function mudarFiltro(filtro) {
    filtroAtual = filtro;
    
    // Atualizar botões
    document.querySelectorAll('[data-filtro]').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-filtro="${filtro}"]`).classList.add('active');
    
    renderizarClientes();
}

// ============================================
// NOVO CLIENTE
// ============================================

function abrirModalNovoCliente() {
    document.getElementById('formNovoCliente').reset();
    const modal = new bootstrap.Modal(document.getElementById('modalNovoCliente'));
    modal.show();
}

async function salvarNovoCliente() {
    const nome = document.getElementById('fiadoNomeCliente').value.trim();
    
    if (!nome) {
        alert('Por favor, preencha o nome do cliente');
        return;
    }
    
    const dados = {
        action: 'cadastrarClienteFiado',
        nome: nome,
        telefone: document.getElementById('fiadoTelefoneCliente').value.trim(),
        cpf: document.getElementById('fiadoCpfCliente').value.trim(),
        endereco: document.getElementById('fiadoEnderecoCliente').value.trim(),
        limite_credito: document.getElementById('fiadoLimiteCredito').value,
        observacoes: document.getElementById('fiadoObservacoesCliente').value.trim()
    };
    
    try {
        const response = await fetch('../src/Controllers/actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(dados)
        });
        
        const data = await response.json();
        
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoCliente'));
            modal.hide();
            
            alert('✅ Cliente cadastrado com sucesso!');
            carregarClientes();
            carregarDashboard();
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao cadastrar cliente');
    }
}

// ============================================
// PAGAMENTO
// ============================================

function abrirModalPagamento(clienteId, clienteNome, saldoDevedor) {
    document.getElementById('pagamentoClienteId').value = clienteId;
    document.getElementById('pagamentoClienteNome').textContent = clienteNome;
    document.getElementById('pagamentoSaldoDevedor').textContent = formatarMoeda(saldoDevedor);
    document.getElementById('pagamentoSaldoDevedor').dataset.valor = saldoDevedor;
    document.getElementById('valorPagamento').value = '';
    document.getElementById('fiadoObservacoesPagamento').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('modalRegistrarPagamento'));
    modal.show();
}

function preencherValorTotal() {
    const saldo = parseFloat(document.getElementById('pagamentoSaldoDevedor').dataset.valor);
    document.getElementById('valorPagamento').value = saldo.toFixed(2);
}

async function salvarPagamento() {
    const clienteId = document.getElementById('pagamentoClienteId').value;
    const valor = document.getElementById('valorPagamento').value;
    
    if (!valor || parseFloat(valor) <= 0) {
        alert('Por favor, informe um valor válido');
        return;
    }
    
    const dados = {
        action: 'registrarPagamentoFiado',
        cliente_id: clienteId,
        valor: valor,
        forma_pagamento: document.getElementById('formaPagamentoPagamento').value,
        observacoes: document.getElementById('fiadoObservacoesPagamento').value.trim()
    };
    
    try {
        const response = await fetch('../src/Controllers/actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(dados)
        });
        
        const data = await response.json();
        
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalRegistrarPagamento'));
            modal.hide();
            
            alert('✅ Pagamento registrado com sucesso!');
            carregarClientes();
            carregarDashboard();
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao registrar pagamento');
    }
}

// ============================================
// HISTÓRICO DO CLIENTE
// ============================================

async function verHistorico(clienteId) {
    const modal = new bootstrap.Modal(document.getElementById('modalHistoricoCliente'));
    modal.show();
    
    document.getElementById('historicoClienteId').value = clienteId;
    document.getElementById('historicoConteudo').innerHTML = `
        <div class="text-center py-4">
            <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
            <p class="text-muted">Carregando histórico...</p>
        </div>
    `;
    
    try {
        const response = await fetch(`../src/Controllers/actions.php?action=obterHistoricoCliente&cliente_id=${clienteId}`);
        const data = await response.json();
        
        if (data.success) {
            const cliente = data.data.cliente;
            const historico = data.data.historico;
            
            document.getElementById('historicoClienteNome').textContent = cliente.nome;
            document.getElementById('historicoTelefone').textContent = cliente.telefone || '-';
            document.getElementById('historicoSaldo').textContent = formatarMoeda(cliente.saldo_devedor);
            
            if (historico.length === 0) {
                document.getElementById('historicoConteudo').innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
                        <p class="text-muted">Nenhuma movimentação encontrada</p>
                    </div>
                `;
                return;
            }
            
            let html = '<div class="timeline">';
            historico.forEach(item => {
                const valor = parseFloat(item.valor);
                let icone, cor, texto;
                
                if (item.tipo === 'compra') {
                    icone = 'bi-cart-plus';
                    cor = 'text-danger';
                    texto = 'Compra Fiada';
                } else if (item.tipo === 'pagamento') {
                    icone = 'bi-cash-coin';
                    cor = 'text-success';
                    texto = 'Pagamento Recebido';
                } else {
                    icone = 'bi-pencil-square';
                    cor = 'text-info';
                    texto = 'Ajuste';
                }
                
                html += `
                    <div class="timeline-item mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="${cor}">
                                    <i class="bi ${icone}"></i>
                                    ${texto}
                                </h6>
                                <p class="mb-1">${formatarMoeda(Math.abs(valor))}</p>
                                ${item.forma_pagamento ? `<small class="text-muted">Forma: ${item.forma_pagamento}</small><br>` : ''}
                                ${item.observacoes ? `<small class="text-muted">${item.observacoes}</small><br>` : ''}
                            </div>
                            <small class="text-muted">${formatarDataHora(item.data_pagamento)}</small>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            document.getElementById('historicoConteudo').innerHTML = html;
        } else {
            document.getElementById('historicoConteudo').innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 2rem;"></i>
                    <p class="text-muted">${data.message}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro:', error);
        document.getElementById('historicoConteudo').innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                <p class="text-muted">Erro ao carregar histórico</p>
            </div>
        `;
    }
}

// ============================================
// EDITAR CLIENTE (PLACEHOLDER)
// ============================================

function editarCliente(clienteId) {
    // TODO: Implementar modal de edição
    alert('Funcionalidade de edição será implementada em breve');
}

// ============================================
// UTILITÁRIOS
// ============================================

function formatarMoeda(valor) {
    return 'R$ ' + parseFloat(valor).toFixed(2).replace('.', ',');
}

function formatarData(data) {
    const d = new Date(data);
    return d.toLocaleDateString('pt-BR');
}

function formatarDataHora(data) {
    const d = new Date(data);
    return d.toLocaleString('pt-BR');
}
