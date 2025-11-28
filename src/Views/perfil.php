<?php
// src/Views/perfil.php - Página de perfil do usuário
?>

<div class="container-fluid">
    <div class="row">
        <!-- Informações do Perfil -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="bi bi-person-circle"></i> Meu Perfil</h5>
                    <button class="btn btn-outline-primary btn-sm" id="btnEditarPerfil">
                        <i class="bi bi-pencil-square"></i> Editar
                    </button>
                </div>
                <div class="card-body">
                    <!-- Formulário de Perfil -->
                    <form id="formPerfil">
                        <div class="row">
                            <!-- Avatar e Informações Básicas -->
                            <div class="col-md-4 text-center">
                                <div class="mb-3">
                                    <div class="position-relative d-inline-block">
                                        <img id="userAvatarLarge" 
                                             src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxjaXJjbGUgY3g9IjYwIiBjeT0iNjAiIHI9IjYwIiBmaWxsPSIjMDA2NkNDIi8+CjxzdmcgeD0iMjQiIHk9IjI0IiB3aWR0aD0iNzIiIGhlaWdodD0iNzIiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMmMwIDAgMy0zIDMtNS41UzE1IDMgMTIgM3MtMyAxLjUtMyAzLjUgMyA1LjUgMyA1LjV6IiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRjLTQuNSAwLTguMiAyLjMtOC4yIDUuMiAwIDEuMSA0LjcgMS44IDguMiAxLjhzOC4yLS43IDguMi0xLjhjMC0yLjktMy43LTUuMi04LjItNS4yeiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cjwvc3ZnPg==" 
                                             alt="Avatar" 
                                             class="rounded-circle border border-3 border-primary"
                                             style="width: 120px; height: 120px; object-fit: cover;">
                                        <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" 
                                                id="btnAlterarFoto" title="Alterar foto">
                                            <i class="bi bi-camera"></i>
                                        </button>
                                    </div>
                                    <input type="file" id="inputFoto" accept="image/*" style="display: none;">
                                </div>
                                
                                <!-- Status Online -->
                                <div class="mb-3">
                                    <span class="badge bg-success">
                                        <i class="bi bi-circle-fill text-light"></i> Online
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Campos do Perfil -->
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="inputNome" class="form-label">
                                            <i class="bi bi-person"></i> Nome Completo
                                        </label>
                                        <input type="text" class="form-control" id="inputNome" disabled>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="inputEmail" class="form-label">
                                            <i class="bi bi-envelope"></i> Email
                                        </label>
                                        <input type="email" class="form-control" id="inputEmail" disabled>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="inputTelefone" class="form-label">
                                            <i class="bi bi-telephone"></i> Telefone
                                        </label>
                                        <input type="tel" class="form-control" id="inputTelefone" disabled>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="inputCpf" class="form-label">
                                            <i class="bi bi-card-text"></i> CPF
                                        </label>
                                        <input type="text" class="form-control" id="inputCpf" disabled>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="inputEndereco" class="form-label">
                                        <i class="bi bi-geo-alt"></i> Endereço
                                    </label>
                                    <input type="text" class="form-control" id="inputEndereco" disabled>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="inputCidade" class="form-label">
                                            <i class="bi bi-building"></i> Cidade
                                        </label>
                                        <input type="text" class="form-control" id="inputCidade" disabled>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="inputEstado" class="form-label">
                                            <i class="bi bi-map"></i> Estado
                                        </label>
                                        <select class="form-control" id="inputEstado" disabled>
                                            <option value="">Selecione...</option>
                                            <option value="SP">São Paulo</option>
                                            <option value="RJ">Rio de Janeiro</option>
                                            <option value="MG">Minas Gerais</option>
                                            <!-- Adicione outros estados conforme necessário -->
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="inputCep" class="form-label">
                                            <i class="bi bi-mailbox"></i> CEP
                                        </label>
                                        <input type="text" class="form-control" id="inputCep" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botões de Ação -->
                        <div class="row mt-3" id="botoesEdicao" style="display: none;">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-secondary me-2" id="btnCancelar">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar com Informações Adicionais -->
        <div class="col-md-4">
            <!-- Estatísticas do Usuário -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6><i class="bi bi-graph-up"></i> Estatísticas</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-1" id="totalVendas">0</h4>
                                <small class="text-muted">Vendas Hoje</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1" id="totalFaturamento">R$ 0,00</h4>
                            <small class="text-muted">Faturamento</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h6 class="text-info mb-1" id="produtosCadastrados">0</h6>
                                <small class="text-muted">Produtos</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="text-warning mb-1" id="pontosSalvos">0</h6>
                            <small class="text-muted">Localizações</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Código do Administrador -->
            <div class="card mb-3" id="cardCodigoAdmin" style="display: none;">
                <div class="card-header bg-primary text-white">
                    <h6><i class="bi bi-key"></i> Seu Código Único</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <h2 class="text-primary mb-2" id="codigoUnicoDisplay">------</h2>
                        <p class="text-muted mb-3">
                            <small>Este é seu código único para gerar códigos de funcionários</small>
                        </p>
                        <button class="btn btn-outline-primary btn-sm" onclick="copiarCodigoUnico()">
                            <i class="bi bi-clipboard"></i> Copiar Código
                        </button>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i>
                        <small>
                            <strong>Como usar:</strong><br>
                            Vá na aba "Funcionários" para gerar códigos para seus funcionarios
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Informações de Funcionário -->
            <div class="card mb-3" id="cardInfoFuncionario" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h6><i class="bi bi-person-badge"></i> Informações do Funcionário</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Função:</strong>
                        <span class="badge bg-primary ms-2" id="funcaoFuncionario">-</span>
                    </div>
                    <div class="mb-2">
                        <strong>Administrador Responsável:</strong>
                        <div id="adminResponsavel" class="text-muted">Carregando...</div>
                    </div>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        <small>
                            <strong>Lembre-se:</strong><br>
                            Suas permissões foram definidas pelo administrador que forneceu seu código de acesso.
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Atividade Recente -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6><i class="bi bi-clock-history"></i> Atividade Recente</h6>
                </div>
                <div class="card-body">
                    <div id="atividadeRecente">
                        <div class="d-flex mb-2">
                            <div class="flex-shrink-0">
                                <span class="badge bg-success rounded-pill">V</span>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <small class="text-muted">Hoje às 14:30</small><br>
                                <small>Nova venda registrada</small>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-2">
                            <div class="flex-shrink-0">
                                <span class="badge bg-info rounded-pill">P</span>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <small class="text-muted">Hoje às 12:15</small><br>
                                <small>Produto adicionado</small>
                            </div>
                        </div>
                        
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <span class="badge bg-primary rounded-pill">L</span>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <small class="text-muted">Hoje às 09:00</small><br>
                                <small>Login realizado</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Configurações Rápidas -->
            <div class="card">
                <div class="card-header">
                    <h6><i class="bi bi-gear"></i> Configurações</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="notificacoesPush" checked>
                        <label class="form-check-label" for="notificacoesPush">
                            Notificações Push
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="alertasEstoque" checked>
                        <label class="form-check-label" for="alertasEstoque">
                            Alertas de Estoque
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="modoEscuro">
                        <label class="form-check-label" for="modoEscuro">
                            Modo Escuro
                        </label>
                    </div>
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-warning btn-sm" id="btnAlterarSenha">
                            <i class="bi bi-key"></i> Alterar Senha
                        </button>
                        <button class="btn btn-outline-danger btn-sm" id="btnExcluirConta">
                            <i class="bi bi-trash"></i> Excluir Conta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Alterar Senha -->
