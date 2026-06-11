// UTILITY FUNCTIONS MODERNOS ES6+
const Utils = {
    // Debounce function
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Throttle function
    throttle: (func, limit) => {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },
    
    // Format currency using Intl API
    formatCurrency: (valor) => {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(valor);
    },
    
    // Format number
    formatNumber: (valor, decimais = 2) => {
        return new Intl.NumberFormat('pt-BR', {
            minimumFractionDigits: decimais,
            maximumFractionDigits: decimais
        }).format(valor);
    },
    
    // Sanitize HTML
    sanitizeHtml: (str) => {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },
    
    // Get current date formatted
    getCurrentDate: () => {
        return new Intl.DateTimeFormat('pt-BR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).format(new Date());
    },
    
    // Format date
    formatDate: (date) => {
        return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
    },
    
    // Deep clone object
    deepClone: (obj) => {
        return JSON.parse(JSON.stringify(obj));
    },
    
    // Generate unique ID
    generateId: () => {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }
};

// CONFIG object with ES6+ features
const CONFIG = {
    SEARCH_DEBOUNCE_DELAY: 300,
    ALERT_DURATION: 4000,
    STORAGE_KEY: 'carrinho_praia',
    API_ENDPOINT: '../src/Controllers/actions.php',
    CHART_COLORS: {
        primary: 'rgba(40, 167, 69, 0.7)',
        border: 'rgba(40, 167, 69, 1)',
        secondary: 'rgba(0, 102, 204, 0.7)'
    }
};

// FUNÇÕES GLOBAIS PARA TESTE E DEBUG
function testarGrafico() {
    console.log('🧪 TESTE MANUAL DO GRÁFICO INICIADO');
    console.log('Chart.js disponível:', typeof Chart !== 'undefined');
    console.log('Elemento canvas existe:', document.getElementById('graficoVendas') !== null);
    console.log('Função atualizarGraficoVendas existe:', typeof atualizarGraficoVendas === 'function');
    
    if (typeof atualizarGraficoVendas === 'function') {
        atualizarGraficoVendas();
    } else {
        console.error('❌ Função atualizarGraficoVendas não está definida');
    }
}

// Expor funções no window para uso global
Object.assign(window, { testarGrafico, Utils, CONFIG });

// Dados iniciais
let carrinho = [];

// Cart persistence functions
function salvarCarrinho() {
    try {
        localStorage.setItem('carrinho_praia', JSON.stringify(carrinho));
    } catch (error) {
        console.warn('Não foi possível salvar o carrinho no localStorage:', error);
    }
}

function carregarCarrinho() {
    try {
        const carrinhoSalvo = localStorage.getItem('carrinho_praia');
        if (carrinhoSalvo) {
            carrinho = JSON.parse(carrinhoSalvo);
            return true;
        }
    } catch (error) {
        console.warn('Erro ao carregar carrinho do localStorage:', error);
        localStorage.removeItem('carrinho_praia');
    }
    return false;
}

function limparCarrinho() {
    carrinho = [];
    localStorage.removeItem('carrinho_praia');
    atualizarCarrinho();
}

// Funções auxiliares (mantendo compatibilidade)
function formatarMoeda(valor) {
    // Usar Utils.formatCurrency para formatação moderna
    return Utils.formatNumber(valor, 2).replace('.', ',');
}

function getDataAtual() {
    // Usar Utils.getCurrentDate para formatação moderna
    return Utils.getCurrentDate();
}

// Carregar dados iniciais
function carregarDados() {
    try {
        const dataAtualEl = document.getElementById('dataAtual');
        if (dataAtualEl) {
            dataAtualEl.textContent = getDataAtual();
        }
        
        carregarCarrinho(); // Carregar carrinho salvo
        atualizarCarrinho();
        inicializarEventListeners(); // Inicializar event listeners
        verificarEstoqueBaixo();
        // atualizarGraficoVendas(); // Removido - só executa na aba de relatórios
        configurarDatasExportacao(); // Configurar datas padrão
    } catch (error) {
        console.warn('Erro ao carregar dados iniciais:', error);
    }
}

// Adicionar produto ao carrinho
function adicionarAoCarrinho(id, nome, preco, quantidade) {
    // Verificar se o produto está disponível
    if (quantidade <= 0) {
        mostrarAlerta('Produto sem estoque disponível!', 'danger');
        return;
    }

    const itemExistente = carrinho.find(item => item.id === id);
    
    if (itemExistente) {
        itemExistente.quantidade++;
    } else {
        carrinho.push({
            id: id,
            nome: nome,
            preco: preco,
            quantidade: 1
        });
    }
    
    atualizarCarrinho();
    salvarCarrinho(); // Salvar no localStorage
    mostrarAlerta(`${nome} adicionado ao carrinho!`, 'success');
}

// Atualizar carrinho
function atualizarCarrinho() {
    const container = document.getElementById('itensCarrinho');
    const totalCarrinho = document.getElementById('totalCarrinho');
    
    // Verificar se os elementos existem
    if (!container || !totalCarrinho) {
        return;
    }
    
    if (carrinho.length === 0) {
        container.innerHTML = '<p class="text-muted">Nenhum item adicionado</p>';
        totalCarrinho.textContent = '0,00';
        const divTroco = document.getElementById('divTroco');
        if (divTroco) {
            divTroco.style.display = 'none';
        }
        return;
    }
    
    container.innerHTML = '';
    let total = 0;
    
    carrinho.forEach(item => {
        const div = document.createElement('div');
        div.className = 'd-flex justify-content-between mb-2';
        div.innerHTML = `
            <span>${item.quantidade}x ${item.nome}</span>
            <span>R$ ${formatarMoeda(item.preco * item.quantidade)}</span>
            <button class="btn btn-sm btn-danger ms-2" onclick="removerDoCarrinho(${item.id})">
                <i class="bi bi-x"></i>
            </button>
        `;
        
        container.appendChild(div);
        total += item.preco * item.quantidade;
    });
    
    totalCarrinho.textContent = formatarMoeda(total);
    
    // Atualizar troco se necessário
    const formaPagamento = document.getElementById('formaPagamento');
    if (formaPagamento && formaPagamento.value === 'dinheiro') {
        calcularTroco();
    }
}

// Remover do carrinho
function removerDoCarrinho(id) {
    carrinho = carrinho.filter(item => item.id !== id);
    atualizarCarrinho();
    salvarCarrinho(); // Salvar no localStorage
}

// Calcular troco
function calcularTroco() {
    const valorPagoEl = document.getElementById('valorPago');
    const valorTrocoEl = document.getElementById('valorTroco');
    
    if (!valorPagoEl || !valorTrocoEl) {
        return;
    }
    
    const valorPago = parseFloat(valorPagoEl.value) || 0;
    const total = carrinho.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
    const troco = valorPago - total;
    
    valorTrocoEl.textContent = formatarMoeda(Math.max(0, troco));
}

// Função para inicializar event listeners
function inicializarEventListeners() {
    // Forma de pagamento
    const formaPagamentoEl = document.getElementById('formaPagamento');
    if (formaPagamentoEl) {
        formaPagamentoEl.addEventListener('change', function() {
            const divTroco = document.getElementById('divTroco');
            if (divTroco) {
                if (this.value === 'dinheiro') {
                    divTroco.style.display = 'block';
                } else {
                    divTroco.style.display = 'none';
                }
            }
        });
    }

    const valorPagoEl = document.getElementById('valorPago');
    if (valorPagoEl) {
        valorPagoEl.addEventListener('input', calcularTroco);
    }

    // Finalizar venda
    const finalizarVendaEl = document.getElementById('finalizarVenda');
    if (finalizarVendaEl) {
        finalizarVendaEl.addEventListener('click', function() {
            if (carrinho.length === 0) {
                mostrarAlerta('Adicione produtos ao carrinho primeiro!', 'warning');
                return;
            }

            const formaPagamentoEl = document.getElementById('formaPagamento');
            const formaPagamento = formaPagamentoEl ? formaPagamentoEl.value : '';
            
            if (formaPagamento === 'dinheiro') {
                const valorPagoEl = document.getElementById('valorPago');
                const valorPago = valorPagoEl ? parseFloat(valorPagoEl.value) || 0 : 0;
                const total = carrinho.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
                
                if (valorPago < total) {
                    mostrarAlerta('Valor pago insuficiente! Faltam R$ ' + formatarMoeda(total - valorPago), 'danger');
                    return;
                }
            }

            const botaoFinalizar = document.getElementById('finalizarVenda');
            mostrarCarregamento(botaoFinalizar, 'Finalizando...');

            // Obter informações do cliente e checkbox de criar pedido
            const nomeClienteEl = document.getElementById('nomeCliente');
            const telefoneClienteEl = document.getElementById('telefoneCliente');
            const criarPedidoEl = document.getElementById('criarPedido');
            
            const nomeCliente = nomeClienteEl ? nomeClienteEl.value.trim() : '';
            const telefoneCliente = telefoneClienteEl ? telefoneClienteEl.value.trim() : '';
            const criarPedido = criarPedidoEl ? criarPedidoEl.checked : false;

            // Enviar venda para o servidor
            const formData = new FormData();
            formData.append('action', 'finalizar_venda');
            formData.append('carrinho', JSON.stringify(carrinho));
            formData.append('forma_pagamento', formaPagamento);
            formData.append('nome_cliente', nomeCliente);
            formData.append('telefone_cliente', telefoneCliente);
            formData.append('criar_pedido', criarPedido ? '1' : '0');
            
            if (formaPagamento === 'dinheiro') {
                const valorPagoEl = document.getElementById('valorPago');
                if (valorPagoEl) {
                    formData.append('valor_pago', valorPagoEl.value);
                }
            }

            // CORREÇÃO: URL correta para o endpoint
            fetch('../src/Controllers/actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor');
                }
                return response.json();
            })
            .then(data => {
                ocultarCarregamento(botaoFinalizar);
                
                if (data.success) {
                    // Limpar carrinho e campos
                    const totalVenda = carrinho.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
                    limparCarrinho();
                    
                    // Limpar campos do cliente
                    if (nomeClienteEl) nomeClienteEl.value = '';
                    if (telefoneClienteEl) telefoneClienteEl.value = '';
                    if (criarPedidoEl) criarPedidoEl.checked = false;
                    
                    let mensagem = `Venda finalizada! Total: R$ ${formatarMoeda(totalVenda)}`;
                    
                    // Mostrar informação sobre pedido criado
                    if (data.data && data.data.pedido_criado) {
                        mensagem += `<br><small>Pedido #${data.data.pedido_id} criado para preparo!</small>`;
                    }
                    
                    mostrarAlerta(mensagem, 'success', 3000);
                    
                    // Atualizar interface após um breve delay
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    mostrarAlerta(data.message || 'Erro ao finalizar venda', 'danger');
                }
            })
            .catch(error => {
                ocultarCarregamento(botaoFinalizar);
                tratarErro(error, 'Finalizar venda');
            });
        });
    }
}

