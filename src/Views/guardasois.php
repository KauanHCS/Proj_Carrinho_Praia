<div class="guardasois-admin-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="bi bi-umbrella-fill text-primary"></i>
                                <strong>Gerenciar Guarda-sóis</strong>
                            </h4>
                            <p class="text-muted mb-0">Configure a quantidade de guarda-sóis do seu carrinho</p>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-primary btn-lg" onclick="abrirModalConfigurarQuantidade()">
                                <i class="bi bi-gear-fill"></i>
                                Configurar Quantidade
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4" id="statsGuardasois">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total</p>
                            <h3 class="mb-0" id="statTotal">0</h3>
                        </div>
                        <div class="stat-icon bg-primary">
                            <i class="bi bi-umbrella-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Vazios</p>
                            <h3 class="mb-0 text-success" id="statVazios">0</h3>
                        </div>
                        <div class="stat-icon bg-success">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Ocupados</p>
                            <h3 class="mb-0 text-warning" id="statOcupados">0</h3>
                        </div>
                        <div class="stat-icon bg-warning">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Aguardando Pag.</p>
                            <h3 class="mb-0 text-danger" id="statAguardando">0</h3>
                        </div>
                        <div class="stat-icon bg-danger">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group w-100" role="group">
                <button type="button" class="btn btn-outline-secondary active" onclick="filtrarStatusAdmin('todos')">
                    <i class="bi bi-grid-3x3-gap-fill"></i> Todos
                </button>
                <button type="button" class="btn btn-outline-success" onclick="filtrarStatusAdmin('vazio')">
                    <i class="bi bi-check-circle"></i> Vazios
                </button>
                <button type="button" class="btn btn-outline-warning" onclick="filtrarStatusAdmin('ocupado')">
                    <i class="bi bi-hourglass-split"></i> Ocupados
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="filtrarStatusAdmin('aguardando_pagamento')">
                    <i class="bi bi-cash-coin"></i> Aguardando Pagamento
                </button>
            </div>
        </div>
    </div>

    <!-- Grid de Guarda-sóis -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div id="gridGuardasoisAdmin" class="guardasois-admin-grid">
                        <div class="text-center py-5">
                            <i class="bi bi-hourglass-split" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3">Carregando guarda-sóis...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Configurar Quantidade -->
<div class="modal fade" id="modalConfigurarQuantidade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-ocean text-white">
                <h5 class="modal-title">
                    <i class="bi bi-gear-fill"></i>
                    Configurar Quantidade de Guarda-sóis
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Importante:</strong> Configure quantos guarda-sóis você tem disponível no seu carrinho de praia.
                </div>
                
                <div class="mb-3">
                    <label for="quantidadeGuardasois" class="form-label">
                        <strong>Quantidade de Guarda-sóis</strong>
                    </label>
                    <input type="number" 
                           class="form-control form-control-lg text-center" 
                           id="quantidadeGuardasois" 
                           min="1" 
                           max="100" 
                           value="10"
                           style="font-size: 2rem; font-weight: bold;">
                    <small class="text-muted">Mínimo: 1 | Máximo: 100</small>
                </div>

                <div class="alert alert-warning" id="avisoAlteracao" style="display: none;">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Atenção:</strong> Você já possui guarda-sóis cadastrados. 
                    Se diminuir a quantidade, os guarda-sóis excedentes serão removidos.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-lg" onclick="salvarQuantidadeGuardasois()">
                    <i class="bi bi-check-circle"></i>
                    Confirmar e Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detalhes do Guarda-sol -->
<div class="modal fade" id="modalDetalhesGuardasol" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-gradient-tropical text-white">
                <h5 class="modal-title">
                    <i class="bi bi-umbrella-fill"></i>
                    Detalhes do Guarda-sol <span id="detalheNumero">#0</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Informações principais -->
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="text-muted">Status:</label>
                        <div id="detalheStatus" class="fs-5 fw-bold"></div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted">Total Consumido:</label>
                        <div id="detalheTotal" class="fs-4 fw-bold text-primary"></div>
                    </div>
                </div>

                <div class="row mb-3" id="detalheClienteContainer" style="display: none;">
                    <div class="col-12">
                        <label class="text-muted">Cliente:</label>
                        <div id="detalheCliente" class="fs-5"></div>
                    </div>
                </div>

                <hr>

                <!-- Comandas abertas -->
                <h6><i class="bi bi-receipt"></i> Comandas Abertas</h6>
                <div id="listaComandasDetalhes">
                    <p class="text-muted">Nenhuma comanda aberta</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-danger" id="btnFecharContaModal" onclick="fecharContaGuardasolModal()" style="display: none;">
                    <i class="bi bi-cash-coin"></i>
                    Fechar Conta
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.guardasois-admin-container {
    padding: 20px;
}

.stat-card {
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
}

.guardasois-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    padding: 10px 0;
}

.guardasol-admin-card {
    border: 3px solid #e9ecef;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    position: relative;
    overflow: hidden;
}

.guardasol-admin-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: #6c757d;
    transition: all 0.3s ease;
}

.guardasol-admin-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.guardasol-admin-card.status-vazio {
    border-color: #198754;
}

.guardasol-admin-card.status-vazio::before {
    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
}

.guardasol-admin-card.status-ocupado {
    border-color: #ffc107;
}

.guardasol-admin-card.status-ocupado::before {
    background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);
}

.guardasol-admin-card.status-aguardando_pagamento {
    border-color: #dc3545;
}

.guardasol-admin-card.status-aguardando_pagamento::before {
    background: linear-gradient(135deg, #dc3545 0%, #e4606d 100%);
}

.guardasol-numero-admin {
    font-size: 3.5rem;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 10px;
    line-height: 1;
}

.guardasol-status-badge-admin {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 12px;
}

.guardasol-status-badge-admin.badge-vazio {
    background: #198754;
    color: white;
}

.guardasol-status-badge-admin.badge-ocupado {
    background: #ffc107;
    color: #000;
}

.guardasol-status-badge-admin.badge-aguardando {
    background: #dc3545;
    color: white;
}

.guardasol-cliente-admin {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 10px;
    font-weight: 500;
}

.guardasol-valor-admin {
    font-size: 1.2rem;
    color: #0066cc;
    font-weight: 700;
    margin-top: 10px;
}

.guardasol-icon-admin {
    font-size: 3rem;
    margin-bottom: 12px;
    opacity: 0.8;
}

@media (max-width: 768px) {
    .guardasois-admin-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .guardasol-numero-admin {
        font-size: 2.5rem;
    }
}
</style>

<script src="assets/js/guardasois.js"></script>
