/**
 * VENDA RÁPIDA - JavaScript
 * Sistema de vendas ultra-rápido para alta rotatividade
 */

// Carrinho em memória
let carrinhoRapido = [];

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    inicializarVendaRapida();
});

/**
 * Inicializa o sistema de venda rápida
 */
function inicializarVendaRapida() {
    // Atualizar hora atual
    atualizarHora();
    setInterval(atualizarHora, 1000);
    
    // Busca em tempo real
    const buscaInput = document.getElementById('buscaRapida');
    if (buscaInput) {
        buscaInput.addEventListener('input', function() {
            filtrarProdutosPorBusca(this.value);
        });
    }
    
    // Atalhos de teclado
    document.addEventListener('keydown', function(e) {
        // ESC = Limpar carrinho
        if (e.key === 'Escape') {
            limparCarrinhoRapido();
        }
        // F1 = Dinheiro
        if (e.key === 'F1') {
            e.preventDefault();
            finalizarVendaRapida('dinheiro');
        }
        // F2 = PIX
        if (e.key === 'F2') {
            e.preventDefault();
            finalizarVendaRapida('pix');
        }
        // F3 = Cartão
        if (e.key === 'F3') {
            e.preventDefault();
            finalizarVendaRapida('cartao');
        }
    });
    
    console.log('✅ Venda Rápida inicializada');
}

/**
 * Atualiza hora atual
 */
function atualizarHora() {
    const agora = new Date();
    const horaFormatada = agora.toLocaleTimeString('pt-BR', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit'
    });
    
    const elementoHora = document.getElementById('horaAtual');
    if (elementoHora) {
        elementoHora.textContent = horaFormatada;
    }
}

/**
 * Adiciona produto ao carrinho a partir do botão (via data attributes)
 */
function adicionarProdutoRapidoFromButton(button) {
    const id = parseInt(button.dataset.id);
    const nome = button.dataset.nome;
    const preco = parseFloat(button.dataset.preco);
    const estoque = parseInt(button.dataset.estoque);
    
    adicionarProdutoRapido(id, nome, preco, estoque);
}

/**
 * Adiciona produto ao carrinho rápido
 */
function adicionarProdutoRapido(id, nome, preco, estoque) {
    // Verificar estoque
    const itemExistente = carrinhoRapido.find(item => item.id === id);
    const quantidadeAtual = itemExistente ? itemExistente.quantidade : 0;
    
    if (quantidadeAtual >= estoque) {
        mostrarAlerta('Estoque insuficiente!', 'warning');
        return;
    }
    
    // Adicionar ou incrementar
    if (itemExistente) {
        itemExistente.quantidade++;
    } else {
        carrinhoRapido.push({
            id: id,
            nome: nome,
            preco: parseFloat(preco),
            quantidade: 1,
            estoque: estoque
        });
    }
    
    // Feedback visual
    animarAdicao();
    
    // Atualizar interface
    atualizarCarrinhoUI();
    
    console.log('✅ Produto adicionado:', nome);
}

/**
 * Incrementa quantidade do produto no carrinho
 */
function incrementarProdutoRapido(id) {
    const item = carrinhoRapido.find(item => item.id === id);
    if (item) {
        if (item.quantidade < item.estoque) {
            item.quantidade++;
            atualizarCarrinhoUI();
        } else {
            mostrarAlerta('Estoque insuficiente! Disponível: ' + item.estoque + ' unidades', 'warning');
        }
    }
}

/**
 * Decrementa quantidade do produto no carrinho
 */
function decrementarProdutoRapido(id) {
    const item = carrinhoRapido.find(item => item.id === id);
    if (item) {
        item.quantidade--;
        if (item.quantidade <= 0) {
            carrinhoRapido = carrinhoRapido.filter(item => item.id !== id);
        }
        atualizarCarrinhoUI();
    }
}

/**
 * Remove item completamente
 */
function removerItemCompleto(id) {
    carrinhoRapido = carrinhoRapido.filter(item => item.id !== id);
    atualizarCarrinhoUI();
}

/**
 * Atualiza interface do carrinho
 */
