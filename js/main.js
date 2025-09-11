// Dados iniciais
let carrinho = [];
let localizacaoVendas = [];

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

// Funções auxiliares
function formatarMoeda(valor) {
    return valor.toFixed(2).replace('.', ',');
}

function getDataAtual() {
    const hoje = new Date();
    return hoje.toLocaleDateString('pt-BR');
}

// Carregar dados iniciais
function carregarDados() {
    document.getElementById('dataAtual').textContent = getDataAtual();
    carregarCarrinho(); // Carregar carrinho salvo
    atualizarCarrinho();
    verificarAlertaEstoque();
    atualizarGraficoVendas();
    inicializarMapa();
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
    
    if (carrinho.length === 0) {
        container.innerHTML = '<p class="text-muted">Nenhum item adicionado</p>';
        totalCarrinho.textContent = '0,00';
        document.getElementById('divTroco').style.display = 'none';
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
    const formaPagamento = document.getElementById('formaPagamento').value;
    if (formaPagamento === 'dinheiro') {
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
    const valorPago = parseFloat(document.getElementById('valorPago').value) || 0;
    const total = carrinho.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
    const troco = valorPago - total;
    
    document.getElementById('valorTroco').textContent = formatarMoeda(Math.max(0, troco));
}

// Forma de pagamento
document.getElementById('formaPagamento').addEventListener('change', function() {
    if (this.value === 'dinheiro') {
        document.getElementById('divTroco').style.display = 'block';
    } else {
        document.getElementById('divTroco').style.display = 'none';
    }
});

document.getElementById('valorPago').addEventListener('input', calcularTroco);

// Finalizar venda
document.getElementById('finalizarVenda').addEventListener('click', function() {
    if (carrinho.length === 0) {
        mostrarAlerta('Adicione produtos ao carrinho primeiro!', 'warning');
        return;
    }

    const formaPagamento = document.getElementById('formaPagamento').value;
    
    if (formaPagamento === 'dinheiro') {
        const valorPago = parseFloat(document.getElementById('valorPago').value) || 0;
        const total = carrinho.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
        
        if (valorPago < total) {
            mostrarAlerta('Valor pago insuficiente! Faltam R$ ' + formatarMoeda(total - valorPago), 'danger');
            return;
        }
    }

    const botaoFinalizar = document.getElementById('finalizarVenda');
    mostrarCarregamento(botaoFinalizar, 'Finalizando...');

    // Enviar venda para o servidor
    const formData = new FormData();
    formData.append('action', 'finalizar_venda');
    formData.append('carrinho', JSON.stringify(carrinho));
    formData.append('forma_pagamento', formaPagamento);
    
    if (formaPagamento === 'dinheiro') {
        formData.append('valor_pago', document.getElementById('valorPago').value);
    }

    fetch('actions.php', {
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
            // Limpar carrinho
            limparCarrinho();
            
            const total = carrinho.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
            mostrarAlerta(`Venda finalizada! Total: R$ ${formatarMoeda(total)}`, 'success');
            
            // Atualizar interface após um breve delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            mostrarAlerta(data.message || 'Erro ao finalizar venda', 'danger');
        }
    })
    .catch(error => {
        ocultarCarregamento(botaoFinalizar);
        tratarErro(error, 'Finalizar venda');
    });
});

// Verificar alerta de estoque baixo
function verificarAlertaEstoque() {
    fetch('actions.php?action=verificar_estoque_baixo')
    .then(response => response.json())
    .then(data => {
        if (data.produto) {
            document.getElementById('alertMessage').textContent = 
                `Só restam ${data.produto.quantidade} unidades de ${data.produto.nome}`;
            document.getElementById('alertLowStock').classList.remove('d-none');
            
            // Esconder após 5 segundos
            setTimeout(() => {
                document.getElementById('alertLowStock').classList.add('d-none');
            }, 5000);
        }
    });
}

// Reabastecer produto
function reabastecerProduto(id) {
    fetch(`actions.php?action=get_produto&id=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('produtoReabastecimento').value = data.produto.id;
            document.getElementById('nomeReabastecimento').value = data.produto.nome;
            document.getElementById('quantidadeAtual').value = data.produto.quantidade;
            document.getElementById('quantidadeReabastecimento').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalReabastecimento'));
            modal.show();
        }
    });
}

// Confirmar reabastecimento
document.getElementById('confirmarReabastecimento').addEventListener('click', function() {
    const id = parseInt(document.getElementById('produtoReabastecimento').value);
    const quantidade = parseInt(document.getElementById('quantidadeReabastecimento').value);
    
    if (isNaN(quantidade) || quantidade <= 0) {
        mostrarAlerta('Quantidade inválida!', 'danger');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'reabastecer');
    formData.append('produto_id', id);
    formData.append('quantidade', quantidade);
    
    fetch('actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalReabastecimento'));
            modal.hide();
            
            // Atualizar página
            location.reload();
            
            mostrarAlerta(`Estoque reabastecido com sucesso!`, 'success');
        } else {
            mostrarAlerta('Erro: ' + data.message, 'danger');
        }
    });
});

// Salvar novo produto
document.getElementById('salvarProduto').addEventListener('click', function() {
    const nome = document.getElementById('nomeProduto').value;
    const categoria = document.getElementById('categoriaProduto').value;
    const preco = parseFloat(document.getElementById('precoProduto').value);
    const quantidade = parseInt(document.getElementById('quantidadeProduto').value);
    const limiteMinimo = parseInt(document.getElementById('limiteMinimo').value);
    const validade = document.getElementById('validadeProduto').value;
    const observacoes = document.getElementById('observacoesProduto').value;
    
    if (!nome || isNaN(preco) || isNaN(quantidade) || isNaN(limiteMinimo)) {
        mostrarAlerta('Preencha todos os campos obrigatórios!', 'danger');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'salvar_produto');
    formData.append('nome', nome);
    formData.append('categoria', categoria);
    formData.append('preco', preco);
    formData.append('quantidade', quantidade);
    formData.append('limite_minimo', limiteMinimo);
    formData.append('validade', validade);
    formData.append('observacoes', observacoes);
    
    fetch('actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoProduto'));
            modal.hide();
            
            // Limpar formulário
            document.getElementById('formNovoProduto').reset();
            
            // Atualizar página
            location.reload();
            
            mostrarAlerta('Produto cadastrado com sucesso!', 'success');
        } else {
            mostrarAlerta('Erro: ' + data.message, 'danger');
        }
    });
});

// Editar produto
function editarProduto(id) {
    fetch(`actions.php?action=get_produto&id=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const produto = data.produto;
            
            // Preencher o formulário com os dados do produto
            document.getElementById('nomeProduto').value = produto.nome;
            document.getElementById('categoriaProduto').value = produto.categoria;
            document.getElementById('precoProduto').value = produto.preco;
            document.getElementById('quantidadeProduto').value = produto.quantidade;
            document.getElementById('limiteMinimo').value = produto.limite_minimo;
            document.getElementById('validadeProduto').value = produto.validade;
            document.getElementById('observacoesProduto').value = produto.observacoes;
            
            // Alterar o texto do botão salvar
            document.getElementById('salvarProduto').textContent = 'Atualizar Produto';
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoProduto'));
            modal.hide();
            
            // Após fechar, abrir novamente
            setTimeout(() => {
                const novoModal = new bootstrap.Modal(document.getElementById('modalNovoProduto'));
                novoModal.show();
            }, 500);
            
            // Alterar o evento de salvar para atualizar
            document.getElementById('salvarProduto').onclick = function() {
                const formData = new FormData();
                formData.append('action', 'atualizar_produto');
                formData.append('id', produto.id);
                formData.append('nome', document.getElementById('nomeProduto').value);
                formData.append('categoria', document.getElementById('categoriaProduto').value);
                formData.append('preco', document.getElementById('precoProduto').value);
                formData.append('quantidade', document.getElementById('quantidadeProduto').value);
                formData.append('limite_minimo', document.getElementById('limiteMinimo').value);
                formData.append('validade', document.getElementById('validadeProduto').value);
                formData.append('observacoes', document.getElementById('observacoesProduto').value);
                
                fetch('actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Fechar modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoProduto'));
                        modal.hide();
                        
                        // Limpar formulário
                        document.getElementById('formNovoProduto').reset();
                        
                        // Restaurar texto original do botão
                        document.getElementById('salvarProduto').textContent = 'Salvar Produto';
                        
                        // Atualizar página
                        location.reload();
                        
                        mostrarAlerta('Produto atualizado com sucesso!', 'success');
                    } else {
                        mostrarAlerta('Erro: ' + data.message, 'danger');
                    }
                });
            };
        }
    });
}

