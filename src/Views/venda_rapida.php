<div class="venda-rapida-container">
    <!-- Header com Informações -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="bi bi-lightning-charge-fill text-warning"></i>
                                <strong>VENDA RÁPIDA</strong>
                            </h5>
                            <small class="text-muted">Modo expresso para atendimento ágil</small>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-success fs-6 px-3 py-2">
                                <i class="bi bi-clock"></i>
                                <span id="horaAtual"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modo de Venda -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-body p-3">
                    <h6 class="mb-2 text-center">
                        <i class="bi bi-toggles"></i>
                        <strong>Modo de Venda</strong>
                    </h6>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="modoVenda" id="modoNaHora" value="na_hora" checked autocomplete="off" onchange="alterarModoVenda('na_hora')">
                        <label class="btn btn-outline-success" for="modoNaHora">
                            <i class="bi bi-cash-coin"></i>
                            Pagar na Hora
                        </label>
                        
                        <input type="radio" class="btn-check" name="modoVenda" id="modoComanda" value="comanda" autocomplete="off" onchange="alterarModoVenda('comanda')">
                        <label class="btn btn-outline-primary" for="modoComanda">
                            <i class="bi bi-receipt"></i>
                            Adicionar à Comanda
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seleção de Guarda-sol (aparece apenas no modo Comanda) -->
    <div class="row mb-3" id="sectionGuardasol" style="display: none;">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <i class="bi bi-umbrella-fill text-primary"></i>
                                <strong>Guarda-sol</strong>
                            </h6>
                            <small id="guardasolInfoDisplay" class="text-muted">
                                Clique para selecionar um guarda-sol
                            </small>
                        </div>
                        <button class="btn btn-outline-primary" onclick="abrirModalGuardasol()">
                            <i class="bi bi-search"></i>
                            Selecionar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Coluna de Produtos (Esquerda) -->
        <div class="col-lg-8 col-md-7">
            <!-- Busca Rápida -->
            <div class="card mb-3">
                <div class="card-body p-3">
                    <div class="input-icon-wrapper">
                        <i class="bi bi-search"></i>
                        <input 
                            type="text" 
                            class="form-control form-control-lg" 
                            id="buscaRapida" 
                            placeholder="Digite para buscar produto..."
                            autocomplete="off">
                    </div>
                </div>
            </div>

            <!-- Filtro por Categoria -->
            <div class="categoria-tabs mb-3">
                <button class="btn-categoria active" data-categoria="todos" onclick="filtrarCategoria('todos')">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    Todos
                </button>
                <button class="btn-categoria" data-categoria="bebida" onclick="filtrarCategoria('bebida')">
                    <i class="bi bi-cup-straw"></i>
                    Bebidas
                </button>
                <button class="btn-categoria" data-categoria="comida" onclick="filtrarCategoria('comida')">
                    <i class="bi bi-egg-fried"></i>
                    Comidas
                </button>
                <button class="btn-categoria" data-categoria="acessorio" onclick="filtrarCategoria('acessorio')">
                    <i class="bi bi-bag"></i>
                    Acessórios
                </button>
                <button class="btn-categoria" data-categoria="outros" onclick="filtrarCategoria('outros')">
                    <i class="bi bi-three-dots"></i>
                    Outros
                </button>
            </div>

            <!-- Grid de Produtos -->
            <div class="produtos-grid" id="produtosGrid">
                <?php
                require_once __DIR__ . '/../../config/database.php';
                $conn = getConnection();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if ($usuarioId) {
                    $sql = "SELECT * FROM produtos WHERE quantidade > 0 AND usuario_id = ? ORDER BY nome";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $usuarioId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        while($produto = $result->fetch_assoc()) {
                            $lowStock = $produto["quantidade"] <= $produto["limite_minimo"];
                            $categoria = htmlspecialchars($produto["categoria"]);
                            $nome = htmlspecialchars($produto["nome"]);
                            $preco = number_format($produto["preco_venda"], 2, ',', '.');
                            
                            // Ícone por categoria
                            $icone = match($categoria) {
                                'bebida' => '🥤',
                                'comida' => '🍔',
                                'acessorio' => '🎒',
                                default => '📦'
                            };
                            
                            echo '<button class="produto-btn" ';
                            echo 'data-id="' . $produto["id"] . '" ';
                            echo 'data-nome="' . htmlspecialchars($produto["nome"], ENT_QUOTES) . '" ';
                            echo 'data-preco="' . $produto["preco_venda"] . '" ';
                            echo 'data-estoque="' . $produto["quantidade"] . '" ';
                            echo 'data-categoria="' . $categoria . '" ';
                            echo ($lowStock ? 'data-low-stock="true" ' : '');
                            echo 'onclick="adicionarProdutoRapidoFromButton(this)">';
                            
                            echo '<div class="produto-icon">' . $icone . '</div>';
                            echo '<div class="produto-nome">' . $nome . '</div>';
                            echo '<div class="produto-preco">R$ ' . $preco . '</div>';
                            echo '<div class="produto-estoque">';
                            echo '<i class="bi bi-box"></i> ' . $produto["quantidade"] . ' un.';
                            if ($lowStock) {
                                echo ' <span class="badge badge-warning badge-pulse ms-1">BAIXO</span>';
                            }
                            echo '</div>';
                            echo '</button>';
                        }
                    } else {
                        echo '<div class="col-12 text-center py-5">';
                        echo '<i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>';
                        echo '<p class="text-muted mt-3">Nenhum produto disponível</p>';
                        echo '</div>';
                    }
                }
                $conn->close();
                ?>
            </div>
        </div>

        <!-- Coluna do Carrinho (Direita) -->
        <div class="col-lg-4 col-md-5">
            <div class="carrinho-rapido sticky-top">
                <div class="card">
                    <div class="card-header bg-gradient-ocean text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-cart3"></i>
                            Carrinho
                        </h5>
                    </div>
                    
                    <div class="card-body p-0">
                        <!-- Lista de Itens -->
                        <div class="carrinho-itens" id="carrinhoItens">
                            <div class="carrinho-vazio">
                                <i class="bi bi-cart-x"></i>
                                <p>Nenhum item adicionado</p>
                                <small class="text-muted">Clique nos produtos para adicionar</small>
                            </div>
                        </div>

                        <!-- Resumo -->
                        <div class="carrinho-resumo">
                            <div class="resumo-linha">
                                <span>Subtotal:</span>
                                <strong class="text-ocean" id="subtotalValor">R$ 0,00</strong>
                            </div>
                            <div class="resumo-linha-total">
                                <span>TOTAL:</span>
                                <strong class="text-success fs-3" id="totalValor">R$ 0,00</strong>
                            </div>
                            <div class="resumo-info">
                                <small class="text-muted">
                                    <i class="bi bi-box"></i>
                                    <span id="totalItens">0</span> item(s)
                                </small>
                            </div>
                        </div>
                        
                        <!-- Nome do Cliente (Opcional) -->
                        <div class="p-3 border-top">
                            <label for="nomeClienteVenda" class="form-label mb-2">
                                <i class="bi bi-person"></i>
                                Nome do Cliente <small class="text-muted">(opcional)</small>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="nomeClienteVenda" 
                                placeholder="Digite o nome do cliente..."
                                autocomplete="off">
                        </div>
                    </div>

                    <!-- Pagamento Misto -->
                    <div class="card-footer p-3 bg-light" id="sectionPagamento">
                        <h6 class="mb-3 text-center">
                            <i class="bi bi-wallet2"></i>
                            Formas de Pagamento
                        </h6>
                        
                        <!-- Opções de Pagamento -->
                        <div class="formas-pagamento-grid mb-3" id="formasPagamentoGrid">
                            <!-- Dinheiro -->
                            <div class="forma-pagamento-item" onclick="toggleFormaPagamentoBox(this, 'dinheiro')">
                                <div class="forma-header">
                                    <div class="forma-icon">
                                        <i class="bi bi-cash-coin text-success"></i>
                                    </div>
                                    <span class="forma-label">Dinheiro</span>
                                    <input class="form-check-input" type="checkbox" id="checkDinheiro">
                                </div>
                                <input type="number" class="form-control form-control-sm" id="valorDinheiro" placeholder="R$ 0,00" step="0.01" min="0" disabled oninput="calcularPagamentoMisto()" onchange="calcularPagamentoMisto()" onclick="event.stopPropagation()">
                            </div>
                            
                            <!-- PIX -->
                            <div class="forma-pagamento-item" onclick="toggleFormaPagamentoBox(this, 'pix')">
                                <div class="forma-header">
                                    <div class="forma-icon">
                                        <i class="bi bi-qr-code text-info"></i>
                                    </div>
                                    <span class="forma-label">PIX</span>
                                    <input class="form-check-input" type="checkbox" id="checkPix">
                                </div>
                                <input type="number" class="form-control form-control-sm" id="valorPix" placeholder="R$ 0,00" step="0.01" min="0" disabled oninput="calcularPagamentoMisto()" onchange="calcularPagamentoMisto()" onclick="event.stopPropagation()">
                            </div>
                            
                            <!-- Cartão -->
                            <div class="forma-pagamento-item" onclick="toggleFormaPagamentoBox(this, 'cartao')">
                                <div class="forma-header">
                                    <div class="forma-icon">
                                        <i class="bi bi-credit-card text-primary"></i>
                                    </div>
                                    <span class="forma-label">Cartão</span>
                                    <input class="form-check-input" type="checkbox" id="checkCartao">
                                </div>
                                <input type="number" class="form-control form-control-sm" id="valorCartao" placeholder="R$ 0,00" step="0.01" min="0" disabled oninput="calcularPagamentoMisto()" onchange="calcularPagamentoMisto()" onclick="event.stopPropagation()">
                            </div>
                            
                            <!-- Fiado -->
                            <div class="forma-pagamento-item" onclick="toggleFormaPagamentoBox(this, 'fiado')">
                                <div class="forma-header">
                                    <div class="forma-icon">
                                        <i class="bi bi-journal-text text-warning"></i>
                                    </div>
                                    <span class="forma-label">Fiado</span>
                                    <input class="form-check-input" type="checkbox" id="checkFiado">
                                </div>
                                <input type="number" class="form-control form-control-sm" id="valorFiado" placeholder="R$ 0,00" step="0.01" min="0" disabled oninput="calcularPagamentoMisto()" onchange="calcularPagamentoMisto()" onclick="event.stopPropagation()">
                            </div>
                        </div>
                        
                        <!-- Resumo de Pagamento (apenas no modo "Na Hora") -->
                        <div class="pagamento-resumo mb-3 p-2 bg-white rounded" id="resumoPagamento">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Total da Venda:</small>
                                <strong class="text-ocean" id="totalVendaPagamento">R$ 0,00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Total Pago:</small>
                                <strong class="text-success" id="totalPago">R$ 0,00</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Restante:</small>
                                <strong class="text-danger" id="valorRestante">R$ 0,00</strong>
                            </div>
                        </div>
                        
                        <!-- Botões para Modo "Na Hora" -->
                        <div id="botoesNaHora">
                            <button class="btn btn-success btn-lg w-100 mb-2" onclick="finalizarVendaMista()" id="btnFinalizarVenda">
                                <i class="bi bi-check-circle"></i>
                                Finalizar Venda
                            </button>
                            <button class="btn btn-outline-danger w-100" onclick="limparCarrinhoRapido()">
                                <i class="bi bi-trash"></i>
                                Limpar Carrinho
                            </button>
                        </div>
                        
                        <!-- Botões para Modo "Comanda" -->
                        <div id="botoesComanda" style="display: none;">
                            <button class="btn btn-primary btn-lg w-100 mb-2" onclick="adicionarItemsComanda()">
                                <i class="bi bi-receipt-cutoff"></i>
                                Adicionar à Comanda
                            </button>
                            <button class="btn btn-warning btn-lg w-100 mb-2" onclick="fecharComandaGuardasol()">
                                <i class="bi bi-check2-square"></i>
                                Fechar Comanda
                            </button>
                            <button class="btn btn-danger btn-lg w-100 mb-2" onclick="pagarComandaAgora()">
                                <i class="bi bi-cash-coin"></i>
                                Pagar Comanda Agora
                            </button>
                            <button class="btn btn-outline-danger w-100" onclick="limparCarrinhoRapido()">
                                <i class="bi bi-trash"></i>
                                Limpar Carrinho
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Venda -->
<div class="modal fade" id="modalConfirmacaoVenda" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-tropical text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle"></i>
                    Venda Realizada!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="success-icon mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h4 class="mb-3">Venda Finalizada com Sucesso!</h4>
                <div class="venda-info">
                    <p class="mb-2">
                        <strong>Total:</strong> 
                        <span class="text-success fs-4" id="modalTotalVenda">R$ 0,00</span>
                    </p>
                    <p class="mb-2">
                        <strong>Forma de Pagamento:</strong> 
                        <span class="badge badge-primary" id="modalFormaPagamento">-</span>
                    </p>
                    <p class="text-muted mb-0">
                        <small>
                            <i class="bi bi-clock"></i>
                            <span id="modalHoraVenda"></span>
                        </small>
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-lg w-100" onclick="novaVendaRapida()">
                    <i class="bi bi-plus-circle"></i>
                    Nova Venda
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Selecionar Cliente Fiado -->
<div class="modal fade" id="modalSelecionarClienteFiado" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-gradient-ocean text-white">
                <h5 class="modal-title">
                    <i class="bi bi-journal-text"></i>
                    Selecionar Cliente para Venda Fiada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Busca Rápida -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="buscaClienteFiadoVenda" placeholder="Buscar cliente por nome ou telefone..." onkeyup="filtrarClientesFiadoVenda()">
                    </div>
                </div>
                
                <!-- Botão Novo Cliente -->
                <div class="mb-3">
                    <button class="btn btn-outline-primary w-100" onclick="abrirCadastroRapidoCliente()">
                        <i class="bi bi-person-plus"></i>
                        Cadastrar Novo Cliente Rapidamente
                    </button>
                </div>
                
                <!-- Lista de Clientes -->
                <div id="listaClientesFiadoVenda" class="clientes-list">
                    <div class="text-center py-4">
                        <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
                        <p class="text-muted">Carregando clientes...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cadastro Rápido de Cliente -->
