/**
 * guardasois.js
 * Gerenciamento administrativo de guarda-sóis
 */

let todosGuardasoisAdmin = [];
let guardasolDetalheAtual = null;

// Carregar guarda-sóis ao abrir a aba
document.addEventListener('DOMContentLoaded', () => {
    // Verificar se estamos na aba de guarda-sóis
    const tabGuardasois = document.getElementById('guardasois');
    if (tabGuardasois && tabGuardasois.classList.contains('show')) {
        carregarGuardasoisAdmin();
    }
});

/**
 * Carregar lista de guarda-sóis
 */
async function carregarGuardasoisAdmin() {
    const grid = document.getElementById('gridGuardasoisAdmin');
    
    grid.innerHTML = `
        <div class="text-center py-5">
            <i class="bi bi-hourglass-split" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="text-muted mt-3">Carregando guarda-sóis...</p>
        </div>
    `;
    
    try {
        const response = await fetch('../src/Controllers/actions.php?action=listarGuardasois');
        const data = await response.json();
        
        if (data.success) {
            todosGuardasoisAdmin = data.data;
            
            if (data.data.length === 0) {
                grid.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-umbrella" style="font-size: 4rem; opacity: 0.3;"></i>
                        <p class="text-muted mt-3 mb-3">Nenhum guarda-sol cadastrado ainda</p>
                        <button class="btn btn-primary btn-lg" onclick="abrirModalConfigurarQuantidade()">
                            <i class="bi bi-gear-fill"></i>
                            Configurar Quantidade
                        </button>
                    </div>
                `;
            } else {
                renderizarGuardasoisAdmin(data.data);
                atualizarEstatisticas(data.data);
            }
        } else {
            grid.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <p class="text-danger mt-3">Erro ao carregar guarda-sóis</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro:', error);
        grid.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                <p class="text-danger mt-3">Erro de conexão</p>
            </div>
        `;
    }
}

/**
 * Renderizar grid de guarda-sóis
 */
function renderizarGuardasoisAdmin(guardasois) {
    const grid = document.getElementById('gridGuardasoisAdmin');
    
    if (guardasois.length === 0) {
        grid.innerHTML = `
            <div class="text-center py-5">
                <p class="text-muted">Nenhum guarda-sol encontrado com esse filtro</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    guardasois.forEach(gs => {
        const statusClass = `status-${gs.status}`;
        const statusBadgeClass = gs.status === 'vazio' ? 'badge-vazio' : 
                                  gs.status === 'ocupado' ? 'badge-ocupado' : 'badge-aguardando';
        const statusText = gs.status === 'vazio' ? 'Vazio' : 
                          gs.status === 'ocupado' ? 'Ocupado' : 'Aguardando Pag.';
        
        const icone = gs.status === 'vazio' ? '☀️' : gs.status === 'ocupado' ? '⌛' : '💵';
        
        html += `
            <div class="guardasol-admin-card ${statusClass}" onclick="abrirDetalhesGuardasol(${gs.id})">
                <div class="guardasol-icon-admin">${icone}</div>
                <div class="guardasol-numero-admin">#${gs.numero}</div>
                <div class="guardasol-status-badge-admin ${statusBadgeClass}">${statusText}</div>
                ${gs.cliente_nome ? `<div class="guardasol-cliente-admin"><i class="bi bi-person"></i> ${gs.cliente_nome}</div>` : ''}
                ${gs.total_consumido > 0 ? `<div class="guardasol-valor-admin">R$ ${parseFloat(gs.total_consumido).toFixed(2).replace('.', ',')}</div>` : ''}
            </div>
        `;
    });
    
    grid.innerHTML = html;
}

/**
 * Atualizar estatísticas
 */
function atualizarEstatisticas(guardasois) {
    const total = guardasois.length;
    const vazios = guardasois.filter(gs => gs.status === 'vazio').length;
    const ocupados = guardasois.filter(gs => gs.status === 'ocupado').length;
    const aguardando = guardasois.filter(gs => gs.status === 'aguardando_pagamento').length;
    
    document.getElementById('statTotal').textContent = total;
    document.getElementById('statVazios').textContent = vazios;
    document.getElementById('statOcupados').textContent = ocupados;
    document.getElementById('statAguardando').textContent = aguardando;
}

/**
 * Filtrar por status
 */
function filtrarStatusAdmin(status) {
    // Atualizar botões ativos
    document.querySelectorAll('.btn-group button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Filtrar
    if (status === 'todos') {
        renderizarGuardasoisAdmin(todosGuardasoisAdmin);
    } else {
        const filtrados = todosGuardasoisAdmin.filter(gs => gs.status === status);
        renderizarGuardasoisAdmin(filtrados);
    }
}

/**
 * Abrir modal de configurar quantidade
 */
async function abrirModalConfigurarQuantidade() {
    const modal = new bootstrap.Modal(document.getElementById('modalConfigurarQuantidade'));
    
    // Se já existem guarda-sóis, mostrar aviso e carregar quantidade atual
    if (todosGuardasoisAdmin.length > 0) {
        document.getElementById('avisoAlteracao').style.display = 'block';
        document.getElementById('quantidadeGuardasois').value = todosGuardasoisAdmin.length;
    } else {
        document.getElementById('avisoAlteracao').style.display = 'none';
        document.getElementById('quantidadeGuardasois').value = 10;
    }
    
    modal.show();
}

/**
 * Salvar quantidade de guarda-sóis
 */
async function salvarQuantidadeGuardasois() {
    const quantidade = parseInt(document.getElementById('quantidadeGuardasois').value);
    
    if (quantidade < 1 || quantidade > 100) {
        alert('A quantidade deve ser entre 1 e 100');
        return;
    }
    
    const confirmMsg = todosGuardasoisAdmin.length > 0 
        ? `Você possui ${todosGuardasoisAdmin.length} guarda-sóis cadastrados.\n\nDeseja alterar para ${quantidade} guarda-sóis?`
        : `Deseja cadastrar ${quantidade} guarda-sóis?`;
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    try {
        const quantidadeAtual = todosGuardasoisAdmin.length;
        
        if (quantidade > quantidadeAtual) {
            // Adicionar novos guarda-sóis
            const promises = [];
            for (let i = quantidadeAtual + 1; i <= quantidade; i++) {
                const formData = new FormData();
                formData.append('action', 'cadastrarGuardasol');
                formData.append('numero', i);
                
                promises.push(
                    fetch('../src/Controllers/actions.php', {
                        method: 'POST',
                        body: formData
                    })
                );
            }
            
            await Promise.all(promises);
            
        } else if (quantidade < quantidadeAtual) {
            // Remover guarda-sóis excedentes (remover os de maior número)
            const idsParaRemover = todosGuardasoisAdmin
                .sort((a, b) => b.numero - a.numero)
                .slice(0, quantidadeAtual - quantidade)
                .map(gs => gs.id);
            
            const promises = idsParaRemover.map(id => {
                const formData = new FormData();
                formData.append('action', 'removerGuardasol');
                formData.append('guardasol_id', id);
                
                return fetch('../src/Controllers/actions.php', {
                    method: 'POST',
                    body: formData
                });
            });
            
            await Promise.all(promises);
        }
        
        // Fechar modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfigurarQuantidade'));
        modal.hide();
        
        // Recarregar lista
        await carregarGuardasoisAdmin();
        
        mostrarAlertaSucesso(`Quantidade atualizada para ${quantidade} guarda-sóis!`);
        
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao salvar configuração');
    }
}

/**
 * Abrir detalhes de um guarda-sol
 */
async function abrirDetalhesGuardasol(guardasolId) {
    const guardasol = todosGuardasoisAdmin.find(gs => gs.id === guardasolId);
    
    if (!guardasol) return;
    
    guardasolDetalheAtual = guardasol;
    
    // Preencher informações
    document.getElementById('detalheNumero').textContent = `#${guardasol.numero}`;
    
    const statusText = guardasol.status === 'vazio' ? 'Vazio' : 
                      guardasol.status === 'ocupado' ? 'Ocupado' : 'Aguardando Pagamento';
    const statusColor = guardasol.status === 'vazio' ? 'text-success' : 
                       guardasol.status === 'ocupado' ? 'text-warning' : 'text-danger';
    
    document.getElementById('detalheStatus').innerHTML = `<span class="${statusColor}">${statusText}</span>`;
    document.getElementById('detalheTotal').textContent = `R$ ${parseFloat(guardasol.total_consumido || 0).toFixed(2).replace('.', ',')}`;
    
    // Cliente
    if (guardasol.cliente_nome) {
        document.getElementById('detalheClienteContainer').style.display = 'block';
        document.getElementById('detalheCliente').textContent = guardasol.cliente_nome;
    } else {
        document.getElementById('detalheClienteContainer').style.display = 'none';
    }
    
    // Botão fechar conta
    if (guardasol.status !== 'vazio') {
        document.getElementById('btnFecharContaModal').style.display = 'inline-block';
    } else {
        document.getElementById('btnFecharContaModal').style.display = 'none';
    }
    
    // Carregar comandas
    await carregarComandasGuardasol(guardasolId);
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalDetalhesGuardasol'));
    modal.show();
}

/**
 * Carregar comandas de um guarda-sol
 */
async function carregarComandasGuardasol(guardasolId) {
    const container = document.getElementById('listaComandasDetalhes');
    
    container.innerHTML = '<p class="text-muted">Carregando...</p>';
    
    try {
        const response = await fetch(`../src/Controllers/actions.php?action=obterComandasGuardasol&guardasol_id=${guardasolId}`);
        const data = await response.json();
        
        if (data.success && data.data && data.data.length > 0) {
            let html = '';
            
            data.data.forEach((comanda, index) => {
                const produtos = JSON.parse(comanda.produtos);
                
                html += `
                    <div class="card mb-2">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Comanda #${index + 1}</strong>
                                <span class="badge bg-primary">R$ ${parseFloat(comanda.subtotal).toFixed(2).replace('.', ',')}</span>
                            </div>
                            <small class="text-muted">${new Date(comanda.data_pedido).toLocaleString('pt-BR')}</small>
                            <hr class="my-2">
                            <ul class="mb-0" style="font-size: 0.9rem;">
                `;
                
                produtos.forEach(prod => {
                    html += `
                        <li>${prod.quantidade}x ${prod.nome} - R$ ${parseFloat(prod.subtotal).toFixed(2).replace('.', ',')}</li>
                    `;
                });
                
                html += `
                            </ul>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-muted">Nenhuma comanda aberta</p>';
        }
    } catch (error) {
        console.error('Erro:', error);
        container.innerHTML = '<p class="text-danger">Erro ao carregar comandas</p>';
    }
}

/**
 * Fechar conta do guarda-sol (do modal de detalhes)
 */
async function fecharContaGuardasolModal() {
    if (!guardasolDetalheAtual) return;
    
    if (!confirm(`Deseja fechar a conta do Guarda-sol #${guardasolDetalheAtual.numero}?\n\nTodas as comandas serão finalizadas e o guarda-sol ficará disponível.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'finalizarGuardasol');
    formData.append('guardasol_id', guardasolDetalheAtual.id);
    
    try {
        const response = await fetch('../src/Controllers/actions.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalDetalhesGuardasol'));
            modal.hide();
            
            // Recarregar lista
            await carregarGuardasoisAdmin();
            
            mostrarAlertaSucesso(`Conta do Guarda-sol #${guardasolDetalheAtual.numero} fechada com sucesso!`);
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro de conexão');
    }
}

/**
 * Mostrar alerta de sucesso
 */
function mostrarAlertaSucesso(mensagem) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '90px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    
    alertDiv.innerHTML = `
        <i class="bi bi-check-circle"></i> ${mensagem}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 4000);
}

// Exportar funções globais
window.carregarGuardasoisAdmin = carregarGuardasoisAdmin;
window.abrirModalConfigurarQuantidade = abrirModalConfigurarQuantidade;
window.salvarQuantidadeGuardasois = salvarQuantidadeGuardasois;
window.filtrarStatusAdmin = filtrarStatusAdmin;
window.abrirDetalhesGuardasol = abrirDetalhesGuardasol;
window.fecharContaGuardasolModal = fecharContaGuardasolModal;

console.log('🏖️ Guarda-sóis Admin JS carregado');
