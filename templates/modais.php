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
                                <label class="form-label">Preço de Venda (R$)</label>
                                <input type="number" class="form-control" id="precoProduto" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Quantidade Inicial</label>
                                <input type="number" class="form-control" id="quantidadeProduto" required>
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