<div class="modal fade" id="modalCadastroRapidoCliente" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-tropical text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i>
                    Cadastro Rápido de Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCadastroRapidoCliente">
                    <div class="mb-3">
                        <label for="rapidoNomeCliente" class="form-label">Nome Completo *</label>
                        <input type="text" class="form-control" id="rapidoNomeCliente" required>
                    </div>
                    <div class="mb-3">
                        <label for="rapidoTelefoneCliente" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="rapidoTelefoneCliente" placeholder="(13) 99999-9999">
                    </div>
                    <div class="mb-3">
                        <label for="rapidoLimiteCredito" class="form-label">Limite de Crédito</label>
                        <input type="number" class="form-control" id="rapidoLimiteCredito" value="500.00" step="0.01" min="0">
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <small>Você poderá completar os dados do cliente depois na aba Fiado/Caderneta.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarClienteRapido()">
                    <i class="bi bi-check-circle"></i>
                    Cadastrar e Continuar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Selecionar Guarda-sol -->
<div class="modal fade" id="modalSelecionarGuardasol" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-gradient-ocean text-white">
                <h5 class="modal-title">
                    <i class="bi bi-umbrella-fill"></i>
                    Selecionar Guarda-sol
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Filtros de Status -->
                <div class="mb-3">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-secondary active" onclick="filtrarGuardasolStatus('todos')">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                            Todos
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="filtrarGuardasolStatus('vazio')">
                            <i class="bi bi-check-circle"></i>
                            Vazios
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="filtrarGuardasolStatus('ocupado')">
                            <i class="bi bi-hourglass-split"></i>
                            Ocupados
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="filtrarGuardasolStatus('aguardando_pagamento')">
                            <i class="bi bi-cash-coin"></i>
                            Aguardando Pag.
                        </button>
                    </div>
                </div>
                
                <!-- Grid de Guarda-sóis -->
                <div id="gridGuardasois" class="guardasois-grid">
                    <div class="text-center py-4">
                        <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
                        <p class="text-muted">Carregando guarda-sóis...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