// Verificar alerta de estoque baixo
function verificarEstoqueBaixo() {
    // CORREÇÃO: URL correta para o endpoint
    fetch('/Proj_Carrinho_Praia/src/Controllers/actions.php?action=verificar_estoque_baixo')
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na resposta do servidor');
        }
        return response.json();
    })
    .then(data => {
        const alertElement = document.getElementById('alertLowStock');
        const messageElement = document.getElementById('alertMessage');
        
        if (data.success && data.data && data.data.produto) {
            const produto = data.data.produto;
            let mensagem = '';
            let tipoAlerta = 'warning';
            let icone = '⚠️';
            
            if (produto.quantidade === 0) {
                mensagem = `${produto.nome} - SEM ESTOQUE`;
                tipoAlerta = 'danger';
                icone = '🚫';
            } else if (produto.quantidade <= produto.limite_minimo) {
                mensagem = `${produto.nome} - ESTOQUE BAIXO (${produto.quantidade} unidades)`;
                tipoAlerta = 'warning';
                icone = '⚠️';
            }
            
            if (messageElement && alertElement) {
                messageElement.innerHTML = `<strong>${icone} ${mensagem}</strong>`;
                
                // Remover classes de alerta anteriores
                alertElement.classList.remove('alert-warning', 'alert-danger');
                
                // Adicionar classe apropriada
                if (tipoAlerta === 'danger') {
                    alertElement.classList.add('alert-danger');
                } else {
                    alertElement.classList.add('alert-warning');
                }
                
                alertElement.classList.remove('d-none');
                
                // Esconder após tempo baseado na criticidade
                const timeout = produto.quantidade === 0 ? 10000 : 6000;
                setTimeout(() => {
                    alertElement.classList.add('d-none');
                }, timeout);
            } else {
                // Fallback: mostrar alerta personalizado
                mostrarAlerta(`${icone} ${mensagem}`, tipoAlerta, 8000);
            }
        } else if (alertElement) {
            // Ocultar alerta se não há produtos com estoque baixo
            alertElement.classList.add('d-none');
        }
    })
    .catch(error => {
        console.warn('Erro ao verificar estoque baixo:', error);
        // Não mostrar erro para o usuário, pois é verificação em background
    });
}