// Excluir produto
function excluirProduto(id) {
    if (confirm('Tem certeza que deseja excluir este produto?')) {
        const formData = new FormData();
        formData.append('action', 'excluir_produto');
        formData.append('id', id);
        
        fetch('actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
                mostrarAlerta('Produto excluído com sucesso!', 'success');
            } else {
                mostrarAlerta('Erro: ' + data.message, 'danger');
            }
        });
    }
}

// Atualizar gráfico de vendas
function atualizarGraficoVendas() {
    fetch('actions.php?action=get_produtos_mais_vendidos')
        .then(response => response.json())
        .then(data => {
            // Verifique se os dados são válidos
            if (!data || !data.success || !data.produtos || data.produtos.length === 0) {
                // Se não houver dados, limpe o gráfico
                if (window.vendasChart) {
                    window.vendasChart.destroy();
                }
                return;
            }
            
            const ctx = document.getElementById('graficoVendas').getContext('2d');
            
            const labels = data.produtos.map(p => p.nome);
            const dataValues = data.produtos.map(p => p.total_vendido);
            
            // Destruir gráfico anterior se existir
            if (window.vendasChart) {
                window.vendasChart.destroy();
            }
            
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
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantidade'
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Erro ao carregar dados para o gráfico:', error);
            // Limpe o gráfico se houver erro
            if (window.vendasChart) {
                window.vendasChart.destroy();
            }
        });
}

