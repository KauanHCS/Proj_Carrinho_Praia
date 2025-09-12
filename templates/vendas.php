<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cart-plus"></i> Registro de Vendas</span>
                <span class="badge bg-secondary">Hoje: <span id="dataAtual"></span></span>
            </div>
            <div class="card-body">
                <!-- Search and Filter Controls -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchProdutos" placeholder="Buscar produtos...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" id="filtroCategoria">
                            <option value="">Todas as categorias</option>
                            <option value="bebida">Bebidas</option>
                            <option value="comida">Comidas</option>
                            <option value="acessorio">Acessórios</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>
                </div>
                <div class="row" id="produtosVenda">
                    <?php
                    $conn = getConnection();
                    $sql = "SELECT * FROM produtos WHERE quantidade > 0 ORDER BY nome";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $lowStock = $row["quantidade"] <= $row["limite_minimo"] ? ' low-stock' : '';
                            $categoria = htmlspecialchars($row["categoria"]);
                            $nome = htmlspecialchars($row["nome"]);
                            $nomeJs = addslashes($row["nome"]);
                            
                            echo '<div class="col-md-4 col-sm-6 mb-3">';
                            echo '<button class="btn btn-light w-100 product-btn' . $lowStock . '" ';
                            echo 'data-categoria="' . $categoria . '" ';
                            echo 'data-nome="' . $nome . '" ';
                            echo 'onclick="adicionarAoCarrinho(' . $row["id"] . ', \'' . $nomeJs . '\', ' . $row["preco"] . ', ' . $row["quantidade"] . ')">';
                            echo '<div class="text-start">';
                            echo '<strong>' . $nome . '</strong><br>';
                            echo '<small class="text-muted">R$ ' . number_format($row["preco"], 2, ',', '.') . ' | ' . $row["quantidade"] . ' unid.</small><br>';
                            echo '<span class="badge bg-secondary">' . ucfirst($categoria) . '</span>';
                            if ($lowStock) {
                                echo ' <span class="badge bg-warning">Estoque baixo</span>';
                            }
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
                    <h5>Total: R$ <span id="totalCarrinho">0,00</span></h5>
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