// NOTA: Funções de produtos foram movidas para produtos-actions.js
// para evitar conflitos entre scripts

// Atualizar gráfico de vendas - VERSÃO CORRIGIDA
function atualizarGraficoVendas() {
    console.log('🔄 Iniciando atualizarGraficoVendas...');
    
    // Tentar várias vezes para encontrar o elemento
    let tentativas = 0;
    const maxTentativas = 5;
    
    function buscarElemento() {
        tentativas++;
        console.log(`🔍 Tentativa ${tentativas} de encontrar elemento graficoVendas...`);
        
        const graficoElement = document.getElementById('graficoVendas');
        if (!graficoElement) {
            console.warn(`❌ Elemento graficoVendas não encontrado! Tentativa ${tentativas}/${maxTentativas}`);
            
            if (tentativas < maxTentativas) {
                setTimeout(buscarElemento, 500); // Tentar novamente em 500ms
                return;
            } else {
                console.error('❌ Elemento graficoVendas não encontrado após todas as tentativas!');
                return;
            }
        }
        
        console.log('✅ Elemento graficoVendas encontrado:', graficoElement);
        carregarGrafico(graficoElement);
    }
    
    function carregarGrafico(graficoElement) {
        // Obter usuário ID da sessão
        const userData = sessionStorage.getItem('user');
        let usuarioId = 1; // Default
        
        if (userData) {
            try {
                const user = JSON.parse(userData);
                usuarioId = user.id || user.usuario_id || 1;
            } catch (e) {
                console.warn('⚠️ Erro ao parsear dados do usuário:', e);
            }
        }
        
        // CORREÇÃO: URL correta com usuário ID
        console.log('🚀 Fazendo fetch para produtos mais vendidos do usuário:', usuarioId);
        fetch(`/Proj_Carrinho_Praia/src/Controllers/actions.php?action=get_produtos_mais_vendidos&usuario_id=${usuarioId}`)
        .then(response => {
            console.log('📡 Resposta recebida:', response);
            return response.json();
        })
        .then(data => {
            console.log('📈 Dados recebidos:', data);
            // Acessar produtos no caminho correto: data.data.produtos
            const produtos = data.data && data.data.produtos ? data.data.produtos : [];
            console.log('📆 Produtos encontrados:', produtos);
            
            // Verifique se os dados são válidos
            if (!data || !data.success || !produtos || produtos.length === 0) {
                console.warn('⚠️ Dados inválidos ou vazios:', data);
                // Mostrar mensagem de erro sem substituir o canvas
                console.log('⚠️ Nenhum dado disponível para o gráfico');
                // NÃO substituir o elemento, apenas mostrar mensagem no console
                // Se não houver dados, limpe o gráfico
                if (window.vendasChart) {
                    window.vendasChart.destroy();
                    window.vendasChart = null;
                }
                return;
            }
            
            try {
                const ctx = graficoElement.getContext('2d');
                
                const labels = produtos.map(p => p.nome);
                const dataValues = produtos.map(p => p.total_vendido);
                
                // Destruir gráfico anterior se existir
                if (window.vendasChart) {
                    window.vendasChart.destroy();
                }
                
                // Verificar se Chart.js está disponível
                console.log('🔍 Verificando Chart.js...', typeof Chart);
                if (typeof Chart !== 'undefined') {
                    console.log('✅ Chart.js encontrado, criando gráfico...');
                    window.vendasChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Quantidade Vendida',
                                data: dataValues,
                                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                                borderColor: 'rgba(40, 167, 69, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, // ADICIONADO
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { // ADICIONADO
                                        stepSize: 1
                                    },
                                    title: {
                                        display: true,
                                        text: 'Quantidade'
                                    }
                                }
                            },
                            plugins: { // ADICIONADO
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            }
                        }
                    });
                } else {
                    console.warn('Chart.js não está carregado');
                    graficoElement.parentElement.innerHTML = '<p class="text-center text-muted">Chart.js não disponível</p>';
                }
            } catch (error) {
                console.warn('Erro ao criar gráfico:', error);
            }
        })
        .catch(error => {
            console.warn('Erro ao carregar dados para o gráfico:', error);
            // Limpe o gráfico se houver erro
            if (window.vendasChart) {
                window.vendasChart.destroy();
                window.vendasChart = null;
            }
            
            // Log do erro sem substituir o elemento
            console.error('❌ Erro ao carregar dados do gráfico');
            // NÃO substituir o elemento para não quebrar futuras chamadas
        });
    }
    
    // Iniciar a busca pelo elemento
    buscarElemento();
}

