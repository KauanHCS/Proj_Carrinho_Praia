<div class="dashboard-container">
    <!-- Header do Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-header">
                <div>
                    <h3 class="mb-1">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </h3>
                    <p class="text-muted mb-0">Visão geral do seu negócio em tempo real</p>
                </div>
                <div class="dashboard-actions">
                    <span class="badge bg-success" id="updateIndicator">
                        <i class="bi bi-circle-fill blink"></i>
                        Ao vivo
                    </span>
                    <button class="btn btn-sm btn-outline-primary" onclick="atualizarDashboard()">
                        <i class="bi bi-arrow-clockwise"></i>
                        Atualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Principais -->
    <div class="row g-3 mb-4">
        <!-- Faturamento do Dia -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="kpi-card kpi-primary">
                <div class="kpi-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Faturamento Hoje</div>
                    <div class="kpi-value" id="faturamentoDia">R$ 0,00</div>
                    <div class="kpi-trend" id="trendFaturamento">
                        <i class="bi bi-arrow-up"></i>
                        <span>+0%</span> vs ontem
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Médio -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="kpi-card kpi-success">
                <div class="kpi-icon">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Ticket Médio</div>
                    <div class="kpi-value" id="ticketMedio">R$ 0,00</div>
                    <div class="kpi-trend" id="trendTicket">
                        <i class="bi bi-arrow-up"></i>
                        <span>+0%</span> vs ontem
                    </div>
                </div>
            </div>
        </div>

        <!-- Nº de Atendimentos -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="kpi-card kpi-info">
                <div class="kpi-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Atendimentos Hoje</div>
                    <div class="kpi-value" id="numAtendimentos">0</div>
                    <div class="kpi-trend" id="trendAtendimentos">
                        <i class="bi bi-arrow-up"></i>
                        <span>+0</span> vs ontem
                    </div>
                </div>
            </div>
        </div>

        <!-- Clima -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="kpi-card kpi-warning">
                <div class="kpi-icon" id="climaIcon">
                    <i class="bi bi-cloud-sun"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Clima - Praia Grande</div>
                    <div class="kpi-value" id="temperatura">--°C</div>
                    <div class="kpi-trend" id="climaDescricao">
                        Carregando...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda Linha: Gráficos e Informações -->
    <div class="row g-3 mb-4">
        <!-- Vendas por Hora -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-gradient-ocean text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i>
                        Vendas por Hora - Hoje
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoVendasHora" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Meta do Dia -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-gradient-tropical text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bullseye"></i>
                        Meta do Dia
                    </h5>
                </div>
                <div class="card-body">
                    <div class="meta-content">
                        <div class="meta-valor mb-3">
                            <div class="text-muted mb-1">Meta: R$ <span id="metaDia">500,00</span></div>
                            <div class="fs-3 fw-bold text-primary" id="metaAtual">R$ 0,00</div>
                        </div>
                        
                        <div class="progress mb-2" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                 role="progressbar" 
                                 id="progressMeta" 
                                 style="width: 0%"
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span class="fw-bold" id="progressTexto">0%</span>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <div class="text-muted small">Falta: <strong id="metaRestante">R$ 500,00</strong></div>
                        </div>
                        
                        <div class="mt-3">
                            <button class="btn btn-sm btn-outline-primary w-100" onclick="editarMeta()">
                                <i class="bi bi-pencil"></i>
                                Editar Meta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terceira Linha: Produtos e Formas de Pagamento -->
    <div class="row g-3 mb-4">
        <!-- Produtos Mais Vendidos -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-gradient-ocean text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-trophy"></i>
                        Top 5 Produtos - Hoje
                    </h5>
                </div>
                <div class="card-body">
                    <div id="topProdutos" class="top-produtos-list">
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
                            <p>Carregando produtos...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formas de Pagamento -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-gradient-tropical text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-wallet2"></i>
                        Formas de Pagamento - Hoje
                    </h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="padding: 1.5rem; min-height: 300px;">
                    <canvas id="graficoFormasPagamento"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quarta Linha: Comparações -->
    <div class="row g-3">
        <!-- Comparação com Ontem -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-day"></i>
                        Comparação com Ontem
                    </h6>
                </div>
                <div class="card-body">
                    <div class="comparison-item">
                        <span>Faturamento:</span>
                        <strong id="compOntemFaturamento">R$ 0,00</strong>
                        <span class="badge bg-success" id="compOntemDiff">0%</span>
                    </div>
                    <div class="comparison-item">
                        <span>Atendimentos:</span>
                        <strong id="compOntemAtendimentos">0</strong>
                        <span class="badge bg-success" id="compOntemAtendDiff">0</span>
                    </div>
                    <div class="comparison-item">
                        <span>Ticket Médio:</span>
                        <strong id="compOntemTicket">R$ 0,00</strong>
                        <span class="badge bg-success" id="compOntemTicketDiff">0%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparação com Semana Passada -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-week"></i>
                        Mesma Data - Semana Passada
                    </h6>
                </div>
                <div class="card-body">
                    <div class="comparison-item">
                        <span>Faturamento:</span>
                        <strong id="compSemanaFaturamento">R$ 0,00</strong>
                        <span class="badge bg-success" id="compSemanaDiff">0%</span>
                    </div>
                    <div class="comparison-item">
                        <span>Atendimentos:</span>
                        <strong id="compSemanaAtendimentos">0</strong>
                        <span class="badge bg-success" id="compSemanaAtendDiff">0</span>
                    </div>
                    <div class="comparison-item">
                        <span>Ticket Médio:</span>
                        <strong id="compSemanaTicket">R$ 0,00</strong>
                        <span class="badge bg-success" id="compSemanaTicketDiff">0%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Horário de Pico -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-alarm"></i>
                        Horário de Pico
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-3">
                        <div class="pico-icon mb-2">
                            <i class="bi bi-fire text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <div class="fs-4 fw-bold text-primary" id="horarioPico">--:--</div>
                        <div class="text-muted">Maior movimento</div>
                        <div class="mt-2">
                            <strong id="vendasPico">0</strong> vendas
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Meta -->
<div class="modal fade" id="modalEditarMeta" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-bullseye"></i>
                    Editar Meta do Dia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="inputMeta" class="form-label">Valor da Meta (R$)</label>
                    <input type="number" class="form-control form-control-lg" id="inputMeta" step="0.01" min="0" placeholder="500.00">
                </div>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Dica:</strong> Defina uma meta realista baseada nas suas vendas médias.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarMeta()">
                    <i class="bi bi-check-circle"></i>
                    Salvar Meta
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Script específico do Dashboard -->
<script src="assets/js/dashboard.js"></script>
