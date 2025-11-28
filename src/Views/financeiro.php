<!-- Página do Financeiro -->
<div class="content">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-cash-stack"></i>
                        Gerenciamento Financeiro
                    </h5>
                    <small class="text-muted">Visualizar e processar pagamentos de vendas</small>
                </div>
                <div class="card-body">
                    
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="filtroStatus" class="form-label">Status da Venda</label>
                            <select id="filtroStatus" class="form-select" onchange="carregarVendasFinanceiro()">
                                <option value="">Todos os status</option>
                                <option value="concluida" selected>Concluída</option>
                                <option value="pendente">Pendente</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtroVendedor" class="form-label">Vendedor</label>
                            <select id="filtroVendedor" class="form-select" onchange="carregarVendasFinanceiro()">
                                <option value="">Todos os vendedores</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtroData" class="form-label">Data</label>
                            <input type="date" id="filtroData" class="form-control" onchange="carregarVendasFinanceiro()">
                        </div>
                    </div>

                    <!-- Estatísticas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h5 class="text-warning" id="totalPendente">R$ 0,00</h5>
                                    <small class="text-muted">Vendas Pendentes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h5 class="text-success" id="totalPago">R$ 0,00</h5>
                                    <small class="text-muted">Vendas Concluídas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h5 class="text-primary" id="totalVendas">0</h5>
                                    <small class="text-muted">Total de Vendas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h5 class="text-info" id="mediaVenda">R$ 0,00</h5>
                                    <small class="text-muted">Ticket Médio</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div id="loadingFinanceiro" class="text-center" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>

                    <!-- Cards de Vendas -->
                    <div id="vendasContainer" class="row g-3">
                        <div class="col-12 text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1"></i>
                            <p>Nenhuma venda encontrada</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Processar Pagamento -->
<div class="modal fade" id="modalPagamento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-credit-card"></i>
                    Processar Pagamento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalhesVendaPagamento"></div>
                
                <hr>
                
                <form id="formPagamento">
                    <input type="hidden" id="vendaIdPagamento">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label for="metodoPagamento" class="form-label">Método de Pagamento</label>
                            <select id="metodoPagamento" class="form-select" required>
                                <option value="">Selecione o método</option>
                                <option value="dinheiro">
                                    <i class="bi bi-cash"></i> Dinheiro
                                </option>
                                <option value="cartao">
                                    <i class="bi bi-credit-card"></i> Cartão
                                </option>
                                <option value="pix">
                                    <i class="bi bi-qr-code"></i> PIX
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="valorRecebido" class="form-label">Valor Recebido</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" id="valorRecebido" class="form-control" step="0.01" required readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3" id="campoTroco" style="display: none;">
                        <div class="col-md-6">
                            <label for="trocoCalculado" class="form-label">Troco</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" id="trocoCalculado" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label for="observacoesPagamento" class="form-label">Observações (opcional)</label>
                        <textarea id="observacoesPagamento" class="form-control" rows="2" placeholder="Informações adicionais sobre o pagamento..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarPagamento()">
                    <i class="bi bi-check-circle"></i> Confirmar Pagamento
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variáveis globais
let vendedores = [];
let vendaAtual = null;

// Carregar dados iniciais
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se esta aba está ativa
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const target = mutation.target;
                if (target.id === 'financeiro' && target.classList.contains('active')) {
                    carregarVendedores();
                    carregarVendasFinanceiro();
                }
            }
        });
    });
    
    const financeiroTab = document.getElementById('financeiro');
    if (financeiroTab) {
        observer.observe(financeiroTab, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        // Se já estiver ativa, carregar imediatamente
        if (financeiroTab.classList.contains('active')) {
            carregarVendedores();
            carregarVendasFinanceiro();
        }
    }
});

// Carregar lista de vendedores para filtro
function carregarVendedores() {
    fetch('../src/Controllers/actions.php?action=listarVendedores')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            vendedores = data.data;
            const select = document.getElementById('filtroVendedor');
            select.innerHTML = '<option value="">Todos os vendedores</option>';
            
            vendedores.forEach(vendedor => {
                select.innerHTML += `<option value="${vendedor.id}">${vendedor.nome}</option>`;
            });
        }
    })
    .catch(error => console.error('Erro ao carregar vendedores:', error));
}

