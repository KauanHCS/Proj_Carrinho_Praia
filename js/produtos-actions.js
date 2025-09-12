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
                document.getElementById('precoProduto').value = produto.preco;
                document.getElementById('quantidadeProduto').value = produto.quantidade;
                document.getElementById('limiteMinimo').value = produto.limite_minimo;
                document.getElementById('validadeProduto').value = produto.validade || '';
                document.getElementById('observacoesProduto').value = produto.observacoes || '';
                
                // Alterar texto do botão
                const botaoSalvar = document.getElementById('salvarProduto');
                botaoSalvar.textContent = 'Atualizar Produto';
                botaoSalvar.setAttribute('data-produto-id', id);
                
                // Abrir modal
                const modal = new bootstrap.Modal(document.getElementById('modalNovoProduto'));
                modal.show();
                
                alert('✅ Produto carregado para edição!');
            } else {
                alert('❌ Erro: ' + (data.message || 'Não foi possível carregar o produto'));
            }
        })
        .catch(error => {
            console.error('Erro ao carregar produto:', error);
            alert('❌ Erro ao carregar produto: ' + error.message);
        });
}

function excluirProduto(id) {
    console.log('Excluindo produto ID:', id);
    
    if (confirm('⚠️ Tem certeza que deseja excluir este produto?')) {
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
                alert('✅ Produto excluído com sucesso!');
                location.reload();
            } else {
                alert('❌ Erro: ' + (data.message || 'Não foi possível excluir o produto'));
            }
        })
        .catch(error => {
            console.error('Erro ao excluir produto:', error);
            alert('❌ Erro ao excluir produto: ' + error.message);
        });
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
                
                alert('✅ Modal de reabastecimento aberto!');
            } else {
                alert('❌ Erro: ' + (data.message || 'Não foi possível carregar o produto'));
            }
        })
        .catch(error => {
            console.error('Erro ao carregar produto:', error);
            alert('❌ Erro ao carregar produto: ' + error.message);
        });
}

// Função para salvar novo produto
function salvarNovoProduto() {
    const nome = document.getElementById('nomeProduto').value;
    const categoria = document.getElementById('categoriaProduto').value;
    const preco = document.getElementById('precoProduto').value;
    const quantidade = document.getElementById('quantidadeProduto').value;
    const limiteMinimo = document.getElementById('limiteMinimo').value;
    const validade = document.getElementById('validadeProduto').value;
    const observacoes = document.getElementById('observacoesProduto').value;
    
    if (!nome || !categoria || !preco || !quantidade || !limiteMinimo) {
        alert('❌ Preencha todos os campos obrigatórios!');
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
            alert('✅ Produto cadastrado com sucesso!');
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoProduto'));
            modal.hide();
            
            // Limpar formulário
            document.getElementById('formNovoProduto').reset();
            
            // Atualizar página
            location.reload();
        } else {
            alert('❌ Erro: ' + (data.message || 'Não foi possível salvar o produto'));
        }
    })
    .catch(error => {
        console.error('Erro ao salvar produto:', error);
        alert('❌ Erro ao salvar produto: ' + error.message);
    });
}

// Função para confirmar reabastecimento
function confirmarReabastecimento() {
    const id = document.getElementById('produtoReabastecimento').value;
    const quantidade = document.getElementById('quantidadeReabastecimento').value;
    
    if (!quantidade || quantidade <= 0) {
        alert('❌ Quantidade inválida!');
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
            alert('✅ Estoque reabastecido com sucesso!');
            
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalReabastecimento'));
            modal.hide();
            
            // Atualizar página
            location.reload();
        } else {
            alert('❌ Erro: ' + (data.message || 'Não foi possível reabastecer o estoque'));
        }
    })
    .catch(error => {
        console.error('Erro ao reabastecer:', error);
        alert('❌ Erro ao reabastecer: ' + error.message);
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