// Expor função atualizarGraficoVendas globalmente
window.atualizarGraficoVendas = atualizarGraficoVendas;

// Função de debug para testar gráfico
function debugGrafico() {
    console.log('🐛 Iniciando debug do gráfico...');
    
    // Verificar se estamos na aba correta
    const relatoriosTab = document.getElementById('relatorios');
    console.log('📊 Aba relatórios:', relatoriosTab);
    console.log('📊 Aba ativa?', relatoriosTab && relatoriosTab.classList.contains('active'));
    
    // Verificar se o elemento existe
    const graficoElement = document.getElementById('graficoVendas');
    console.log('📈 Elemento gráfico:', graficoElement);
    
    if (graficoElement) {
        console.log('📐 Dimensões do elemento:', {
            width: graficoElement.offsetWidth,
            height: graficoElement.offsetHeight,
            visible: graficoElement.offsetWidth > 0 && graficoElement.offsetHeight > 0
        });
    }
    
    // Verificar Chart.js
    console.log('📊 Chart.js disponível?', typeof Chart !== 'undefined');
    
    // Tentar carregar o gráfico
    if (graficoElement) {
        console.log('🚀 Tentando carregar gráfico...');
        atualizarGraficoVendas();
    }
}

// Expor função de debug globalmente
window.debugGrafico = debugGrafico;