// Carregar vendas para financeiro
function carregarVendasFinanceiro() {
    const loading = document.getElementById('loadingFinanceiro');
    const container = document.getElementById('vendasContainer');
    
    loading.style.display = 'block';
    
    const filtros = {
        status: document.getElementById('filtroStatus').value,
        vendedor: document.getElementById('filtroVendedor').value,
        data: document.getElementById('filtroData').value
    };
    
    const queryString = new URLSearchParams({
        action: 'listarVendasFinanceiro',
        ...filtros
    });
    
    fetch('../src/Controllers/actions.php?' + queryString)
    .then(response => response.json())
    .then(data => {
        loading.style.display = 'none';
        
        if (data.success) {
            atualizarEstatisticas(data.data);
            
            if (data.data.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1"></i>
                        <p>Nenhuma venda encontrada</p>
                    </div>
                `;
            } else {
                container.innerHTML = '';
                
                data.data.forEach(venda => {
                    const card = criarCardVenda(venda);
                    container.appendChild(card);
                });
            }
        } else {
            container.innerHTML = `
                <div class="col-12 text-center text-danger py-5">
                    <i class="bi bi-exclamation-circle fs-1"></i>
                    <p>Erro: ${data.message}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        loading.style.display = 'none';
        console.error('Erro:', error);
        container.innerHTML = `
            <div class="col-12 text-center text-danger py-5">
                <i class="bi bi-exclamation-circle fs-1"></i>
                <p>Erro de conexão</p>
            </div>
        `;
    });
}

// Criar card de venda
function criarCardVenda(venda) {
    const col = document.createElement('div');
    col.className = 'col-12 col-md-6 col-lg-4';
    
    const statusBadge = getStatusBadge(venda.status);
    const dataFormatada = new Date(venda.data).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    const formaPagamento = {
        'dinheiro': '<i class="bi bi-cash"></i> Dinheiro',
        'pix': '<i class="bi bi-qr-code"></i> PIX',
        'cartao': '<i class="bi bi-credit-card"></i> Cartão',
        'multiplo': '<i class="bi bi-wallet2"></i> Múltiplo'
    }[venda.forma_pagamento] || venda.forma_pagamento;
    
    col.innerHTML = `
        <div class="card h-100 shadow-sm" style="border-left: 4px solid ${venda.status === 'concluida' ? '#28a745' : venda.status === 'pendente' ? '#ffc107' : '#dc3545'};">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1">
                            <i class="bi bi-receipt"></i> Venda #${venda.id}
                            ${venda.numero_guardasol ? `<span class="badge bg-info">GS ${venda.numero_guardasol}</span>` : ''}
                        </h6>
                        <small class="text-muted">${dataFormatada}</small>
                    </div>
                    ${statusBadge}
                </div>
                
                <div class="mb-2">
                    <strong><i class="bi bi-person"></i> Cliente:</strong> 
                    <span>${venda.cliente_nome || 'Não informado'}</span>
                </div>
                
                ${venda.cliente_telefone ? `
                    <div class="mb-2">
                        <strong><i class="bi bi-telephone"></i> Telefone:</strong> 
                        <span>${venda.cliente_telefone}</span>
                    </div>
                ` : ''}
                
                <div class="mb-2">
                    <strong><i class="bi bi-box"></i> Produtos:</strong>
                    <div class="small text-muted mt-1">${venda.produtos_info || 'Sem produtos'}</div>
                </div>
                
                <div class="mb-2">
                    <strong><i class="bi bi-wallet"></i> Pagamento:</strong> 
                    <span>${formaPagamento}</span>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-success">R$ ${parseFloat(venda.total).toFixed(2)}</h5>
                        ${venda.desconto > 0 ? `<small class="text-muted">Desconto: R$ ${parseFloat(venda.desconto).toFixed(2)}</small>` : ''}
                    </div>
                    <button class="btn btn-sm btn-outline-info" onclick="visualizarDetalhes(${venda.id})">
                        <i class="bi bi-eye"></i> Detalhes
                    </button>
                </div>
            </div>
        </div>
    `;
    
    return col;
}

// Atualizar estatísticas
function atualizarEstatisticas(vendas) {
    let totalPendente = 0;
    let totalConcluido = 0;
    let totalVendas = vendas.length;
    let somaTotal = 0;
    
    vendas.forEach(venda => {
        const valor = parseFloat(venda.total);
        somaTotal += valor;
        
        if (venda.status === 'pendente') {
            totalPendente += valor;
        } else if (venda.status === 'concluida') {
            totalConcluido += valor;
        }
    });
    
    document.getElementById('totalPendente').textContent = `R$ ${totalPendente.toFixed(2)}`;
    document.getElementById('totalPago').textContent = `R$ ${totalConcluido.toFixed(2)}`;
    document.getElementById('totalVendas').textContent = totalVendas;
    document.getElementById('mediaVenda').textContent = totalVendas > 0 ? `R$ ${(somaTotal / totalVendas).toFixed(2)}` : 'R$ 0,00';
}

// Gerar badge de status
function getStatusBadge(status) {
    const badges = {
        'pendente': '<span class="badge bg-warning text-dark">Pendente</span>',
        'concluida': '<span class="badge bg-success">Concluída</span>',
        'cancelada': '<span class="badge bg-danger">Cancelada</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Desconhecido</span>';
}

// Abrir modal de pagamento
function abrirModalPagamento(vendaId) {
    // Buscar detalhes da venda
    fetch(`../src/Controllers/actions.php?action=obterDetalhesVenda&venda_id=${vendaId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            vendaAtual = data.data;
            
            document.getElementById('vendaIdPagamento').value = vendaId;
            document.getElementById('valorRecebido').value = parseFloat(vendaAtual.total).toFixed(2);
            
            // Mostrar detalhes da venda
            document.getElementById('detalhesVendaPagamento').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-person"></i> Cliente: ${vendaAtual.nome_cliente}</h6>
                        <p class="mb-1"><strong>Vendedor:</strong> ${vendaAtual.vendedor_nome}</p>
                        <p class="mb-1"><strong>Data:</strong> ${new Date(vendaAtual.data_venda).toLocaleString('pt-BR')}</p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-cash"></i> Total: <span class="text-success">R$ ${parseFloat(vendaAtual.total).toFixed(2)}</span></h6>
                        <p class="mb-1"><strong>Produtos:</strong> ${vendaAtual.produtos_info}</p>
                    </div>
                </div>
            `;
            
            // Reset form
            document.getElementById('formPagamento').reset();
            document.getElementById('vendaIdPagamento').value = vendaId;
            document.getElementById('valorRecebido').value = parseFloat(vendaAtual.total).toFixed(2);
            document.getElementById('campoTroco').style.display = 'none';
            
            // Mostrar modal
            new bootstrap.Modal(document.getElementById('modalPagamento')).show();
        } else {
            alert('Erro ao carregar detalhes da venda: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de conexão ao carregar detalhes da venda');
    });
}

// Mostrar/ocultar campo de troco
document.getElementById('metodoPagamento').addEventListener('change', function() {
    const campoTroco = document.getElementById('campoTroco');
    
    if (this.value === 'dinheiro') {
        campoTroco.style.display = 'block';
        document.getElementById('valorRecebido').readOnly = false;
    } else {
        campoTroco.style.display = 'none';
        document.getElementById('valorRecebido').readOnly = true;
        if (vendaAtual) {
            document.getElementById('valorRecebido').value = parseFloat(vendaAtual.total).toFixed(2);
        }
    }
});

// Calcular troco
document.getElementById('valorRecebido').addEventListener('input', function() {
    const metodoPagamento = document.getElementById('metodoPagamento').value;
    
    if (metodoPagamento === 'dinheiro' && vendaAtual) {
        const valorRecebido = parseFloat(this.value) || 0;
        const totalVenda = parseFloat(vendaAtual.total);
        const troco = valorRecebido - totalVenda;
        
        document.getElementById('trocoCalculado').value = troco >= 0 ? troco.toFixed(2) : '0.00';
    }
});

// Confirmar pagamento
function confirmarPagamento() {
    const form = document.getElementById('formPagamento');
    const formData = new FormData();
    
    formData.append('action', 'processarPagamento');
    formData.append('venda_id', document.getElementById('vendaIdPagamento').value);
    formData.append('metodo_pagamento', document.getElementById('metodoPagamento').value);
    formData.append('valor_recebido', document.getElementById('valorRecebido').value);
    formData.append('observacoes', document.getElementById('observacoesPagamento').value);
    
    if (document.getElementById('metodoPagamento').value === 'dinheiro') {
        formData.append('troco', document.getElementById('trocoCalculado').value);
    }
    
    // Validações
    if (!document.getElementById('metodoPagamento').value) {
        alert('Selecione o método de pagamento');
        return;
    }
    
    if (!document.getElementById('valorRecebido').value || parseFloat(document.getElementById('valorRecebido').value) <= 0) {
        alert('Valor recebido deve ser maior que zero');
        return;
    }
    
    if (document.getElementById('metodoPagamento').value === 'dinheiro') {
        const valorRecebido = parseFloat(document.getElementById('valorRecebido').value);
        const totalVenda = parseFloat(vendaAtual.total);
        
        if (valorRecebido < totalVenda) {
            alert('Valor recebido não pode ser menor que o total da venda');
            return;
        }
    }
    
    // Enviar dados
    fetch('../src/Controllers/actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Pagamento processado com sucesso!\n\nVenda: #${data.data.venda_id}\nMétodo: ${data.data.metodo_pagamento}\nValor: R$ ${data.data.valor_total}`);
            
            // Fechar modal
            bootstrap.Modal.getInstance(document.getElementById('modalPagamento')).hide();
            
            // Recarregar vendas
            carregarVendasFinanceiro();
        } else {
            alert('Erro ao processar pagamento: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de conexão ao processar pagamento');
    });
}

// Visualizar detalhes (para vendas já pagas)
function visualizarDetalhes(vendaId) {
    fetch(`../src/Controllers/actions.php?action=obterDetalhesVenda&venda_id=${vendaId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const venda = data.data;
            const dataFormatada = new Date(venda.data_venda).toLocaleString('pt-BR');
            
            alert(`Detalhes da Venda #${venda.id}\n\nCliente: ${venda.nome_cliente}\nVendedor: ${venda.vendedor_nome}\nData: ${dataFormatada}\nTotal: R$ ${parseFloat(venda.total).toFixed(2)}\nStatus: ${venda.status_pagamento}\nMétodo: ${venda.metodo_pagamento || 'Não informado'}\nProdutos: ${venda.produtos_info}`);
        } else {
            alert('Erro ao carregar detalhes: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de conexão');
    });
}
</script>