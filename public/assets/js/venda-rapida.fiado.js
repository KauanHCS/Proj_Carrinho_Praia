// --- Funções para seleção/cadastro de cliente fiado ---
function abrirModalSelecionarClienteFiado() {
    const modalEl = document.getElementById('modalSelecionarClienteFiado');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
    carregarListaClientesFiado();
}

function carregarListaClientesFiado(query = '') {
    const container = document.getElementById('listaClientesFiadoVenda');
    container.innerHTML = '<div class="text-center py-4"><i class="bi bi-hourglass-split" style="font-size: 2rem;"></i><p class="text-muted">Carregando clientes...</p></div>';

    fetch(`../src/Controllers/actions.php?action=listarClientesFiado&query=${encodeURIComponent(query)}`)
        .then(r => r.json())
        .then(json => {
            if (!json.success) {
                container.innerHTML = '<p class="text-muted text-center">Erro ao carregar clientes</p>';
                return;
            }
            const clientes = json.data || [];
            if (clientes.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">Nenhum cliente encontrado</p>';
                return;
            }
            const html = clientes.map(c => {
                const nome = c.nome || c.nome_completo || c.nome_cliente || c.razao || '';
                const telefone = c.telefone || c.celular || '';
                return `
                    <div class="cliente-item d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <strong>${escapeHtml(nome)}</strong><br>
                            <small class="text-muted">${escapeHtml(telefone)}</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-primary" onclick="selecionarClienteFiado(${c.id}, ${JSON.stringify(nome)})">Selecionar</button>
                        </div>
                    </div>
                `;
            }).join('');
            container.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<p class="text-muted text-center">Erro ao carregar clientes</p>';
        });
}

function filtrarClientesFiadoVenda() {
    const q = document.getElementById('buscaClienteFiadoVenda').value.trim();
    carregarListaClientesFiado(q);
}

function selecionarClienteFiado(id, nome) {
    // Preencher campos na venda rápida
    document.getElementById('clienteIdVenda').value = id;
    document.getElementById('nomeClienteVenda').value = nome;

    // Fechar modal
    const modalEl = document.getElementById('modalSelecionarClienteFiado');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

    mostrarAlerta && mostrarAlerta('Cliente selecionado: ' + nome, 'success', 2000);
}

function abrirCadastroRapidoCliente() {
    const modal = new bootstrap.Modal(document.getElementById('modalCadastroRapidoCliente'));
    modal.show();
}

function salvarClienteRapido() {
    const nome = document.getElementById('rapidoNomeCliente').value.trim();
    const telefone = document.getElementById('rapidoTelefoneCliente').value.trim();
    const limite = document.getElementById('rapidoLimiteCredito').value || '0';

    if (!nome) {
        mostrarAlerta && mostrarAlerta('Preencha o nome do cliente', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'cadastrarClienteFiado');
    formData.append('nome', nome);
    formData.append('telefone', telefone);
    formData.append('limite_credito', limite);

    fetch('../src/Controllers/actions.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(json => {
            if (!json.success) {
                mostrarAlerta && mostrarAlerta('Erro ao cadastrar cliente: ' + (json.message || ''), 'danger');
                return;
            }
            const cliente = json.data || {};
            // preencher e fechar modal cadastro
            document.getElementById('clienteIdVenda').value = cliente.id || cliente.cliente_id || '';
            document.getElementById('nomeClienteVenda').value = cliente.nome || nome;

            const modal = bootstrap.Modal.getInstance(document.getElementById('modalCadastroRapidoCliente'));
            if (modal) modal.hide();

            // fechar seleção também se estava aberta
            const selModal = bootstrap.Modal.getInstance(document.getElementById('modalSelecionarClienteFiado'));
            if (selModal) selModal.hide();

            mostrarAlerta && mostrarAlerta('Cliente cadastrado e selecionado', 'success');
        })
        .catch(err => {
            console.error(err);
            mostrarAlerta && mostrarAlerta('Erro ao cadastrar cliente', 'danger');
        });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// --- Fim funções de cliente fiado ---