function atualizarCarrinhoUI() {
    const containerItens = document.getElementById('carrinhoItens');
    
    if (carrinhoRapido.length === 0) {
        containerItens.innerHTML = `
            <div class="carrinho-vazio">
                <i class="bi bi-cart-x"></i>
                <p>Nenhum item adicionado</p>
                <small class="text-muted">Clique nos produtos para adicionar</small>
            </div>
        `;
    } else {
        let html = '';
        carrinhoRapido.forEach(item => {
            const subtotal = item.preco * item.quantidade;
            html += `
                <div class="carrinho-item">
                    <div class="item-info">
                        <div class="item-nome">${item.nome}</div>
                        <div class="item-preco">R$ ${item.preco.toFixed(2).replace('.', ',')}</div>
                    </div>
                    <div class="item-controles">
                        <button class="btn-qty" onclick="decrementarProdutoRapido(${item.id})">
                            <i class="bi bi-dash"></i>
                        </button>
                        <span class="item-quantidade">${item.quantidade}</span>
                        <button class="btn-qty" onclick="incrementarProdutoRapido(${item.id})">
                            <i class="bi bi-plus"></i>
                        </button>
                        <button class="btn-remover" onclick="removerItemCompleto(${item.id})" title="Remover item">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="item-subtotal">
                        R$ ${subtotal.toFixed(2).replace('.', ',')}
                    </div>
                </div>
            `;
        });
        containerItens.innerHTML = html;
    }
    
    // Atualizar totais
    atualizarTotais();
}

/**
 * Atualiza valores totais
 */
function atualizarTotais() {
    const subtotal = carrinhoRapido.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
    const totalItens = carrinhoRapido.reduce((sum, item) => sum + item.quantidade, 0);
    
    document.getElementById('subtotalValor').textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
    document.getElementById('totalValor').textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
    document.getElementById('totalItens').textContent = totalItens;
    
    // Atualizar também o total no resumo de pagamento
    const totalVendaPagamento = document.getElementById('totalVendaPagamento');
    if (totalVendaPagamento) {
        totalVendaPagamento.textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
    }
    
    // Recalcular pagamento misto se houver valores
    calcularPagamentoMisto();
}

/**
 * Toggle forma de pagamento clicando na caixa inteira
 */
function toggleFormaPagamentoBox(element, forma) {
    const checkbox = document.getElementById(`check${forma.charAt(0).toUpperCase() + forma.slice(1)}`);
    const input = document.getElementById(`valor${forma.charAt(0).toUpperCase() + forma.slice(1)}`);
    
    // Toggle checkbox
    checkbox.checked = !checkbox.checked;
    
    // Adicionar/remover classe selected
    if (checkbox.checked) {
        element.classList.add('selected');
        input.disabled = false;
        
        // Lógica de auto-preenchimento inteligente
        setTimeout(() => {
            const formasMarcadas = contarFormasMarcadas();
            const total = carrinhoRapido.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
            
            if (formasMarcadas === 1) {
                // Se for a PRIMEIRA forma marcada, preencher com o total
                input.value = total.toFixed(2);
            } else {
                // Se for a SEGUNDA ou TERCEIRA, limpar o primeiro valor também
                input.value = ''; // Deixar vazio para o usuário preencher
                
                // Limpar valores de todas as formas marcadas para forçar preenchimento manual
                ['dinheiro', 'pix', 'cartao', 'fiado'].forEach(f => {
                    const cb = document.getElementById(`check${f.charAt(0).toUpperCase() + f.slice(1)}`);
                    const inp = document.getElementById(`valor${f.charAt(0).toUpperCase() + f.slice(1)}`);
                    if (cb && cb.checked && inp) {
                        inp.value = ''; // Limpar todos
                    }
                });
            }
            input.focus();
            input.select();
            
            // Recalcular APÓS preencher o valor
            calcularPagamentoMisto();
        }, 50);
    } else {
        element.classList.remove('selected');
        input.disabled = true;
        input.value = '';
        
        // Se sobrou apenas 1 forma marcada após desmarcar, preencher com o total
        setTimeout(() => {
            const formasMarcadas = contarFormasMarcadas();
            if (formasMarcadas === 1) {
                const total = carrinhoRapido.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
                // Encontrar a forma que está marcada e preencher
                ['dinheiro', 'pix', 'cartao', 'fiado'].forEach(f => {
                    const cb = document.getElementById(`check${f.charAt(0).toUpperCase() + f.slice(1)}`);
                    const inp = document.getElementById(`valor${f.charAt(0).toUpperCase() + f.slice(1)}`);
                    if (cb && cb.checked && inp) {
                        inp.value = total.toFixed(2);
                    }
                });
            }
            
            // Recalcular APÓS preencher o valor
            calcularPagamentoMisto();
        }, 50);
    }
}

/**
 * Habilita/desabilita input de valor ao marcar checkbox (mantido para compatibilidade)
 */