<div class="modal fade" id="modalAlterarSenha" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-key"></i> Alterar Senha</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAlterarSenha">
                    <div class="mb-3">
                        <label for="senhaAtual" class="form-label">Senha Atual</label>
                        <input type="password" class="form-control" id="senhaAtual" required>
                    </div>
                    <div class="mb-3">
                        <label for="novaSenha" class="form-label">Nova Senha</label>
                        <input type="password" class="form-control" id="novaSenha" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmarSenha" class="form-label">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="confirmarSenha" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarSenha">
                    <i class="bi bi-check-circle"></i> Alterar Senha
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variáveis globais do perfil
let modoEdicao = false;
let dadosOriginais = {};

// Inicializar página de perfil
document.addEventListener('DOMContentLoaded', function() {
    carregarDadosPerfil();
    carregarEstatisticas();
    configurarEventos();
});

// Carregar dados do perfil
function carregarDadosPerfil() {
    const user = JSON.parse(sessionStorage.getItem('user') || '{}');
    
    // Preencher campos com dados do usuário
    document.getElementById('inputNome').value = user.name || '';
    document.getElementById('inputEmail').value = user.email || '';
    document.getElementById('inputTelefone').value = user.telefone || '';
    document.getElementById('inputCpf').value = user.cpf || '';
    document.getElementById('inputEndereco').value = user.endereco || '';
    document.getElementById('inputCidade').value = user.cidade || '';
    document.getElementById('inputEstado').value = user.estado || '';
    document.getElementById('inputCep').value = user.cep || '';
    
    // Atualizar avatar
    const defaultAvatar = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxjaXJjbGUgY3g9IjYwIiBjeT0iNjAiIHI9IjYwIiBmaWxsPSIjMDA2NkNDIi8+CjxzdmcgeD0iMjQiIHk9IjI0IiB3aWR0aD0iNzIiIGhlaWdodD0iNzIiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMmMwIDAgMy0zIDMtNS41UzE1IDMgMTIgM3MtMyAxLjUtMyAzLjUgMyA1LjUgMyA1LjV6IiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRjLTQuNSAwLTguMiAyLjMtOC4yIDUuMiAwIDEuMSA0LjcgMS44IDguMiAxLjhzOC4yLS43IDguMi0xLjhjMC0yLjktMy43LTUuMi04LjItNS4yeiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cjwvc3ZnPg==";
    document.getElementById('userAvatarLarge').src = user.imageUrl || defaultAvatar;
    
    // Mostrar informações específicas baseadas no tipo de usuário
    mostrarInformacoesTipoUsuario(user);
    
    // Salvar dados originais
    dadosOriginais = { ...user };
}