// Função global para mostrar sidebar (para debug)
window.mostrarSidebar = function() {
    console.log('🔧 Forçando exibição da sidebar...');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (sidebar) {
        sidebar.classList.remove('collapsed');
        sidebar.style.transform = 'translateX(0)';
        console.log('✅ Sidebar forçada a aparecer');
    }
    
    if (mainContent) {
        mainContent.classList.remove('collapsed');
        console.log('✅ Main content ajustado');
    }
};

// Função global para debug da sidebar
window.debugSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleButton = document.getElementById('sidebarToggle');
    
    console.log('🐛 Debug da sidebar:', {
        sidebar: !!sidebar,
        mainContent: !!mainContent,
        toggleButton: !!toggleButton,
        sidebarVisible: sidebar ? getComputedStyle(sidebar).transform : 'N/A',
        sidebarClasses: sidebar ? Array.from(sidebar.classList) : [],
        mainContentClasses: mainContent ? Array.from(mainContent.classList) : []
    });
};

// NOVA FUNÇÃO: Corrigir renderização de gráficos
function corrigirGraficoDashboard() {
    try {
        console.log('🔧 Corrigindo gráfico do dashboard...');
        // Destruir gráfico anterior se existir
        if (window.vendasChart) {
            console.log('Destruindo gráfico do dashboard...');
            window.vendasChart.destroy();
            window.vendasChart = null;
        }
        
        console.log('Recarregando gráfico do dashboard...');
        setTimeout(() => {
            atualizarGraficoVendas();
        }, 100);
        
        // Mostrar feedback diretamente para evitar recursão
        console.log('✅ Gráfico do dashboard recarregado!');
        
        // Criar alerta simples sem recursão
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success position-fixed';
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
        alertDiv.innerHTML = '🔧 Gráfico recarregado!';
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 2000);
    } catch (error) {
        console.error('Erro ao corrigir gráfico:', error);
        if (typeof mostrarAlerta === 'function') {
            mostrarAlerta('❌ Erro ao corrigir gráfico', 'danger', 3000);
        }
    }
}