function toggleFormaPagamento(forma) {
    const checkbox = document.getElementById(`check${forma.charAt(0).toUpperCase() + forma.slice(1)}`);
    const input = document.getElementById(`valor${forma.charAt(0).toUpperCase() + forma.slice(1)}`);
    const element = checkbox.closest('.forma-pagamento-item');
    
    if (checkbox.checked) {
        element.classList.add('selected');
        input.disabled = false;
        input.focus();
        
        // Auto-preencher com valor restante se for a primeira forma
        const formasMarcadas = contarFormasMarcadas();
        if (formasMarcadas === 1) {
            const total = carrinhoRapido.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
            input.value = total.toFixed(2);
        }
    } else {
        element.classList.remove('selected');
        input.disabled = true;
        input.value = '';
    }
    
    calcularPagamentoMisto();
}

/**
 * Conta quantas formas de pagamento estão marcadas
 */
function contarFormasMarcadas() {
    let count = 0;
    ['dinheiro', 'pix', 'cartao', 'fiado'].forEach(forma => {
        const checkbox = document.getElementById(`check${forma.charAt(0).toUpperCase() + forma.slice(1)}`);
        if (checkbox && checkbox.checked) count++;
    });
    return count;
}

/**
 * Converte valor de string para número (aceita vírgula ou ponto)
 */
function converterValor(valor) {
    if (!valor) return 0;
    // Remove espaços e substitui vírgula por ponto
    const valorLimpo = String(valor).trim().replace(',', '.');
    const numero = parseFloat(valorLimpo);
    return isNaN(numero) ? 0 : numero;
}

/**
 * Calcula e atualiza resumo de pagamento misto
 */
function calcularPagamentoMisto() {
    const total = carrinhoRapido.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
    
    let totalPago = 0;
    ['dinheiro', 'pix', 'cartao', 'fiado'].forEach(forma => {
        const checkbox = document.getElementById(`check${forma.charAt(0).toUpperCase() + forma.slice(1)}`);
        const input = document.getElementById(`valor${forma.charAt(0).toUpperCase() + forma.slice(1)}`);
        
        if (checkbox && checkbox.checked && input && input.value) {
            totalPago += converterValor(input.value);
        }
    });
    
    const restante = total - totalPago;
    
    // Atualizar UI
    document.getElementById('totalPago').textContent = `R$ ${totalPago.toFixed(2).replace('.', ',')}`;
    document.getElementById('valorRestante').textContent = `R$ ${Math.abs(restante).toFixed(2).replace('.', ',')}`;
    
    // Cor do restante
    const elementoRestante = document.getElementById('valorRestante');
    if (restante > 0.01) {
        elementoRestante.classList.remove('text-success');
        elementoRestante.classList.add('text-danger');
    } else if (restante < -0.01) {
        elementoRestante.classList.remove('text-danger');
        elementoRestante.classList.add('text-warning');
    } else {
        elementoRestante.classList.remove('text-danger', 'text-warning');
        elementoRestante.classList.add('text-success');
    }
    
    // Habilitar/desabilitar botão finalizar
    const btnFinalizar = document.getElementById('btnFinalizarVenda');
    if (btnFinalizar) {
        if (carrinhoRapido.length === 0 || totalPago < total - 0.01) {
            btnFinalizar.disabled = true;
            btnFinalizar.classList.add('opacity-50');
        } else {
            btnFinalizar.disabled = false;
            btnFinalizar.classList.remove('opacity-50');
        }
    }
}

/**
 * Finaliza venda com pagamento misto
 */
