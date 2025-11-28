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
    
    // Validar cliente se houver pagamento fiado
    const temFiado = formasPagamento.some(f => f.forma === 'fiado');
    if (temFiado && !clienteFiadoSelecionado) {
        mostrarAlerta('Por favor, selecione um cliente para venda fiada!', 'warning');
        return;
    }
    
    // Capturar nome do cliente (opcional)
    const nomeClienteInput = document.getElementById('nomeClienteVenda');
    const nomeCliente = nomeClienteInput ? nomeClienteInput.value.trim() : '';
    
    // Preparar dados da venda
    const formData = new URLSearchParams();
    formData.append('action', 'finalizar_venda');
    formData.append('carrinho', JSON.stringify(carrinhoRapido));
    formData.append('nome_cliente', clienteFiadoSelecionado ? clienteFiadoSelecionado.nome : nomeCliente);
    formData.append('telefone_cliente', '');
    formData.append('criar_pedido', '0');
    formData.append('valor_pago', totalPago);
    
    // Adicionar cliente fiado se selecionado
    if (clienteFiadoSelecionado) {
        formData.append('cliente_fiado_id', clienteFiadoSelecionado.id);
    }
    
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
            clienteFiadoSelecionado = null;
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
    
    // Capturar nome do cliente (opcional)
    const nomeClienteInput = document.getElementById('nomeClienteVenda');
    const nomeCliente = nomeClienteInput ? nomeClienteInput.value.trim() : '';
    
    // Preparar dados da venda no formato esperado pelo backend
    const formData = new URLSearchParams();
    formData.append('action', 'finalizar_venda');
    formData.append('carrinho', JSON.stringify(carrinhoRapido));
    formData.append('forma_pagamento', formaPagamento);
    formData.append('nome_cliente', nomeCliente);
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
    
    // Limpar campo nome do cliente
    const nomeClienteInput = document.getElementById('nomeClienteVenda');
    if (nomeClienteInput) {
        nomeClienteInput.value = '';
    }
    
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
        
        // Limpar campo nome do cliente
        const nomeClienteInput = document.getElementById('nomeClienteVenda');
        if (nomeClienteInput) {
            nomeClienteInput.value = '';
        }
        
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

// ============================================
// INTEGRAÇÃO COM CLIENTE FIADO
// ============================================

let clientesFiadoCache = [];
let clienteFiadoSelecionado = null;

/**
 * Intercepta clique na forma de pagamento Fiado
 */
document.addEventListener('DOMContentLoaded', function() {
    const checkFiado = document.getElementById('checkFiado');
    if (checkFiado) {
        checkFiado.addEventListener('change', function(e) {
            if (this.checked) {
                // Abrir modal de seleção de cliente
                abrirModalSelecionarClienteFiado();
            } else {
                // Limpar seleção
                clienteFiadoSelecionado = null;
            }
        });
    }
});

/**
 * Abre modal para selecionar cliente fiado
 */
function abrirModalSelecionarClienteFiado() {
    const modal = new bootstrap.Modal(document.getElementById('modalSelecionarClienteFiado'));
    modal.show();
    carregarClientesFiadoVenda();
}

/**
 * Carrega lista de clientes fiado
 */
async function carregarClientesFiadoVenda() {
    const container = document.getElementById('listaClientesFiadoVenda');
    container.innerHTML = '<div class="text-center py-4"><i class="bi bi-hourglass-split" style="font-size: 2rem;"></i><p class="text-muted">Carregando...</p></div>';
    
    try {
        const response = await fetch('../src/Controllers/actions.php?action=listarClientesFiado');
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            clientesFiadoCache = data.data;
            renderizarClientesFiadoVenda(clientesFiadoCache);
        } else {
            container.innerHTML = '<div class="text-center py-4"><i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.3;"></i><p class="text-muted">Nenhum cliente cadastrado</p></div>';
        }
    } catch (error) {
        console.error('Erro ao carregar clientes:', error);
        container.innerHTML = '<div class="text-center py-4 text-danger"><i class="bi bi-x-circle" style="font-size: 2rem;"></i><p>Erro ao carregar clientes</p></div>';
    }
}

