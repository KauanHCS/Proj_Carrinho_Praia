<!-- Gerenciamento de Funcionários -->
<div class="content">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-people"></i>
                        Gerenciamento de Funcionários
                    </h5>
                </div>
                <div class="card-body">
                    
                    <!-- Seção: Gerar Códigos -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="bi bi-key"></i> Gerar Novo Código</h6>
                                </div>
                                <div class="card-body">
                                    <form id="formGerarCodigo">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Sistema Reutilizável:</strong> Este código pode ser usado por múltiplos funcionários. Você definirá as funções depois.
                        </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Gerar Código Reutilizável
                                    </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Como Funciona</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success"></i> <strong>Anotar Pedidos:</strong> Pode apenas registrar pedidos dos clientes</li>
                                        <li><i class="bi bi-check-circle text-success"></i> <strong>Fazer Pedidos:</strong> Pode processar e finalizar vendas</li>
                                        <li><i class="bi bi-check-circle text-success"></i> <strong>Ambos:</strong> Pode anotar e finalizar pedidos</li>
                                        <li><i class="bi bi-cash text-warning"></i> <strong>Financeiro:</strong> Cuida apenas dos pagamentos (dinheiro, cartão, pix)</li>
                                        <li><i class="bi bi-cash-stack text-info"></i> <strong>Financeiro + Anotar:</strong> Anota pedidos e cuida dos pagamentos</li>
                                    </ul>
                                    <hr>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-lightbulb"></i>
                                        Cada código pode ser usado por múltiplas pessoas. Você define as funções de cada funcionário individualmente.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção: Códigos Gerados -->
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col">
                                    <h6 class="mb-0"><i class="bi bi-list-ul"></i> Códigos Gerados</h6>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-sm btn-outline-primary" onclick="carregarCodigosFuncionarios()">
                                        <i class="bi bi-arrow-clockwise"></i> Atualizar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="loadingCodigos" class="text-center" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped" id="tabelaCodigos">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Funcionários Cadastrados</th>
                                <th>Data Criação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="corpoTabelaCodigos">
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <i class="bi bi-inbox"></i> Nenhum código gerado ainda
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

<script>
// Função para gerar novo código
document.getElementById('formGerarCodigo').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'gerarCodigoFuncionario');
    
    fetch('../src/Controllers/actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Código reutilizável gerado com sucesso!\n\nCódigo: ${data.data.codigo}\n\nCompartilhe este código com quantos funcionários quiser. Você definirá as funções deles depois.`);
            carregarCodigosFuncionarios();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de conexão: ' + error);
    });
});