function finalizarVendaMista() {
    if (carrinhoRapido.length === 0) {
        mostrarAlerta('Carrinho vazio! Adicione produtos primeiro.', 'warning');
        return;
    }
    
    const total = carrinhoRapido.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
    
    // Coletar formas de pagamento selecionadas
    const formasPagamento = [];
    ['dinheiro', 'pix', 'cartao', 'fiado'].forEach(forma => {
        const checkbox = document.getElementById(`check${forma.charAt(0).toUpperCase() + forma.slice(1)}`);
        const input = document.getElementById(`valor${forma.charAt(0).toUpperCase() + forma.slice(1)}`);
        
        if (checkbox && checkbox.checked && input && input.value) {
            const valor = converterValor(input.value);
            if (valor > 0) {
                formasPagamento.push({
                    forma: forma,
                    valor: valor
                });
            }
        }
    });
    
    // Validar
    if (formasPagamento.length === 0) {
        mostrarAlerta('Selecione pelo menos uma forma de pagamento!', 'warning');
        return;
    }
    
    const totalPago = formasPagamento.reduce((sum, f) => sum + f.valor, 0);
    if (totalPago < total - 0.01) {
        mostrarAlerta(`Valor insuficiente! Faltam R$ ${(total - totalPago).toFixed(2).replace('.', ',')}`, 'warning');
        return;
    }
    
    // Preparar dados da venda
    const formData = new URLSearchParams();
    formData.append('action', 'finalizar_venda');
    formData.append('carrinho', JSON.stringify(carrinhoRapido));
    formData.append('nome_cliente', '');
    formData.append('telefone_cliente', '');
    formData.append('criar_pedido', '0');
    formData.append('valor_pago', totalPago);
    
    // Adicionar formas de pagamento (até 3)
    if (formasPagamento[0]) {
        formData.append('forma_pagamento', formasPagamento[0].forma);
        formData.append('valor_pago', formasPagamento[0].valor);
    }
    if (formasPagamento[1]) {
        formData.append('forma_pagamento_secundaria', formasPagamento[1].forma);
        formData.append('valor_pago_secundario', formasPagamento[1].valor);
    }
    if (formasPagamento[2]) {
        formData.append('forma_pagamento_terciaria', formasPagamento[2].forma);
        formData.append('valor_pago_terciario', formasPagamento[2].valor);
    }
    
    // Enviar para servidor
    console.log('Enviando venda mista:', formData.toString());
    
    fetch('../src/Controllers/actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            // Mostrar modal de sucesso
            mostrarModalSucessoMisto(total, formasPagamento);
            
            // Limpar carrinho e formas de pagamento
            carrinhoRapido = [];
            limparFormasPagamento();
            atualizarCarrinhoUI();
            
            // Recarregar página após 2s para atualizar estoque
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            mostrarAlerta('Erro ao finalizar venda: ' + (data.message || 'Erro desconhecido'), 'danger');
        }
    })
    .catch(error => {
        console.error('Erro ao processar venda:', error);
        mostrarAlerta('Erro de conexão ao processar venda. Verifique o console.', 'danger');
    });
}

/**
 * Limpa todas as formas de pagamento
 */
function limparFormasPagamento() {
    ['dinheiro', 'pix', 'cartao', 'fiado'].forEach(forma => {
        const checkbox = document.getElementById(`check${forma.charAt(0).toUpperCase() + forma.slice(1)}`);
        const input = document.getElementById(`valor${forma.charAt(0).toUpperCase() + forma.slice(1)}`);
        
        if (checkbox) {
            checkbox.checked = false;
            // Remover classe selected da caixa
            const element = checkbox.closest('.forma-pagamento-item');
            if (element) element.classList.remove('selected');
        }
        if (input) {
            input.value = '';
            input.disabled = true;
        }
    });
    
    calcularPagamentoMisto();
}

/**
 * Finaliza venda rápida
 */
function finalizarVendaRapida(formaPagamento) {
    if (carrinhoRapido.length === 0) {
        mostrarAlerta('Carrinho vazio! Adicione produtos primeiro.', 'warning');
        return;
    }
    
    const total = carrinhoRapido.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
    
    // Preparar dados da venda no formato esperado pelo backend
    const formData = new URLSearchParams();
    formData.append('action', 'finalizar_venda');
    formData.append('carrinho', JSON.stringify(carrinhoRapido));
    formData.append('forma_pagamento', formaPagamento);
    formData.append('nome_cliente', '');
    formData.append('telefone_cliente', '');
    formData.append('criar_pedido', '0');
    formData.append('valor_pago', total);
    
    // Enviar para servidor
    console.log('Enviando venda:', formData.toString());
    
    fetch('../src/Controllers/actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            // Mostrar modal de sucesso
            mostrarModalSucesso(total, formaPagamento);
            
            // Limpar carrinho
            carrinhoRapido = [];
            atualizarCarrinhoUI();
            
            // Recarregar página após 2s para atualizar estoque
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            mostrarAlerta('Erro ao finalizar venda: ' + (data.message || 'Erro desconhecido'), 'danger');
        }
    })
    .catch(error => {
        console.error('Erro ao processar venda:', error);
        mostrarAlerta('Erro de conexão ao processar venda. Verifique o console.', 'danger');
    });
}

/**
 * Mostra modal de sucesso com pagamento misto
 */