// Mostrar informações específicas do tipo de usuário
function mostrarInformacoesTipoUsuario(user) {
    const cardCodigoAdmin = document.getElementById('cardCodigoAdmin');
    const cardInfoFuncionario = document.getElementById('cardInfoFuncionario');
    
    // Verificar tipo de usuário
    if (user.tipo === 'administrador' || user.tipo_usuario === 'administrador' || !user.tipo) {
        // É administrador - mostrar código único
        cardCodigoAdmin.style.display = 'block';
        cardInfoFuncionario.style.display = 'none';
        
        // Exibir código único
        const codigoUnico = user.codigo_unico || '------';
        document.getElementById('codigoUnicoDisplay').textContent = codigoUnico;
        
        console.log('👑 Usuário administrador - Código:', codigoUnico);
        
    } else if (user.tipo === 'funcionario' || user.tipo_usuario === 'funcionario') {
        // É funcionário - mostrar informações do funcionário
        cardCodigoAdmin.style.display = 'none';
        cardInfoFuncionario.style.display = 'block';
        
        // Exibir função
        const funcaoTexto = {
            'anotar_pedido': 'Anotar Pedidos',
            'fazer_pedido': 'Fazer Pedidos',
            'ambos': 'Anotar e Fazer Pedidos'
        };
        
        const funcao = user.funcao || user.funcao_funcionario || 'Não definida';
        document.getElementById('funcaoFuncionario').textContent = funcaoTexto[funcao] || funcao;
        
        // Buscar informações do administrador responsável
        if (user.admin_id) {
            buscarDadosAdministrador(user.admin_id);
        } else {
            document.getElementById('adminResponsavel').textContent = 'Não encontrado';
        }
        
        console.log('👤 Usuário funcionário - Função:', funcao);
    }
}

// Buscar dados do administrador responsável
function buscarDadosAdministrador(adminId) {
    // Em um sistema real, isso seria uma consulta à API
    // Por ora, vamos simular
    document.getElementById('adminResponsavel').innerHTML = 
        '<i class="bi bi-person-check text-primary"></i> Administrador (ID: ' + adminId + ')';
}

// Função para copiar o código único
function copiarCodigoUnico() {
    const codigo = document.getElementById('codigoUnicoDisplay').textContent;
    
    if (codigo && codigo !== '------') {
        navigator.clipboard.writeText(codigo).then(() => {
            // Feedback visual
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copiado!';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
            
            console.log('📋 Código copiado:', codigo);
        }).catch(err => {
            console.error('Erro ao copiar:', err);
            alert('Código: ' + codigo);
        });
    }
}