// Enhanced notification system
function mostrarAlerta(mensagem, tipo = 'info', duracao = 4000) {
    // Remove existing alerts of the same type
    const existingAlerts = document.querySelectorAll(`.toast-notification.alert-${tipo}`);
    existingAlerts.forEach(alert => alert.remove());
    
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show toast-notification`;
    alerta.role = 'alert';
    alerta.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 350px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 8px;
    `;
    
    const icons = {
        success: 'bi-check-circle',
        danger: 'bi-exclamation-triangle',
        warning: 'bi-exclamation-circle',
        info: 'bi-info-circle'
    };
    
    alerta.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi ${icons[tipo] || icons.info} me-2"></i>
            <div class="flex-grow-1">${mensagem}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(alerta);
    
    // Auto remove
    const timer = setTimeout(() => {
        if (alerta.parentNode) {
            alerta.classList.add('fade');
            setTimeout(() => alerta.remove(), 300);
        }
    }, duracao);
    
    // Manual close
    alerta.querySelector('.btn-close').addEventListener('click', () => {
        clearTimeout(timer);
        alerta.remove();
    });
}

// Sistema de notificações para ações de produtos
function notificarAcaoProduto(acao, produtoNome, tipo = 'success') {
    const acoes = {
        'cadastrado': {
            icone: '➕',
            mensagem: `Produto "${produtoNome}" cadastrado com sucesso!`,
            tipo: 'success'
        },
        'atualizado': {
            icone: '✏️',
            mensagem: `Produto "${produtoNome}" atualizado com sucesso!`,
            tipo: 'info'
        },
        'reabastecido': {
            icone: '📦',
            mensagem: `Estoque de "${produtoNome}" reabastecido!`,
            tipo: 'success'
        },
        'excluido': {
            icone: '🗑️',
            mensagem: `Produto "${produtoNome}" excluído com sucesso!`,
            tipo: 'warning'
        },
        'sem_estoque': {
            icone: '🚫',
            mensagem: `ATENÇÃO: "${produtoNome}" ficou sem estoque!`,
            tipo: 'danger'
        },
        'estoque_baixo': {
            icone: '⚠️',
            mensagem: `AVISO: "${produtoNome}" com estoque baixo!`,
            tipo: 'warning'
        }
    };
    
    const notificacao = acoes[acao];
    if (notificacao) {
        mostrarAlerta(
            `${notificacao.icone} ${notificacao.mensagem}`,
            notificacao.tipo,
            acao === 'sem_estoque' ? 10000 : 5000
        );
    }
}

