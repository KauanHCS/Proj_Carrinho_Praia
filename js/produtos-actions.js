// Funções para ações de produtos - Versão simplificada sem conflitos

function editarProduto(id) {
    console.log('Editando produto ID:', id);
    
    fetch(`actions.php?action=get_produto&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.produto) {
                const produto = data.data.produto;
                console.log('Produto carregado:', produto);
                
                // Preencher formulário
                document.getElementById('nomeProduto').value = produto.nome;
                document.getElementById('categoriaProduto').value = produto.categoria;
                document.getElementById('precoCompraProduto').value = produto.preco_compra || produto.preco_compra;
                document.getElementById('precoVendaProduto').value = produto.preco_venda || produto.preco;
                document.getElementById('quantidadeProduto').value = produto.quantidade;
                document.getElementById('limiteMinimo').value = produto.limite_minimo;
                document.getElementById('validadeProduto').value = produto.validade || '';
                document.getElementById('observacoesProduto').value = produto.observacoes || '';
                
                // Calcular margem após carregar os dados
                setTimeout(calcularMargemLucro, 100);
                
                // Alterar texto do botão
                const botaoSalvar = document.getElementById('salvarProduto');
                botaoSalvar.textContent = 'Atualizar Produto';
                botaoSalvar.setAttribute('data-produto-id', id);
                
                // Abrir modal
                const modal = new bootstrap.Modal(document.getElementById('modalNovoProduto'));
                modal.show();
                
                // Notificação visual sem popup
                if (typeof mostrarAlerta === 'function') {
                    mostrarAlerta('Produto carregado para edição', 'info', 3000);
                }
            } else {
                if (typeof mostrarAlerta === 'function') {
                    mostrarAlerta('Erro: ' + (data.message || 'Não foi possível carregar o produto'), 'danger', 5000);
                } else {
                    console.error('Erro ao carregar produto:', data.message);
                }
            }
        })
        .catch(error => {
            console.error('Erro ao carregar produto:', error);
            alert('❌ Erro ao carregar produto: ' + error.message);
        });
}

function excluirProduto(id) {
    console.log('Excluindo produto ID:', id);
    
    const botao = document.querySelector(`button[onclick="excluirProduto(${id})"]`);
    
    if (botao && !botao.classList.contains('confirmar-exclusao')) {
        // Primeiro clique - mostrar confirmação
        if (typeof mostrarAlerta === 'function') {
            mostrarAlerta('⚠️ Tem certeza? Clique em "Confirmar" ou "Cancelar"', 'warning', 8000);
        }
        
        // Criar container para os botões
        const containerBotoes = document.createElement('div');
        containerBotoes.className = 'btn-group';
        containerBotoes.setAttribute('data-produto-id', id);
        
        // Criar botão de cancelar
        const botaoCancelar = document.createElement('button');
        botaoCancelar.className = 'btn btn-sm btn-secondary';
        botaoCancelar.innerHTML = '<i class="bi bi-x-circle"></i> Cancelar';
        botaoCancelar.onclick = function() {
            cancelarExclusao(id);
        };
        
        // Modificar botão original para confirmar
        botao.classList.add('confirmar-exclusao', 'btn-danger');
        botao.classList.remove('btn-outline-danger');
        botao.innerHTML = '<i class="bi bi-check-circle"></i> Confirmar';
        
        // Inserir os botões
        containerBotoes.appendChild(botaoCancelar);
        containerBotoes.appendChild(botao.cloneNode(true));
        
        // Substituir o botão original pelo container
        botao.parentNode.replaceChild(containerBotoes, botao);
        
        return;
    }
    
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
            if (typeof mostrarAlerta === 'function') {
                mostrarAlerta('Produto excluído com sucesso!', 'success', 3000);
            }
            if (typeof notificarAcaoProduto === 'function') {
                notificarAcaoProduto('excluido', data.data.nome || 'Produto');
            }
            setTimeout(() => location.reload(), 1500);
        } else {
            if (typeof mostrarAlerta === 'function') {
                mostrarAlerta('Erro: ' + (data.message || 'Não foi possível excluir o produto'), 'danger', 5000);
            }
        }
    })
    .catch(error => {
        console.error('Erro ao excluir produto:', error);
        if (typeof mostrarAlerta === 'function') {
            mostrarAlerta('Erro ao excluir produto: ' + error.message, 'danger', 5000);
        }
    });
}

// Função para cancelar exclusão e restaurar estado original
function cancelarExclusao(id) {
    const container = document.querySelector(`[data-produto-id="${id}"]`);
    
    if (container) {
        // Criar botão original restaurado
        const botaoOriginal = document.createElement('button');
        botaoOriginal.className = 'btn btn-sm btn-outline-danger';
        botaoOriginal.innerHTML = '<i class="bi bi-trash"></i>';
        botaoOriginal.setAttribute('onclick', `excluirProduto(${id})`);
        
        // Substituir container pelos botão original
        container.parentNode.replaceChild(botaoOriginal, container);
        
        if (typeof mostrarAlerta === 'function') {
            mostrarAlerta('❌ Exclusão cancelada', 'info', 2000);
        }
    }
}

function reabastecerProduto(id) {
    console.log('Reabastecendo produto ID:', id);
    
    fetch(`actions.php?action=get_produto&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const produto = data.data.produto;
                console.log('Produto para reabastecimento:', produto);
                
                // Preencher campos do modal de reabastecimento
                document.getElementById('produtoReabastecimento').value = produto.id;
                document.getElementById('nomeReabastecimento').value = produto.nome;
                document.getElementById('quantidadeAtual').value = produto.quantidade;
                document.getElementById('quantidadeReabastecimento').value = '';
                
                // Abrir modal
                const modal = new bootstrap.Modal(document.getElementById('modalReabastecimento'));
                modal.show();
                
                // Notificação visual sem popup
                if (typeof mostrarAlerta === 'function') {
                    mostrarAlerta('Modal de reabastecimento aberto', 'info', 2000);
                }
            } else {
                if (typeof mostrarAlerta === 'function') {
                    mostrarAlerta('Erro: ' + (data.message || 'Não foi possível carregar o produto'), 'danger', 5000);
                } else {
                    console.error('Erro ao carregar produto:', data.message);
                }
            }
        })
        .catch(error => {
            console.error('Erro ao carregar produto:', error);
            alert('❌ Erro ao carregar produto: ' + error.message);
        });
}