// Carregar estatísticas do usuário
function carregarEstatisticas() {
    fetch('../src/Controllers/actions.php?action=estatisticasPerfil', {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('totalVendas').textContent = data.data.vendas_hoje;
            document.getElementById('totalFaturamento').textContent = 'R$ ' + data.data.faturamento_hoje;
            document.getElementById('produtosCadastrados').textContent = data.data.produtos_cadastrados;
        }
    })
    .catch(error => {
        console.error('Erro ao carregar estatísticas:', error);
    });
    
    // Contar pontos salvos na localização
    const pontosSalvos = JSON.parse(localStorage.getItem('pontos_venda') || '[]');
    document.getElementById('pontosSalvos').textContent = pontosSalvos.length;
    
    // Carregar atividade recente
    carregarAtividadeRecente();
}

// Carregar atividade recente
function carregarAtividadeRecente() {
    fetch('../src/Controllers/actions.php?action=atividadeRecente', {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.length > 0) {
            const container = document.getElementById('atividadeRecente');
            container.innerHTML = '';
            
            data.data.forEach(atividade => {
                const dataAtividade = new Date(atividade.data);
                const hora = dataAtividade.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                const dataFormatada = dataAtividade.toLocaleDateString('pt-BR');
                
                const div = document.createElement('div');
                div.className = 'd-flex mb-2';
                div.innerHTML = `
                    <div class="flex-shrink-0">
                        <span class="badge bg-success rounded-pill">V</span>
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <small class="text-muted">${dataFormatada} às ${hora}</small><br>
                        <small>Venda de R$ ${parseFloat(atividade.total).toFixed(2)}</small>
                    </div>
                `;
                container.appendChild(div);
            });
        }
    })
    .catch(error => {
        console.error('Erro ao carregar atividade:', error);
    });
}

// Configurar eventos
function configurarEventos() {
    // Botão editar perfil
    document.getElementById('btnEditarPerfil').addEventListener('click', toggleModoEdicao);
    
    // Botão cancelar
    document.getElementById('btnCancelar').addEventListener('click', cancelarEdicao);
    
    // Formulário de perfil
    document.getElementById('formPerfil').addEventListener('submit', salvarPerfil);
    
    // Alterar foto
    document.getElementById('btnAlterarFoto').addEventListener('click', function() {
        document.getElementById('inputFoto').click();
    });
    
    document.getElementById('inputFoto').addEventListener('change', alterarFoto);
    
    // Botão alterar senha
    document.getElementById('btnAlterarSenha').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalAlterarSenha'));
        modal.show();
    });
    
    // Confirmar alteração de senha
    document.getElementById('btnConfirmarSenha').addEventListener('click', alterarSenha);
    
    // Botão excluir conta
    document.getElementById('btnExcluirConta').addEventListener('click', confirmarExclusaoConta);
    
    // Configurações switches
    document.getElementById('notificacoesPush').addEventListener('change', salvarConfiguracao);
    document.getElementById('alertasEstoque').addEventListener('change', salvarConfiguracao);
    document.getElementById('modoEscuro').addEventListener('change', toggleModoEscuro);
}

// Toggle modo edição
function toggleModoEdicao() {
    modoEdicao = !modoEdicao;
    
    const campos = ['inputNome', 'inputEmail', 'inputTelefone', 'inputCpf', 'inputEndereco', 'inputCidade', 'inputEstado', 'inputCep'];
    const btnEditar = document.getElementById('btnEditarPerfil');
    const botoesEdicao = document.getElementById('botoesEdicao');
    
    campos.forEach(campoId => {
        document.getElementById(campoId).disabled = !modoEdicao;
    });
    
    if (modoEdicao) {
        btnEditar.innerHTML = '<i class="bi bi-x-circle"></i> Cancelar';
        btnEditar.className = 'btn btn-outline-secondary btn-sm';
        botoesEdicao.style.display = 'block';
    } else {
        btnEditar.innerHTML = '<i class="bi bi-pencil-square"></i> Editar';
        btnEditar.className = 'btn btn-outline-primary btn-sm';
        botoesEdicao.style.display = 'none';
    }
}

// Cancelar edição
function cancelarEdicao() {
    // Restaurar dados originais
    carregarDadosPerfil();
    toggleModoEdicao();
}

