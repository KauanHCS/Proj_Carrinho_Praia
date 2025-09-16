<!-- Modal Novo Produto -->
<div class="modal fade" id="modalNovoProduto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Novo Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoProduto">
                    <div class="mb-3">
                        <label class="form-label">Nome do Produto</label>
                        <input type="text" class="form-control" id="nomeProduto" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoria</label>
                        <select class="form-select" id="categoriaProduto" required>
                            <option value="bebida">Bebida</option>
                            <option value="comida">Comida</option>
                            <option value="acessorio">Acessório</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-currency-dollar"></i> Preço de Compra (R$)</label>
                                <input type="number" class="form-control" id="precoCompraProduto" step="0.01" required placeholder="Quanto você pagou">
                                <small class="form-text text-muted">Quanto você pagou por este produto</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-tag"></i> Preço de Venda (R$)</label>
                                <input type="number" class="form-control" id="precoVendaProduto" step="0.01" required placeholder="Quanto vai vender">
                                <small class="form-text text-muted">Preço que você vai vender</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Quantidade Inicial</label>
                                <input type="number" class="form-control" id="quantidadeProduto" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Margem de Lucro</label>
                                <div class="form-control-plaintext fw-bold text-success" id="margemLucroCalculada">
                                    <i class="bi bi-calculator"></i> Será calculada automaticamente
                                </div>
                                <small class="form-text text-muted">Calculada automaticamente com base nos preços</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Limite Mínimo</label>
                                <input type="number" class="form-control" id="limiteMinimo" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Validade (opcional)</label>
                                <input type="date" class="form-control" id="validadeProduto">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoesProduto" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="salvarProduto">Salvar Produto</button>
            </div>
        </div>
    </div>
</div>

<script>
// Função para calcular margem de lucro
function calcularMargemLucro() {
    const precoCompra = parseFloat(document.getElementById('precoCompraProduto').value) || 0;
    const precoVenda = parseFloat(document.getElementById('precoVendaProduto').value) || 0;
    const margemDiv = document.getElementById('margemLucroCalculada');
    
    if (precoCompra > 0 && precoVenda > 0) {
        const margem = ((precoVenda - precoCompra) / precoCompra) * 100;
        const lucroAbsoluto = precoVenda - precoCompra;
        
        let cor = 'text-danger';
        let icone = 'bi-arrow-down';
        
        if (margem > 0) {
            cor = margem > 50 ? 'text-success' : 'text-warning';
            icone = 'bi-arrow-up';
        } else if (margem === 0) {
            cor = 'text-secondary';
            icone = 'bi-dash';
        }
        
        margemDiv.innerHTML = `
            <i class="bi ${icone} ${cor}"></i> 
            <span class="${cor}">
                ${margem.toFixed(2)}% 
                <small>(+R$ ${lucroAbsoluto.toFixed(2)})</small>
            </span>
        `;
        
        // Validar se é um negócio viável
        if (margem < 0) {
            margemDiv.innerHTML += '<br><small class="text-danger">⚠️ Prejuízo! Preço de venda menor que compra</small>';
        } else if (margem < 20) {
            margemDiv.innerHTML += '<br><small class="text-warning">💡 Margem baixa, considere aumentar o preço</small>';
        } else if (margem > 100) {
            margemDiv.innerHTML += '<br><small class="text-info">🚀 Excelente margem de lucro!</small>';
        }
        
    } else {
        margemDiv.innerHTML = '<i class="bi bi-calculator"></i> Digite os preços para calcular';
    }
}

// Adicionar event listeners quando o documento carregar
document.addEventListener('DOMContentLoaded', function() {
    const precoCompraInput = document.getElementById('precoCompraProduto');
    const precoVendaInput = document.getElementById('precoVendaProduto');
    
    if (precoCompraInput && precoVendaInput) {
        precoCompraInput.addEventListener('input', calcularMargemLucro);
        precoVendaInput.addEventListener('input', calcularMargemLucro);
    }
});
</script>

<!-- Modal Reabastecimento -->
<div class="modal fade" id="modalReabastecimento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-repeat"></i> Reabastecer Estoque</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formReabastecimento">
                    <input type="hidden" id="produtoReabastecimento">
                    <div class="mb-3">
                        <label class="form-label">Produto</label>
                        <input type="text" class="form-control" id="nomeReabastecimento" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantidade Atual</label>
                        <input type="text" class="form-control" id="quantidadeAtual" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantidade para Adicionar</label>
                        <input type="number" class="form-control" id="quantidadeReabastecimento" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmarReabastecimento">Reabastecer</button>
            </div>
        </div>
    </div>
</div>