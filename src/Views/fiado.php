<div class="fiado-container">
    <!-- Header com Dashboard de Resumo -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="fiado-header">
                <div>
                    <h3 class="mb-1">
                        <i class="bi bi-journal-text"></i>
                        Fiado / Caderneta
                    </h3>
                    <p class="text-muted mb-0">Controle de vendas fiadas e pagamentos</p>
                </div>
                <div class="fiado-actions">
                    <button class="btn btn-primary" onclick="abrirModalNovoCliente()">
                        <i class="bi bi-person-plus"></i>
                        Novo Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs do Fiado -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="kpi-card kpi-warning">
                <div class="kpi-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Total a Receber</div>
                    <div class="kpi-value" id="totalReceber">R$ 0,00</div>
                    <div class="kpi-trend text-muted">
                        <span id="qtdClientes">0</span> clientes
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="kpi-card kpi-danger">
                <div class="kpi-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Clientes Inadimplentes</div>
                    <div class="kpi-value" id="clientesInadimplentes">0</div>
                    <div class="kpi-trend text-danger">
                        R$ <span id="valorInadimplente">0,00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="kpi-card kpi-success">
                <div class="kpi-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Recebido Hoje</div>
                    <div class="kpi-value" id="recebidoHoje">R$ 0,00</div>
                    <div class="kpi-trend text-success">
                        <span id="qtdPagamentosHoje">0</span> pagamentos
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="kpi-card kpi-info">
                <div class="kpi-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Vendas Fiadas (Mês)</div>
                    <div class="kpi-value" id="vendasMes">R$ 0,00</div>
                    <div class="kpi-trend text-info">
                        <span id="qtdVendasMes">0</span> vendas
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros e Busca -->
    <div class="row mb-3">
        <div class="col-lg-6 col-md-12 mb-2">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="buscaCliente" placeholder="Buscar por nome ou telefone..." onkeyup="filtrarClientes()">
            </div>
        </div>
        <div class="col-lg-6 col-md-12 mb-2">
            <div class="btn-group w-100" role="group">
                <button type="button" class="btn btn-outline-primary active" data-filtro="todos" onclick="mudarFiltro('todos')">
                    Todos
                </button>
                <button type="button" class="btn btn-outline-warning" data-filtro="devedores" onclick="mudarFiltro('devedores')">
                    Com Dívida
                </button>
                <button type="button" class="btn btn-outline-danger" data-filtro="inadimplentes" onclick="mudarFiltro('inadimplentes')">
                    Inadimplentes
                </button>
                <button type="button" class="btn btn-outline-success" data-filtro="quitados" onclick="mudarFiltro('quitados')">
                    Quitados
                </button>
            </div>
        </div>
    </div>

    <!-- Lista de Clientes -->
    <div class="row" id="listaClientes">
        <div class="col-12 text-center py-5">
            <i class="bi bi-hourglass-split" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="text-muted mt-3">Carregando clientes...</p>
        </div>
    </div>
</div>

<!-- Modal: Novo Cliente -->
<div class="modal fade" id="modalNovoCliente" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-ocean text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i>
                    Novo Cliente Fiado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoCliente">
                    <div class="mb-3">
                        <label for="fiadoNomeCliente" class="form-label">Nome Completo *</label>
                        <input type="text" class="form-control" id="fiadoNomeCliente" required>
                    </div>
                    <div class="mb-3">
                        <label for="fiadoTelefoneCliente" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="fiadoTelefoneCliente" placeholder="(13) 99999-9999">
                    </div>
                    <div class="mb-3">
                        <label for="fiadoCpfCliente" class="form-label">CPF</label>
                        <input type="text" class="form-control" id="fiadoCpfCliente" placeholder="000.000.000-00">
                    </div>
                    <div class="mb-3">
                        <label for="fiadoEnderecoCliente" class="form-label">Endereço</label>
                        <textarea class="form-control" id="fiadoEnderecoCliente" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="fiadoLimiteCredito" class="form-label">Limite de Crédito</label>
                        <input type="number" class="form-control" id="fiadoLimiteCredito" value="500.00" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="fiadoObservacoesCliente" class="form-label">Observações</label>
                        <textarea class="form-control" id="fiadoObservacoesCliente" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarNovoCliente()">
                    <i class="bi bi-check-circle"></i>
                    Cadastrar Cliente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Registrar Pagamento -->
<div class="modal fade" id="modalRegistrarPagamento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-tropical text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cash-coin"></i>
                    Registrar Pagamento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pagamentoClienteId">
                <div class="cliente-info-pagamento mb-3 p-3 bg-light rounded">
                    <h6 id="pagamentoClienteNome">Cliente</h6>
                    <div class="d-flex justify-content-between">
                        <span>Saldo Devedor:</span>
                        <strong class="text-danger" id="pagamentoSaldoDevedor">R$ 0,00</strong>
                    </div>
                </div>
                
                <form id="formRegistrarPagamento">
                    <div class="mb-3">
                        <label for="valorPagamento" class="form-label">Valor do Pagamento *</label>
                        <input type="number" class="form-control form-control-lg" id="valorPagamento" step="0.01" min="0.01" required>
                        <div class="form-text">
                            <button type="button" class="btn btn-sm btn-link p-0" onclick="preencherValorTotal()">
                                Pagar valor total
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="formaPagamentoPagamento" class="form-label">Forma de Pagamento</label>
                        <select class="form-select" id="formaPagamentoPagamento">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="PIX">PIX</option>
                            <option value="Cartão">Cartão</option>
                            <option value="Transferência">Transferência</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="fiadoObservacoesPagamento" class="form-label">Observações</label>
                        <textarea class="form-control" id="fiadoObservacoesPagamento" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="salvarPagamento()">
                    <i class="bi bi-check-circle"></i>
                    Confirmar Pagamento
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Histórico do Cliente -->
<div class="modal fade" id="modalHistoricoCliente" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-gradient-ocean text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history"></i>
                    Histórico do Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="historicoClienteId">
                <div class="cliente-info-historico mb-3 p-3 bg-light rounded">
                    <h6 id="historicoClienteNome">Cliente</h6>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Telefone:</small>
                            <div id="historicoTelefone">-</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Saldo Devedor:</small>
                            <div class="text-danger fw-bold" id="historicoSaldo">R$ 0,00</div>
                        </div>
                    </div>
                </div>
                
                <div id="historicoConteudo">
                    <div class="text-center py-4">
                        <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
                        <p class="text-muted">Carregando histórico...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