// Inicializar mapa
function inicializarMapa() {
    // Coordenadas aproximadas de uma praia
    const centro = { lat: -23.550520, lng: -46.633308 };
    
    const map = new google.maps.Map(document.getElementById('map'), {
        zoom: 15,
        center: centro
    });
    
    // Verifique se AdvancedMarkerElement está disponível
    if (typeof google.maps.marker !== 'undefined' && typeof google.maps.marker.AdvancedMarkerElement !== 'undefined') {
        // Use AdvancedMarkerElement se disponível
        const marker = new google.maps.marker.AdvancedMarkerElement({
            position: centro,
            map: map,
            title: 'Ponto de Venda'
        });
    } else {
        // Use o Marker antigo como fallback
        const marker = new google.maps.Marker({
            position: centro,
            map: map,
            title: 'Ponto de Venda'
        });
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

// Export and backup functions
function exportarVendas() {
    const startDate = document.getElementById('exportStartDate')?.value || '';
    const endDate = document.getElementById('exportEndDate')?.value || '';
    
    let url = 'utils/backup_export.php?action=export_sales';
    if (startDate) url += '&start_date=' + encodeURIComponent(startDate);
    if (endDate) url += '&end_date=' + encodeURIComponent(endDate);
    
    // Create hidden link and click to trigger download
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    mostrarAlerta('Iniciando download das vendas...', 'info');
}

function exportarProdutos() {
    const url = 'utils/backup_export.php?action=export_products';
    
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    mostrarAlerta('Iniciando download dos produtos...', 'info');
}

function criarBackup() {
    if (!confirm('Deseja criar um backup completo do banco de dados?')) {
        return;
    }
    
    const url = 'utils/backup_export.php?action=backup_database';
    
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    mostrarAlerta('Criando backup do sistema...', 'info');
}

// Product search and filter functions
function filtrarProdutos() {
    const searchTerm = document.getElementById('searchProdutos')?.value.toLowerCase() || '';
    const selectedCategory = document.getElementById('filtroCategoria')?.value || '';
    const produtos = document.querySelectorAll('#produtosVenda .col-md-4, #produtosVenda .col-sm-6');
    
    produtos.forEach(produto => {
        const button = produto.querySelector('.product-btn');
        if (!button) return;
        
        const nome = button.querySelector('strong')?.textContent.toLowerCase() || '';
        const categoria = button.getAttribute('data-categoria') || '';
        
        const matchesSearch = nome.includes(searchTerm);
        const matchesCategory = !selectedCategory || categoria === selectedCategory;
        
        if (matchesSearch && matchesCategory) {
            produto.style.display = 'block';
        } else {
            produto.style.display = 'none';
        }
    });
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    carregarDados();
    
    // Adicionar event listeners para busca e filtro
    const searchInput = document.getElementById('searchProdutos');
    const categoryFilter = document.getElementById('filtroCategoria');
    
    if (searchInput) {
        searchInput.addEventListener('input', filtrarProdutos);
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filtrarProdutos);
    }
    
    // Atualizar alertas periodicamente
    setInterval(verificarAlertaEstoque, 30000);
});