// Salvar perfil
function salvarPerfil(event) {
    event.preventDefault();
    
    const dadosAtualizados = {
        name: document.getElementById('inputNome').value,
        email: document.getElementById('inputEmail').value,
        telefone: document.getElementById('inputTelefone').value,
        cpf: document.getElementById('inputCpf').value,
        endereco: document.getElementById('inputEndereco').value,
        cidade: document.getElementById('inputCidade').value,
        estado: document.getElementById('inputEstado').value,
        cep: document.getElementById('inputCep').value
    };
    
    // Manter dados existentes
    const user = JSON.parse(sessionStorage.getItem('user') || '{}');
    const userAtualizado = { ...user, ...dadosAtualizados };
    
    // Salvar no sessionStorage
    sessionStorage.setItem('user', JSON.stringify(userAtualizado));
    
    // Atualizar header
    document.getElementById('headerUserName').textContent = userAtualizado.name;
    document.getElementById('headerUserEmail').textContent = userAtualizado.email;
    
    toggleModoEdicao();
    
    // Mostrar mensagem de sucesso
    if (typeof mostrarAlerta === 'function') {
        mostrarAlerta('✅ Perfil atualizado com sucesso!', 'success');
    } else {
        alert('Perfil atualizado com sucesso!');
    }
}

// Alterar foto
function alterarFoto(event) {
    const file = event.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imageUrl = e.target.result;
            
            // Atualizar avatares
            document.getElementById('userAvatarLarge').src = imageUrl;
            document.getElementById('headerUserAvatar').src = imageUrl;
            
            // Salvar no perfil
            const user = JSON.parse(sessionStorage.getItem('user') || '{}');
            user.imageUrl = imageUrl;
            sessionStorage.setItem('user', JSON.stringify(user));
            
            if (typeof mostrarAlerta === 'function') {
                mostrarAlerta('📷 Foto atualizada com sucesso!', 'success');
            }
        };
        reader.readAsDataURL(file);
    }
}

// Alterar senha
function alterarSenha() {
    const senhaAtual = document.getElementById('senhaAtual').value;
    const novaSenha = document.getElementById('novaSenha').value;
    const confirmarSenha = document.getElementById('confirmarSenha').value;
    
    if (!senhaAtual || !novaSenha || !confirmarSenha) {
        alert('Preencha todos os campos!');
        return;
    }
    
    if (novaSenha !== confirmarSenha) {
        alert('As senhas não coincidem!');
        return;
    }
    
    if (novaSenha.length < 6) {
        alert('A nova senha deve ter pelo menos 6 caracteres!');
        return;
    }
    
    // Enviar para API
    const formData = new FormData();
    formData.append('action', 'alterarSenha');
    formData.append('senha_atual', senhaAtual);
    formData.append('nova_senha', novaSenha);
    
    fetch('../src/Controllers/actions.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAlterarSenha'));
            modal.hide();
            
            // Limpar formulário
            document.getElementById('formAlterarSenha').reset();
            
            alert('🔐 Senha alterada com sucesso!');
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao alterar senha');
    });
}

// Confirmar exclusão da conta
function confirmarExclusaoConta() {
    const senha = prompt('⚠️ ATENÇÃO: Esta ação é irreversível!\n\nDigite sua senha para confirmar a exclusão da conta:');
    
    if (!senha) {
        return;
    }
    
    if (confirm('Tem certeza ABSOLUTA que deseja excluir sua conta?\nTodos os seus dados serão perdidos permanentemente.')) {
        // Enviar para API
        const formData = new FormData();
        formData.append('action', 'excluirConta');
        formData.append('senha', senha);
        
        fetch('../src/Controllers/actions.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                sessionStorage.clear();
                localStorage.clear();
                
                alert('Conta excluída com sucesso. Você será redirecionado para a página de login.');
                window.location.href = 'login.php';
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir conta');
        });
    }
}

// Salvar configuração
function salvarConfiguracao() {
    const configs = {
        notificacoesPush: document.getElementById('notificacoesPush').checked,
        alertasEstoque: document.getElementById('alertasEstoque').checked,
        modoEscuro: document.getElementById('modoEscuro').checked
    };
    
    localStorage.setItem('configuracoes', JSON.stringify(configs));
    
    if (typeof mostrarAlerta === 'function') {
        mostrarAlerta('⚙️ Configuração salva!', 'info', 2000);
    }
}