// Função para criar notificação no banco de dados
function criarNotificacao(titulo, mensagem, tipo = 'info', produtoId = null, acao = null) {
    const formData = new FormData();
    formData.append('action', 'criar_notificacao');
    formData.append('titulo', titulo);
    formData.append('mensagem', mensagem);
    formData.append('tipo', tipo);
    if (produtoId) formData.append('produto_id', produtoId);
    if (acao) formData.append('acao', acao);
    
    // CORREÇÃO: URL correta para o endpoint
    fetch('../src/Controllers/actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .catch(error => console.warn('Erro ao criar notificação:', error));
}

// Funções de exportação e backup - VERSÃO ÚNICA CORRIGIDA
function exportarVendas() {
    const startDate = document.getElementById('exportStartDate')?.value;
    const endDate = document.getElementById('exportEndDate')?.value;
    
    let url = 'export_data.php?action=vendas';
    
    if (startDate) url += '&start_date=' + encodeURIComponent(startDate);
    if (endDate) url += '&end_date=' + encodeURIComponent(endDate);
    
    mostrarAlerta('Preparando exportação...', 'info', 2000);
    
    // Criar link temporário para download
    const link = document.createElement('a');
    link.href = url;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(() => {
        mostrarAlerta('✅ Dados de vendas exportados com sucesso!', 'success');
    }, 1000);
}

function exportarProdutos() {
    mostrarAlerta('Preparando exportação...', 'info', 2000);
    
    const link = document.createElement('a');
    link.href = 'export_data.php?action=produtos';
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(() => {
        mostrarAlerta('✅ Dados de produtos exportados com sucesso!', 'success');
    }, 1000);
}

function criarBackup() {
    if (!confirm('Deseja criar um backup dos seus dados?\n\nEste processo pode demorar alguns segundos.')) {
        return;
    }
    
    mostrarAlerta('💾 Criando backup...', 'info', 3000);
    
    const link = document.createElement('a');
    link.href = 'backup_system.php?action=backup';
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(() => {
        mostrarAlerta('✅ Backup criado com sucesso!\n\nO arquivo foi baixado para o seu computador.', 'success', 8000);
    }, 2000);
}

// Função para definir datas padrão nos campos de exportação
function configurarDatasExportacao() {
    const startDateEl = document.getElementById('exportStartDate');
    const endDateEl = document.getElementById('exportEndDate');
    
    if (startDateEl && endDateEl) {
        // Data de início: primeiro dia do mês atual
        const hoje = new Date();
        const primeiroDiaDoMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
        
        // Formatar datas no formato YYYY-MM-DD
        const formatarData = (data) => {
            return data.toISOString().split('T')[0];
        };
        
        startDateEl.value = formatarData(primeiroDiaDoMes);
        endDateEl.value = formatarData(hoje);
    }
}

// Loading state management
function mostrarCarregamento(elemento, texto = 'Carregando...') {
    if (typeof elemento === 'string') {
        elemento = document.getElementById(elemento);
    }
    if (!elemento) return;
    
    elemento.dataset.originalContent = elemento.innerHTML;
    elemento.disabled = true;
    elemento.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
        ${texto}
    `;
}

function ocultarCarregamento(elemento) {
    if (typeof elemento === 'string') {
        elemento = document.getElementById(elemento);
    }
    if (!elemento) return;
    
    elemento.disabled = false;
    elemento.innerHTML = elemento.dataset.originalContent || elemento.innerHTML;
    delete elemento.dataset.originalContent;
}

// Enhanced error handling
function tratarErro(error, contexto = '') {
    console.error(`Erro ${contexto}:`, error);
    
    let mensagem = 'Ocorreu um erro inesperado. Tente novamente.';
    
    if (error.message) {
        mensagem = error.message;
    } else if (typeof error === 'string') {
        mensagem = error;
    }
    
    mostrarAlerta(`${contexto ? contexto + ': ' : ''}${mensagem}`, 'danger', 6000);
}

// NOTA: As funções de filtro foram movidas para filtro-simple.js
// para evitar conflitos e duplicação de código

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    try {
        carregarDados();
        // NOTA: inicializarFiltros() foi removido - agora é tratado pelo filtro-simple.js
        
        // Atualizar alertas periodicamente
        setInterval(verificarEstoqueBaixo, 30000);
    } catch (error) {
        // Silenciar erros de inicialização
    }
});
