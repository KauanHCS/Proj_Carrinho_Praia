<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cart-plus"></i> Registro de Vendas</span>
                <span class="badge bg-secondary">Hoje: <span id="dataAtual"></span></span>
            </div>
            <div class="card-body">
                <div class="row" id="produtosVenda">
                    <?php
                    $conn = getConnection();
                    $sql = "SELECT * FROM produtos WHERE quantidade > 0 ORDER BY nome";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $lowStock = $row["quantidade"] <= $row["limite_minimo"] ? 'low' : '';
                            echo '<div class="col-md-4 col-sm-6">';
                            echo '<button class="btn btn-light w-100 product-btn ' . $lowStock . '" onclick="adicionarAoCarrinho(' . $row["id"] . ', \'' . $row["nome"] . '\', ' . $row["preco"] . ', ' . $row["quantidade"] . ')">';
                            echo '<div class="text-start">';
                            echo '<strong>' . $row["nome"] . '</strong><br>';
                            echo '<small>R$ ' . number_format($row["preco"], 2, ',', '.') . ' | ' . $row["quantidade"] . ' unid.</small>';
                            echo '</div>';
                            echo '</button>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="col-12"><p class="text-center text-muted">Nenhum produto cadastrado</p></div>';
                    }
                    $conn->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-receipt"></i> Carrinho de Compras
            </div>
            <div class="card-body">
                <div id="itensCarrinho">
                    <p class="text-muted">Nenhum item adicionado</p>
                </div>
                <div class="mt-3">
                    <h5>Total:: R$ <span id="totalCarrinho">0,00</span></h5>
                </div>
                <div class="mt-3">
                    <label for="formaPagamento" class="form-label">Forma de Pagamento</label>
                    <select class="form-select" id="formaPagamento">
                        <option value="dinheiro">Dinheiro</option>
                        <option value="pix">PIX</option>
                        <option value="cartao">Cartão</option>
                    </select>
                </div>
                <div class="mt-3" id="divTroco" style="display: none;">
                    <label for="valorPago" class="form-label">Valor Pago</label>
                    <input type="number" class="form-control" id="valorPago" placeholder="R$ 0,00">
                    <div class="mt-2">
                        <strong>Troco: R$ <span id="valorTroco">0,00</span></strong>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-success w-100" id="finalizarVenda">
                        <i class="bi bi-check-circle"></i> Finalizar Venda
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>