/**
 * Renderiza lista de clientes
 */
function renderizarClientesFiadoVenda(clientes) {
    const container = document.getElementById('listaClientesFiadoVenda');
    const totalVenda = carrinhoRapido.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
    
    let html = '';
    clientes.forEach(cliente => {
        const saldo = parseFloat(cliente.saldo_devedor);
        const limite = parseFloat(cliente.limite_credito);
        const disponivel = limite - saldo;
        const podeComprar = disponivel >= totalVenda;
        
        const classeItem = podeComprar ? '' : 'indisponivel';
        const badge = podeComprar ? '<span class="badge bg-success badge-status-fiado">Disponível</span>' : '<span class="badge bg-danger badge-status-fiado">Limite insuficiente</span>';
        
        html += `
            <div class="cliente-fiado-item ${classeItem}" onclick="${podeComprar ? `selecionarClienteFiado(${cliente.id}, '${cliente.nome}', ${disponivel})` : ''}">
                <div class="cliente-fiado-info">
                    <div class="cliente-fiado-nome">
                        <i class="bi bi-person-circle text-primary"></i>
                        ${cliente.nome}
                        ${badge}
                    </div>
                    <div class="cliente-fiado-detalhes">
                        ${cliente.telefone ? `<span><i class="bi bi-telephone"></i> ${cliente.telefone}</span>` : ''}
                        <span><i class="bi bi-wallet2"></i> Limite: R$ ${limite.toFixed(2).replace('.', ',')}</span>
                    </div>
                </div>
                <div class="cliente-fiado-limite">
                    <div class="cliente-fiado-disponivel">Disponível:</div>
                    <div class="cliente-fiado-valor">R$ ${disponivel.toFixed(2).replace('.', ',')}</div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

/**
 * Filtra clientes na busca
 */
function filtrarClientesFiadoVenda() {
    const busca = document.getElementById('buscaClienteFiadoVenda').value.toLowerCase();
    const clientesFiltrados = clientesFiadoCache.filter(cliente => {
        return cliente.nome.toLowerCase().includes(busca) || 
               (cliente.telefone && cliente.telefone.includes(busca));
    });
    renderizarClientesFiadoVenda(clientesFiltrados);
}

/**
 * Seleciona cliente para venda fiada
 */
function selecionarClienteFiado(clienteId, clienteNome, limiteDisponivel) {
    clienteFiadoSelecionado = {
        id: clienteId,
        nome: clienteNome,
        limite_disponivel: limiteDisponivel
    };
    
    // Fechar modal de seleção
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalSelecionarClienteFiado'));
    modal.hide();
    
    // Preencher valor do fiado automaticamente
    const total = carrinhoRapido.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
    document.getElementById('valorFiado').value = total.toFixed(2);
    
    mostrarAlerta(`Cliente selecionado: ${clienteNome}`, 'success');
    
    calcularPagamentoMisto();
}

/**
 * Abre modal de cadastro rápido
 */
function abrirCadastroRapidoCliente() {
    // Fechar modal de seleção
    const modalSelecao = bootstrap.Modal.getInstance(document.getElementById('modalSelecionarClienteFiado'));
    if (modalSelecao) modalSelecao.hide();
    
    // Abrir modal de cadastro
    setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('modalCadastroRapidoCliente'));
        modal.show();
        document.getElementById('formCadastroRapidoCliente').reset();
    }, 300);
}

/**
 * Salva cliente rápido
 */
async function salvarClienteRapido() {
    const nome = document.getElementById('rapidoNomeCliente').value.trim();
    
    if (!nome) {
        alert('Por favor, preencha o nome do cliente');
        return;
    }
    
    const dados = {
        action: 'cadastrarClienteFiado',
        nome: nome,
        telefone: document.getElementById('rapidoTelefoneCliente').value.trim(),
        cpf: '',
        endereco: '',
        limite_credito: document.getElementById('rapidoLimiteCredito').value,
        observacoes: 'Cadastro rápido via Venda Rápida'
    };
    
    try {
        const response = await fetch('../src/Controllers/actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(dados)
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Fechar modal de cadastro
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalCadastroRapidoCliente'));
            modal.hide();
            
            // Selecionar automaticamente o novo cliente
            const limiteCredito = parseFloat(document.getElementById('rapidoLimiteCredito').value);
            selecionarClienteFiado(data.data.cliente_id, nome, limiteCredito);
            
            mostrarAlerta('Cliente cadastrado e selecionado!', 'success');
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao cadastrar cliente');
    }
}

// ==============================================
//  SISTEMA DE GUARDA-SÓIS
// ==============================================

let guardasolSelecionado = null;
let todosGuardasois = [];

/**
 * Abre modal de seleção de guarda-sol
 */
async function abrirModalGuardasol() {
    const modal = new bootstrap.Modal(document.getElementById('modalSelecionarGuardasol'));
    modal.show();
    
    // Carregar lista de guarda-sóis
    await carregarGuardasois();
}

/**
 * Carrega lista de guarda-sóis do servidor
 */
async function carregarGuardasois() {
    const gridGuardasois = document.getElementById('gridGuardasois');
    
    // Loading
    gridGuardasois.innerHTML = `
        <div class="text-center py-4">
            <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
            <p class="text-muted">Carregando guarda-sóis...</p>
        </div>
    `;
    
    try {
        const response = await fetch('../src/Controllers/actions.php?action=listarGuardasois');
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            todosGuardasois = data.data;
            renderizarGuardasois(todosGuardasois);
        } else {
            gridGuardasois.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-umbrella" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="text-muted mt-3">Nenhum guarda-sol cadastrado ainda.</p>
                    <p class="text-muted"><small>Entre em contato com o administrador para cadastrar guarda-sóis.</small></p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro ao carregar guarda-sóis:', error);
        gridGuardasois.innerHTML = `
            <div class="col-12 text-center py-4">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                <p class="text-danger">Erro ao carregar guarda-sóis</p>
            </div>
        `;
    }
}

/**
 * Renderiza grid de guarda-sóis
 */
function renderizarGuardasois(guardasois) {
    const gridGuardasois = document.getElementById('gridGuardasois');
    
    if (guardasois.length === 0) {
        gridGuardasois.innerHTML = `
            <div class="col-12 text-center py-4">
                <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
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
            <div class="guardasol-card ${statusClass}" onclick="selecionarGuardasol(${gs.id}, ${gs.numero}, '${gs.status}', '${gs.cliente_nome || ''}', ${gs.total_consumido || 0})">
                <div class="guardasol-icon">${icone}</div>
                <div class="guardasol-numero">#${gs.numero}</div>
                <div class="guardasol-status-badge ${statusBadgeClass}">${statusText}</div>
                ${gs.cliente_nome ? `<div class="guardasol-cliente"><i class="bi bi-person"></i> ${gs.cliente_nome}</div>` : ''}
                ${gs.total_consumido > 0 ? `<div class="guardasol-valor">R$ ${parseFloat(gs.total_consumido).toFixed(2).replace('.', ',')}</div>` : ''}
            </div>
        `;
    });
    
    gridGuardasois.innerHTML = html;
}

/**
 * Seleciona um guarda-sol
 */
function selecionarGuardasol(id, numero, status, clienteNome, totalConsumido) {
    guardasolSelecionado = {
        id: id,
        numero: numero,
        status: status,
        cliente_nome: clienteNome,
        total_consumido: totalConsumido
    };
    
    // Atualizar display no header
    const displayInfo = document.getElementById('guardasolInfoDisplay');
    let infoText = `Guarda-sol #${numero} - ${status === 'vazio' ? 'Vazio' : status === 'ocupado' ? 'Ocupado' : 'Aguardando Pagamento'}`;
    
    if (clienteNome) {
        infoText += ` - ${clienteNome}`;
    }
    
    if (totalConsumido > 0) {
        infoText += ` (R$ ${parseFloat(totalConsumido).toFixed(2).replace('.', ',')})`;
    }
    
    displayInfo.textContent = infoText;
    displayInfo.classList.remove('text-muted');
    displayInfo.classList.add('text-primary');
    
    // Fechar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalSelecionarGuardasol'));
    modal.hide();
    
    mostrarAlerta(`Guarda-sol #${numero} selecionado!`, 'success');
}

/**
 * Filtra guarda-sóis por status
 */
function filtrarGuardasolStatus(status) {
    // Atualizar botões ativos
    document.querySelectorAll('#modalSelecionarGuardasol .btn-group button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Filtrar
    if (status === 'todos') {
        renderizarGuardasois(todosGuardasois);
    } else {
        const filtrados = todosGuardasois.filter(gs => gs.status === status);
        renderizarGuardasois(filtrados);
    }
}

// ==============================================
//  SISTEMA DE COMANDAS E GUARDA-SÓIS
// ==============================================

let modoVendaAtual = 'na_hora'; // 'na_hora' ou 'comanda'

/**
 * Altera modo de venda (Na Hora / Comanda)
 */
function alterarModoVenda(modo) {
    modoVendaAtual = modo;
    
    const sectionGuardasol = document.getElementById('sectionGuardasol');
    const formasPagamentoGrid = document.getElementById('formasPagamentoGrid');
    const resumoPagamento = document.getElementById('resumoPagamento');
    const botoesNaHora = document.getElementById('botoesNaHora');
    const botoesComanda = document.getElementById('botoesComanda');
    
    if (modo === 'comanda') {
        // Modo Comanda: mostrar guarda-sol, ocultar pagamento
        sectionGuardasol.style.display = 'block';
        formasPagamentoGrid.style.display = 'none';
        resumoPagamento.style.display = 'none';
        botoesNaHora.style.display = 'none';
        botoesComanda.style.display = 'block';
    } else {
        // Modo Na Hora: ocultar guarda-sol, mostrar pagamento
        sectionGuardasol.style.display = 'none';
        formasPagamentoGrid.style.display = 'grid';
        resumoPagamento.style.display = 'block';
        botoesNaHora.style.display = 'block';
        botoesComanda.style.display = 'none';
    }
}

/**
 * Adiciona itens do carrinho à comanda do guarda-sol
 */
async function adicionarItemsComanda() {
    if (carrinhoRapido.length === 0) {
        mostrarAlerta('Adicione produtos ao carrinho', 'warning');
        return;
    }
    
    if (!guardasolSelecionado) {
        mostrarAlerta('Selecione um guarda-sol antes de adicionar à comanda', 'warning');
        return;
    }
    
    // Preparar produtos para JSON
    const produtos = carrinhoRapido.map(item => ({
        produto_id: item.id,
        nome: item.nome,
        quantidade: item.quantidade,
        preco_unitario: item.preco,
        subtotal: item.preco * item.quantidade
    }));
    
    const subtotal = carrinhoRapido.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
    
    const formData = new FormData();
    formData.append('action', 'adicionarComanda');
    formData.append('guardasol_id', guardasolSelecionado.id);
    formData.append('produtos', JSON.stringify(produtos));
    formData.append('subtotal', subtotal);
    
    try {
        const response = await fetch('../src/Controllers/actions.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Mostrar mensagem com número do pedido
            const mensagem = data.data.pedido_numero 
                ? `✅ Items adicionados à comanda do Guarda-sol #${guardasolSelecionado.numero}!\n\n📝 Pedido criado: ${data.data.pedido_numero}\n\nO pedido foi enviado automaticamente para preparo na aba "Pedidos".`
                : `Items adicionados à comanda do Guarda-sol #${guardasolSelecionado.numero}!`;
            
            alert(mensagem);
            
            // Limpar carrinho
            carrinhoRapido = [];
            atualizarCarrinhoUI();
            
            // Atualizar informações do guarda-sol
            await atualizarInfoGuardasolSelecionado();
        } else {
            mostrarAlerta('Erro: ' + data.message, 'danger');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarAlerta('Erro de conexão', 'danger');
    }
}

/**
 * Fecha a comanda (muda status para aguardando pagamento)
 */
async function fecharComandaGuardasol() {
    if (!guardasolSelecionado) {
        mostrarAlerta('Selecione um guarda-sol', 'warning');
        return;
    }
    
    if (!confirm(`Deseja fechar a comanda do Guarda-sol #${guardasolSelecionado.numero}?\n\nO guarda-sol ficará aguardando pagamento.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'fecharComanda');
    formData.append('guardasol_id', guardasolSelecionado.id);
    
    try {
        const response = await fetch('../src/Controllers/actions.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarAlerta(`Comanda do Guarda-sol #${guardasolSelecionado.numero} fechada! Aguardando pagamento.`, 'success');
            
            // Limpar seleção
            guardasolSelecionado = null;
            document.getElementById('guardasolInfoDisplay').textContent = 'Clique para selecionar um guarda-sol';
            document.getElementById('guardasolInfoDisplay').classList.remove('text-primary');
            document.getElementById('guardasolInfoDisplay').classList.add('text-muted');
        } else {
            mostrarAlerta('Erro: ' + data.message, 'danger');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarAlerta('Erro de conexão', 'danger');
    }
}

/**
 * Pagar comanda imediatamente (abre modal de pagamento)
 */
async function pagarComandaAgora() {
    if (!guardasolSelecionado) {
        mostrarAlerta('Selecione um guarda-sol', 'warning');
        return;
    }
    
    // Verificar se o guarda-sol tem comanda
    if (guardasolSelecionado.status === 'vazio') {
        mostrarAlerta('Este guarda-sol não possui comandas para pagar', 'warning');
        return;
    }
    
    // Buscar total da comanda
    try {
        const response = await fetch(`../src/Controllers/actions.php?action=obterComandasGuardasol&guardasol_id=${guardasolSelecionado.id}`);
        const data = await response.json();
        
        console.log('Resposta completa:', data);
        console.log('Comandas:', data.data);
        
        if (data.success && data.data && data.data.length > 0) {
            const comandas = data.data;
            const totalComanda = comandas.reduce((sum, cmd) => sum + parseFloat(cmd.subtotal), 0);
            
            console.log('Total calculado:', totalComanda);
            
            // Abrir modal de pagamento com o total
            abrirModalPagamentoComanda(guardasolSelecionado, totalComanda, comandas);
        } else {
            const qtdComandas = data.data?.length || 0;
            mostrarAlerta(`Nenhuma comanda aberta para este guarda-sol (${qtdComandas} comandas encontradas). Status: ${guardasolSelecionado.status}`, 'warning');
        }
    } catch (error) {
        console.error('Erro ao buscar comandas:', error);
        mostrarAlerta('Erro ao buscar comandas', 'danger');
    }
}

/**
 * Abre modal para pagar comanda
 */
function abrirModalPagamentoComanda(guardasol, totalComanda, comandas) {
    // Criar modal dinâmico
    const modalHtml = `
        <div class="modal fade" id="modalPagarComanda" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-ocean text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-cash-coin"></i>
                            Pagar Comanda - Guarda-sol #${guardasol.numero}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Total da Comanda:</strong> R$ ${totalComanda.toFixed(2).replace('.', ',')}
                        </div>
                        
                        <h6>Formas de Pagamento</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <button class="btn btn-success w-100" onclick="finalizarPagamentoComanda('dinheiro')">
                                    <i class="bi bi-cash-coin"></i> Dinheiro
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-info w-100" onclick="finalizarPagamentoComanda('pix')">
                                    <i class="bi bi-qr-code"></i> PIX
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-primary w-100" onclick="finalizarPagamentoComanda('cartao')">
                                    <i class="bi bi-credit-card"></i> Cartão
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-warning w-100" onclick="finalizarPagamentoComanda('fiado')">
                                    <i class="bi bi-journal-text"></i> Fiado
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal anterior se existir
    const modalAnterior = document.getElementById('modalPagarComanda');
    if (modalAnterior) {
        modalAnterior.remove();
    }
    
    // Adicionar ao body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Armazenar dados temporariamente
    window.dadosPagamentoComanda = { guardasol, totalComanda, comandas };
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalPagarComanda'));
    modal.show();
}

/**
 * Finaliza pagamento da comanda
 */
async function finalizarPagamentoComanda(formaPagamento) {
    const dados = window.dadosPagamentoComanda;
    
    if (!dados) {
        mostrarAlerta('Erro ao processar pagamento', 'danger');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'finalizarPagamentoComanda');
    formData.append('guardasol_id', dados.guardasol.id);
    formData.append('forma_pagamento', formaPagamento);
    formData.append('total', dados.totalComanda);
    
    try {
        const response = await fetch('../src/Controllers/actions.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalPagarComanda'));
            modal.hide();
            
            // Mostrar sucesso
            mostrarAlerta(`Pagamento realizado! Guarda-sol #${dados.guardasol.numero} liberado.`, 'success');
            
            // Limpar seleção
            guardasolSelecionado = null;
            document.getElementById('guardasolInfoDisplay').textContent = 'Clique para selecionar um guarda-sol';
            document.getElementById('guardasolInfoDisplay').classList.remove('text-primary');
            document.getElementById('guardasolInfoDisplay').classList.add('text-muted');
            
            // Limpar dados temporários
            delete window.dadosPagamentoComanda;
        } else {
            mostrarAlerta('Erro: ' + data.message, 'danger');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarAlerta('Erro de conexão', 'danger');
    }
}

/**
 * Atualiza informações do guarda-sol selecionado
 */
async function atualizarInfoGuardasolSelecionado() {
    if (!guardasolSelecionado) return;
    
    try {
        const response = await fetch('../src/Controllers/actions.php?action=listarGuardasois');
        const data = await response.json();
        
        if (data.success) {
            const guardasolAtualizado = data.data.find(gs => gs.id === guardasolSelecionado.id);
            if (guardasolAtualizado) {
                guardasolSelecionado = guardasolAtualizado;
                
                // Atualizar display
                let infoText = `Guarda-sol #${guardasolAtualizado.numero} - ${guardasolAtualizado.status === 'vazio' ? 'Vazio' : guardasolAtualizado.status === 'ocupado' ? 'Ocupado' : 'Aguardando Pagamento'}`;
                
                if (guardasolAtualizado.cliente_nome) {
                    infoText += ` - ${guardasolAtualizado.cliente_nome}`;
                }
                
                if (guardasolAtualizado.total_consumido > 0) {
                    infoText += ` (R$ ${parseFloat(guardasolAtualizado.total_consumido).toFixed(2).replace('.', ',')})`;
                }
                
                document.getElementById('guardasolInfoDisplay').textContent = infoText;
            }
        }
    } catch (error) {
        console.error('Erro ao atualizar guarda-sol:', error);
    }
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
window.selecionarClienteFiado = selecionarClienteFiado;
window.filtrarClientesFiadoVenda = filtrarClientesFiadoVenda;
window.abrirCadastroRapidoCliente = abrirCadastroRapidoCliente;
window.salvarClienteRapido = salvarClienteRapido;
window.abrirModalGuardasol = abrirModalGuardasol;
window.selecionarGuardasol = selecionarGuardasol;
window.filtrarGuardasolStatus = filtrarGuardasolStatus;
window.alterarModoVenda = alterarModoVenda;
window.adicionarItemsComanda = adicionarItemsComanda;
window.fecharComandaGuardasol = fecharComandaGuardasol;
window.pagarComandaAgora = pagarComandaAgora;
window.finalizarPagamentoComanda = finalizarPagamentoComanda;

console.log('📦 Venda Rápida JS carregado');