// Função para carregar códigos existentes
function carregarCodigosFuncionarios() {
    const loading = document.getElementById('loadingCodigos');
    const tabela = document.getElementById('corpoTabelaCodigos');
    
    loading.style.display = 'block';
    
    fetch('../src/Controllers/actions.php?action=listarCodigosFuncionarios')
    .then(response => response.json())
    .then(data => {
        loading.style.display = 'none';
        
        if (data.success) {
            if (data.data.length === 0) {
                tabela.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            <i class="bi bi-inbox"></i> Nenhum código gerado ainda
                        </td>
                    </tr>
                `;
            } else {
                tabela.innerHTML = '';
                data.data.forEach(codigo => {
                    const row = document.createElement('tr');
                    
                    // Processar funcionários
                    let funcionariosHtml = '';
                    if (codigo.funcionarios && codigo.funcionarios.length > 0) {
                        funcionariosHtml = codigo.funcionarios.map(func => {
                            const funcaoTexto = {
                                'anotar_pedido': 'Anota Pedidos',
                                'fazer_pedido': 'Faz Pedidos', 
                                'ambos': 'Anota/Faz Pedidos',
                                'financeiro': 'Financeiro',
                                'financeiro_e_anotar': 'Financeiro + Anotar',
                                '': 'Sem função definida'
                            };
                            
                            const funcaoAtual = func.funcao || '';
                            const selectId = `funcao_${func.id}`;
                            
                            return `
                                <div class="mb-2 p-2 border rounded">
                                    <strong>${func.nome}</strong><br>
                                    <small class="text-muted">${func.email}</small><br>
                                    <div class="mt-1">
                                        <select class="form-select form-select-sm" id="${selectId}" onchange="atualizarFuncaoFuncionario(${func.id}, this.value)">
                                            <option value="" ${funcaoAtual === '' ? 'selected' : ''}>Definir função...</option>
                                            <option value="anotar_pedido" ${funcaoAtual === 'anotar_pedido' ? 'selected' : ''}>Anotar Pedidos</option>
                                            <option value="fazer_pedido" ${funcaoAtual === 'fazer_pedido' ? 'selected' : ''}>Fazer Pedidos</option>
                                            <option value="ambos" ${funcaoAtual === 'ambos' ? 'selected' : ''}>Ambos</option>
                                            <option value="financeiro" ${funcaoAtual === 'financeiro' ? 'selected' : ''}>Financeiro</option>
                                            <option value="financeiro_e_anotar" ${funcaoAtual === 'financeiro_e_anotar' ? 'selected' : ''}>Financeiro + Anotar</option>
                                        </select>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    } else {
                        funcionariosHtml = '<small class="text-muted"><i class="bi bi-person-x"></i> Nenhum funcionário cadastrado ainda</small>';
                    }
                    
                    row.innerHTML = `
                        <td><code>${codigo.codigo}</code></td>
                        <td>${funcionariosHtml}</td>
                        <td>${new Date(codigo.data_criacao).toLocaleString('pt-BR')}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="copiarCodigo('${codigo.codigo}')">
                                <i class="bi bi-clipboard"></i> Copiar
                            </button>
                        </td>
                    `;
                    
                    tabela.appendChild(row);
                });
            }
        } else {
            tabela.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-danger">
                        <i class="bi bi-exclamation-circle"></i> Erro ao carregar códigos: ${data.message}
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        loading.style.display = 'none';
        console.error('Erro:', error);
        tabela.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-danger">
                    <i class="bi bi-exclamation-circle"></i> Erro de conexão
                </td>
            </tr>
        `;
    });
}

// Função para atualizar função de funcionário
function atualizarFuncaoFuncionario(funcionarioId, novaFuncao) {
    if (!novaFuncao) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'atualizarFuncaoFuncionario');
    formData.append('funcionario_id', funcionarioId);
    formData.append('nova_funcao', novaFuncao);
    
    fetch('../src/Controllers/actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const funcaoTexto = {
                'anotar_pedido': 'Anotar Pedidos',
                'fazer_pedido': 'Fazer Pedidos',
                'ambos': 'Anotar e Fazer Pedidos',
                'financeiro': 'Financeiro',
                'financeiro_e_anotar': 'Financeiro e Anotar Pedidos'
            };
            alert(`Função atualizada com sucesso!\n\nFuncionário: ${data.data.funcionario_nome}\nNova função: ${funcaoTexto[novaFuncao]}`);
        } else {
            alert('Erro ao atualizar função: ' + data.message);
            // Recarregar para reverter a seleção
            carregarCodigosFuncionarios();
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de conexão: ' + error);
        // Recarregar para reverter a seleção
        carregarCodigosFuncionarios();
    });
}

// Função para copiar código
function copiarCodigo(codigo) {
    navigator.clipboard.writeText(codigo).then(() => {
        alert('Código copiado para a área de transferência!');
    }).catch(err => {
        console.error('Erro ao copiar:', err);
        // Fallback para navegadores mais antigos
        const input = document.createElement('input');
        input.value = codigo;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        alert('Código copiado para a área de transferência!');
    });
}

// Carregar códigos automaticamente quando a aba é exibida
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se esta aba está ativa
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const target = mutation.target;
                if (target.id === 'funcionarios' && target.classList.contains('active')) {
                    carregarCodigosFuncionarios();
                }
            }
        });
    });
    
    const funcionariosTab = document.getElementById('funcionarios');
    if (funcionariosTab) {
        observer.observe(funcionariosTab, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        // Se já estiver ativa, carregar imediatamente
        if (funcionariosTab.classList.contains('active')) {
            carregarCodigosFuncionarios();
        }
    }
});
</script>