// Função para salvar novo produto ou editar existente
function salvarNovoProduto() {
    const nome = document.getElementById('nomeProduto').value;
    const categoria = document.getElementById('categoriaProduto').value;
    const precoCompra = document.getElementById('precoCompraProduto').value;
    const precoVenda = document.getElementById('precoVendaProduto').value;
    const quantidade = document.getElementById('quantidadeProduto').value;
    const limiteMinimo = document.getElementById('limiteMinimo').value;
    const validade = document.getElementById('validadeProduto').value;
    const observacoes = document.getElementById('observacoesProduto').value;
    
    if (!nome || !categoria || !precoCompra || !precoVenda || !quantidade || !limiteMinimo) {
        if (typeof mostrarAlerta === 'function') {
            mostrarAlerta('Preencha todos os campos obrigatórios!', 'warning', 4000);
        }
        return;
    }
    
    // Validar se preço de venda é maior que preço de compra
    const compra = parseFloat(precoCompra);
    const venda = parseFloat(precoVenda);
    
    if (venda <= compra) {
        if (typeof mostrarAlerta === 'function') {
            mostrarAlerta('⚠️ Preço de venda deve ser maior que preço de compra!', 'danger', 5000);
        }
        return;
    }
    
    // Verificar se é edição ou novo produto
    const botaoSalvar = document.getElementById('salvarProduto');
    const produtoId = botaoSalvar.getAttribute('data-produto-id');
    const isEdicao = produtoId && produtoId !== '';
    
    const formData = new FormData();
    formData.append('action', isEdicao ? 'atualizar_produto' : 'salvar_produto');
    if (isEdicao) {
        formData.append('id', produtoId);
    }
    formData.append('nome', nome);
    formData.append('categoria', categoria);
    formData.append('preco_compra', precoCompra);
    formData.append('preco_venda', precoVenda);
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
            const mensagem = isEdicao ? 'Produto atualizado com sucesso!' : 'Produto cadastrado com sucesso!';
            const acao = isEdicao ? 'atualizado' : 'cadastrado';
            
            if (typeof mostrarAlerta === 'function') {
                mostrarAlerta(mensagem, 'success', 3000);
            }
            if (typeof notificarAcaoProduto === 'function') {
                notificarAcaoProduto(acao, nome);
            }
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoProduto'));
            modal.hide();
            
            // Limpar formulário e resetar estado
            document.getElementById('formNovoProduto').reset();
            botaoSalvar.textContent = 'Salvar Produto';
            botaoSalvar.removeAttribute('data-produto-id');
            
            // Atualizar página
            setTimeout(() => location.reload(), 1500);
        } else {
            if (typeof mostrarAlerta === 'function') {
                mostrarAlerta('Erro: ' + (data.message || 'Não foi possível salvar o produto'), 'danger', 5000);
            }
        }
    })
    .catch(error => {
        console.error('Erro ao salvar produto:', error);
        if (typeof mostrarAlerta === 'function') {
            mostrarAlerta('Erro ao salvar produto: ' + error.message, 'danger', 5000);
        }
    });
}

// Função para confirmar reabastecimento
function confirmarReabastecimento() {
    const id = document.getElementById('produtoReabastecimento').value;
    const quantidade = document.getElementById('quantidadeReabastecimento').value;
    
    if (!quantidade || quantidade <= 0) {
        if (typeof mostrarAlerta === 'function') {
            mostrarAlerta('Quantidade inválida!', 'warning', 4000);
        }
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
            if (typeof mostrarAlerta === 'function') {
                mostrarAlerta('Estoque reabastecido com sucesso!', 'success', 3000);
            }
            if (typeof notificarAcaoProduto === 'function') {
                notificarAcaoProduto('reabastecido', data.data.nome || 'Produto');
            }
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalReabastecimento'));
            modal.hide();
            
            // Atualizar página
            setTimeout(() => location.reload(), 1500);
        } else {
            if (typeof mostrarAlerta === 'function') {
                mostrarAlerta('Erro: ' + (data.message || 'Não foi possível reabastecer o estoque'), 'danger', 5000);
            }
        }
    })
    .catch(error => {
        console.error('Erro ao reabastecer:', error);
        if (typeof mostrarAlerta === 'function') {
            mostrarAlerta('Erro ao reabastecer: ' + error.message, 'danger', 5000);
        }
    });
}

// Inicializar eventos quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 produtos-actions.js carregado');
    
    // Event listener para salvar produto
    const btnSalvar = document.getElementById('salvarProduto');
    if (btnSalvar) {
        btnSalvar.addEventListener('click', function() {
            console.log('Botão salvar clicado');
            salvarNovoProduto();
        });
    }
    
    // Event listener para confirmar reabastecimento
    const btnReabastecer = document.getElementById('confirmarReabastecimento');
    if (btnReabastecer) {
        btnReabastecer.addEventListener('click', function() {
            console.log('Botão reabastecer clicado');
            confirmarReabastecimento();
        });
    }
});

console.log('✅ Script produtos-actions.js carregado!');