function mostrarModalSucessoMisto(total, formasPagamento) {
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmacaoVenda'));
    
    // Preencher total
    document.getElementById('modalTotalVenda').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
    
    // Criar lista de formas de pagamento
    const nomesFormas = {
        'dinheiro': 'Dinheiro',
        'pix': 'PIX',
        'cartao': 'Cartão',
        'fiado': 'Fiado'
    };
    
    let formasHTML = '';
    formasPagamento.forEach((forma, index) => {
        const nomForma = nomesFormas[forma.forma] || forma.forma;
        const valor = forma.valor.toFixed(2).replace('.', ',');
        formasHTML += `<span class="badge badge-primary me-1">${nomForma}: R$ ${valor}</span>`;
    });
    
    document.getElementById('modalFormaPagamento').innerHTML = formasHTML;
    
    const agora = new Date();
    document.getElementById('modalHoraVenda').textContent = agora.toLocaleString('pt-BR');
    
    // Mostrar modal
    modal.show();
    
    // Som de sucesso
    playSuccessSound();
}

/**
 * Mostra modal de sucesso (versão simples)
 */
function mostrarModalSucesso(total, formaPagamento) {
    // Converter para array para usar a função mista
    mostrarModalSucessoMisto(total, [{forma: formaPagamento, valor: total}]);
}

/**
 * Nova venda (limpa e fecha modal)
 */
function novaVendaRapida() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmacaoVenda'));
    if (modal) {
        modal.hide();
    }
    
    carrinhoRapido = [];
    atualizarCarrinhoUI();
    
    // Focar na busca
    const buscaInput = document.getElementById('buscaRapida');
    if (buscaInput) {
        buscaInput.focus();
    }
}

/**
 * Limpa carrinho
 */
function limparCarrinhoRapido() {
    if (carrinhoRapido.length === 0) return;
    
    if (confirm('Deseja realmente limpar o carrinho?')) {
        carrinhoRapido = [];
        limparFormasPagamento();
        atualizarCarrinhoUI();
        mostrarAlerta('Carrinho limpo!', 'info');
    }
}

/**
 * Filtra produtos por categoria
 */
function filtrarCategoria(categoria) {
    // Atualizar botões ativos
    document.querySelectorAll('.btn-categoria').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-categoria="${categoria}"]`).classList.add('active');
    
    // Filtrar produtos
    const produtos = document.querySelectorAll('.produto-btn');
    produtos.forEach(produto => {
        if (categoria === 'todos' || produto.dataset.categoria === categoria) {
            produto.style.display = 'flex';
        } else {
            produto.style.display = 'none';
        }
    });
}

/**
 * Filtra produtos por busca
 */
function filtrarProdutosPorBusca(termo) {
    termo = termo.toLowerCase().trim();
    
    const produtos = document.querySelectorAll('.produto-btn');
    produtos.forEach(produto => {
        const nome = produto.dataset.nome.toLowerCase();
        if (nome.includes(termo)) {
            produto.style.display = 'flex';
        } else {
            produto.style.display = 'none';
        }
    });
    
    // Se busca vazia, mostrar todos da categoria ativa
    if (termo === '') {
        const categoriaAtiva = document.querySelector('.btn-categoria.active').dataset.categoria;
        filtrarCategoria(categoriaAtiva);
    }
}

/**
 * Animação ao adicionar produto
 */
function animarAdicao() {
    const carrinho = document.querySelector('.carrinho-rapido .card');
    if (carrinho) {
        carrinho.classList.add('pulse-animation');
        setTimeout(() => {
            carrinho.classList.remove('pulse-animation');
        }, 300);
    }
}

/**
 * Som de sucesso
 */
function playSuccessSound() {
    // Criar som usando Web Audio API (opcional)
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    } catch (e) {
        // Silenciar erro se áudio não disponível
        console.log('Audio not available');
    }
}

/**
 * Mostra alerta temporário
 */
function mostrarAlerta(mensagem, tipo = 'info') {
    // Criar elemento de alerta
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo} alert-dismissible fade show`;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '90px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    
    alertDiv.innerHTML = `
        <i class="bi bi-${tipo === 'success' ? 'check-circle' : tipo === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
        ${mensagem}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Remover após 3 segundos
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Exportar funções globais
window.adicionarProdutoRapido = adicionarProdutoRapido;
window.decrementarProdutoRapido = decrementarProdutoRapido;
window.incrementarProdutoRapido = incrementarProdutoRapido;
window.removerItemCompleto = removerItemCompleto;
window.finalizarVendaRapida = finalizarVendaRapida;
window.novaVendaRapida = novaVendaRapida;
window.limparCarrinhoRapido = limparCarrinhoRapido;
window.filtrarCategoria = filtrarCategoria;

console.log('📦 Venda Rápida JS carregado');