// Toggle modo escuro
function toggleModoEscuro() {
    const modoEscuro = document.getElementById('modoEscuro').checked;
    
    if (modoEscuro) {
        document.body.classList.add('dark-mode');
        if (typeof mostrarAlerta === 'function') {
            mostrarAlerta('🌙 Modo escuro ativado em todas as páginas!', 'info', 3000);
        }
    } else {
        document.body.classList.remove('dark-mode');
        if (typeof mostrarAlerta === 'function') {
            mostrarAlerta('☀️ Modo claro ativado em todas as páginas!', 'info', 3000);
        }
    }
    
    salvarConfiguracao();
    
    // Notificar que o modo foi alterado globalmente
    console.log('Modo escuro alterado para:', modoEscuro ? 'ativado' : 'desativado');
}

// Carregar configurações salvas
function carregarConfiguracoes() {
    const configs = JSON.parse(localStorage.getItem('configuracoes') || '{}');
    
    if (configs.notificacoesPush !== undefined) {
        document.getElementById('notificacoesPush').checked = configs.notificacoesPush;
    }
    
    if (configs.alertasEstoque !== undefined) {
        document.getElementById('alertasEstoque').checked = configs.alertasEstoque;
    }
    
    if (configs.modoEscuro !== undefined) {
        document.getElementById('modoEscuro').checked = configs.modoEscuro;
        if (configs.modoEscuro) {
            document.body.classList.add('dark-mode');
        }
    }
}

// Carregar configurações ao inicializar
carregarConfiguracoes();
</script>

<style>
/* Estilos específicos da página de perfil */
.card {
    border: none;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.card-header {
    background: linear-gradient(135deg, #0066cc, #0099ff);
    color: white;
    border-radius: 10px 10px 0 0 !important;
    border: none;
}

.card-header h5, .card-header h6 {
    margin: 0;
    font-weight: 600;
}

.form-control:disabled {
    background-color: #f8f9fa;
    border-color: #e9ecef;
}

.form-control:focus {
    border-color: #0066cc;
    box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #0066cc, #0099ff);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0052a3, #007acc);
}

.badge {
    font-size: 0.75em;
}

.border-end {
    border-right: 1px solid #dee2e6;
}

/* Modo escuro para perfil - usando as variáveis globais */
.dark-mode {
    background-color: var(--bg-primary) !important;
    color: var(--text-primary) !important;
}

.dark-mode .card {
    background-color: var(--bg-white) !important;
    border-color: var(--border-color) !important;
    color: var(--text-primary) !important;
}

.dark-mode .card-body {
    background-color: transparent !important;
    color: var(--text-primary) !important;
}

.dark-mode .form-control {
    background-color: var(--bg-primary) !important;
    border-color: var(--border-color) !important;
    color: var(--text-primary) !important;
}

.dark-mode .form-control:disabled {
    background-color: var(--bg-light) !important;
    border-color: var(--border-color) !important;
    color: var(--text-secondary) !important;
}

.dark-mode .form-control:focus {
    border-color: var(--primary-color) !important;
    box-shadow: 0 0 0 0.2rem rgba(88, 166, 255, 0.25) !important;
    background-color: var(--bg-primary) !important;
}

.dark-mode .text-muted {
    color: var(--text-muted) !important;
}

.dark-mode .modal-content {
    background-color: var(--bg-white) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}

.dark-mode .modal-header {
    background: linear-gradient(135deg, var(--primary-color), #4169e1) !important;
    border-bottom-color: var(--border-color) !important;
}

.dark-mode .modal-footer {
    border-top-color: var(--border-color) !important;
}

.dark-mode .border-end {
    border-right-color: var(--border-color) !important;
}

.dark-mode .form-check-input {
    background-color: var(--bg-light) !important;
    border-color: var(--border-color) !important;
}

.dark-mode .form-check-input:checked {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
}

.dark-mode .form-check-label {
    color: var(--text-primary) !important;
}

/* Corrigir elementos específicos */
.dark-mode .container-fluid,
.dark-mode .row,
.dark-mode [class*="col-"] {
    background-color: transparent !important;
}

.dark-mode hr {
    border-color: var(--border-color) !important;
    opacity: 0.3;
}

/* Animações */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(0, 102, 204, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(0, 102, 204, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(0, 102, 204, 0);
    }
}

.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-info:hover,
.btn-outline-warning:hover,
.btn-outline-danger:hover {
    transform: translateY(-2px);
    transition: all 0.2s ease;
}
</style>