// Versão simplificada da função de filtro
function filtrarProdutosSimple() {
    var searchTerm = document.getElementById('searchProdutos').value.toLowerCase();
    var selectedCategory = document.getElementById('filtroCategoria').value;
    var produtos = document.querySelectorAll('#produtosVenda .col-md-4, #produtosVenda .col-sm-6');
    
    var produtosVisiveis = 0;
    
    for (var i = 0; i < produtos.length; i++) {
        var produto = produtos[i];
        var button = produto.querySelector('.product-btn');
        
        if (button) {
            var strongElement = button.querySelector('strong');
            var nome = strongElement ? strongElement.textContent.toLowerCase() : '';
            var categoria = button.getAttribute('data-categoria') || '';
            
            var matchesSearch = searchTerm === '' || nome.indexOf(searchTerm) !== -1;
            var matchesCategory = selectedCategory === '' || categoria === selectedCategory;
            
            if (matchesSearch && matchesCategory) {
                produto.style.display = '';
                produtosVisiveis++;
            } else {
                produto.style.display = 'none';
            }
        }
    }
    
    // Mostrar/ocultar mensagem de "nenhum produto encontrado"
    var container = document.getElementById('produtosVenda');
    var mensagemVazia = container.querySelector('.mensagem-vazia');
    
    if (produtosVisiveis === 0) {
        if (!mensagemVazia) {
            mensagemVazia = document.createElement('div');
            mensagemVazia.className = 'col-12 text-center text-muted mensagem-vazia mt-4';
            mensagemVazia.innerHTML = '<p><i class="bi bi-search"></i> Nenhum produto encontrado</p>';
            container.appendChild(mensagemVazia);
        }
        mensagemVazia.style.display = '';
    } else {
        if (mensagemVazia) {
            mensagemVazia.style.display = 'none';
        }
    }
}

// Inicializar filtros de forma segura
function inicializarFiltrosSimple() {
    var searchInput = document.getElementById('searchProdutos');
    var categoryFilter = document.getElementById('filtroCategoria');
    
    if (searchInput) {
        searchInput.oninput = filtrarProdutosSimple;
    }
    
    if (categoryFilter) {
        categoryFilter.onchange = filtrarProdutosSimple;
    }
}

// Auto-inicializar quando disponível
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarFiltrosSimple);
} else {
    inicializarFiltrosSimple();
}
