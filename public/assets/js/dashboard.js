// ============================================
// DASHBOARD - Sistema de Métricas em Tempo Real
// ============================================

let graficoVendasHora = null;
let graficoFormasPagamento = null;
let intervalAtualizacao = null;
let metaDiaria = parseFloat(localStorage.getItem('metaDiaria')) || 500.00;

// ============================================
// INICIALIZAÇÃO
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard inicializado');
    inicializarDashboard();
    iniciarAtualizacaoAutomatica();
});

function inicializarDashboard() {
    carregarMetaDiaria();
    carregarDadosDashboard();
    carregarClima();
}

// ============================================
// ATUALIZAÇÃO AUTOMÁTICA
// ============================================

function iniciarAtualizacaoAutomatica() {
    // Atualiza a cada 30 segundos
    intervalAtualizacao = setInterval(() => {
        carregarDadosDashboard();
        piscarIndicadorAtualizacao();
    }, 30000);
}

function piscarIndicadorAtualizacao() {
    const indicador = document.getElementById('updateIndicator');
    indicador.style.opacity = '0.5';
    setTimeout(() => {
        indicador.style.opacity = '1';
    }, 300);
}

function atualizarDashboard() {
    console.log('Atualizando dashboard manualmente...');
    carregarDadosDashboard();
    carregarClima();
    piscarIndicadorAtualizacao();
}

// ============================================
// CARREGAMENTO DE DADOS PRINCIPAIS
// ============================================

async function carregarDadosDashboard() {
    try {
        const response = await fetch('../src/Controllers/actions.php?action=getDashboardMetrics');
        const data = await response.json();
        
        if (data.success) {
            atualizarKPIs(data.data);
            atualizarGraficoVendasHora(data.data.vendas_por_hora);
            atualizarTopProdutos(data.data.top_produtos);
            atualizarFormasPagamento(data.data.formas_pagamento);
            atualizarComparacoes(data.data.comparacoes);
            atualizarHorarioPico(data.data.horario_pico);
        } else {
            console.error('Erro ao carregar métricas:', data.message);
        }
    } catch (error) {
        console.error('Erro ao buscar dados do dashboard:', error);
    }
}

// ============================================
// ATUALIZAÇÃO DE KPIs
// ============================================

function atualizarKPIs(data) {
    // Faturamento do Dia
    document.getElementById('faturamentoDia').textContent = formatarMoeda(data.faturamento_hoje);
    atualizarTrend('trendFaturamento', data.comparacao_ontem_faturamento);
    
    // Ticket Médio
    document.getElementById('ticketMedio').textContent = formatarMoeda(data.ticket_medio);
    atualizarTrend('trendTicket', data.comparacao_ontem_ticket);
    
    // Número de Atendimentos
    document.getElementById('numAtendimentos').textContent = data.num_atendimentos;
    atualizarTrendNumero('trendAtendimentos', data.comparacao_ontem_atendimentos);
    
    // Atualizar Meta
    document.getElementById('metaAtual').textContent = formatarMoeda(data.faturamento_hoje);
    atualizarProgressoMeta(data.faturamento_hoje);
}

function atualizarTrend(elementId, percentual) {
    const element = document.getElementById(elementId);
    const valor = parseFloat(percentual);
    
    const icone = valor >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
    const classe = valor >= 0 ? 'text-success' : 'text-danger';
    const sinal = valor >= 0 ? '+' : '';
    
    element.innerHTML = `
        <i class="bi ${icone}"></i>
        <span>${sinal}${valor.toFixed(1)}%</span> vs ontem
    `;
    element.className = `kpi-trend ${classe}`;
}

function atualizarTrendNumero(elementId, diferenca) {
    const element = document.getElementById(elementId);
    const valor = parseInt(diferenca);
    
    const icone = valor >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
    const classe = valor >= 0 ? 'text-success' : 'text-danger';
    const sinal = valor > 0 ? '+' : '';
    
    element.innerHTML = `
        <i class="bi ${icone}"></i>
        <span>${sinal}${valor}</span> vs ontem
    `;
    element.className = `kpi-trend ${classe}`;
}

// ============================================
// GRÁFICO DE VENDAS POR HORA
// ============================================

function atualizarGraficoVendasHora(dados) {
    const ctx = document.getElementById('graficoVendasHora');
    
    if (!ctx) return;
    
    const labels = dados.map(d => d.hora + 'h');
    const valores = dados.map(d => parseFloat(d.total));
    
    if (graficoVendasHora) {
        graficoVendasHora.destroy();
    }
    
    graficoVendasHora = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Faturamento (R$)',
                data: valores,
                borderColor: '#0dcaf0',
                backgroundColor: 'rgba(13, 202, 240, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#0dcaf0',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'R$ ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(0);
                        }
                    }
                }
            }
        }
    });
}

// ============================================
// TOP PRODUTOS
// ============================================

function atualizarTopProdutos(produtos) {
    const container = document.getElementById('topProdutos');
    
    if (!produtos || produtos.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                <p>Nenhuma venda registrada hoje</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    produtos.forEach((produto, index) => {
        const posicao = index + 1;
        const medalha = posicao === 1 ? '🥇' : posicao === 2 ? '🥈' : posicao === 3 ? '🥉' : `${posicao}º`;
        const progressWidth = (produto.quantidade / produtos[0].quantidade) * 100;
        
        html += `
            <div class="produto-item">
                <div class="produto-posicao">${medalha}</div>
                <div class="produto-info">
                    <div class="produto-nome">${produto.nome}</div>
                    <div class="produto-stats">
                        <span class="badge bg-primary">${produto.quantidade} unidades</span>
                        <span class="badge bg-success">${formatarMoeda(produto.total)}</span>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: ${progressWidth}%"></div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ============================================
// FORMAS DE PAGAMENTO
// ============================================

function atualizarFormasPagamento(dados) {
    const ctx = document.getElementById('graficoFormasPagamento');
    
    if (!ctx) return;
    
    // Se não houver dados, mostrar mensagem
    if (!dados || dados.length === 0) {
        ctx.parentElement.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                <p>Nenhuma venda hoje</p>
            </div>
        `;
        return;
    }
    
    const labels = dados.map(d => d.forma_pagamento);
    const valores = dados.map(d => parseFloat(d.total));
    
    // Função para obter cor baseada no nome (case-insensitive)
    function getCor(forma) {
        const formaLower = forma.toLowerCase();
        if (formaLower.includes('dinheiro')) return '#198754';  // Verde
        if (formaLower.includes('pix')) return '#0dcaf0';       // Azul claro
        if (formaLower.includes('cart')) return '#0d6efd';      // Azul
        if (formaLower.includes('fiado')) return '#ffc107';     // Amarelo
        return '#6c757d'; // Cinza para outros
    }
    
    const backgroundColors = labels.map(label => getCor(label));
    
    // Cores mais claras para hover
    const hoverColors = labels.map(label => {
        const cor = getCor(label);
        return cor + 'dd'; // Adiciona opacidade no hover
    });
    
    if (graficoFormasPagamento) {
        graficoFormasPagamento.destroy();
    }
    
    graficoFormasPagamento = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: valores,
                backgroundColor: backgroundColors,
                hoverBackgroundColor: hoverColors,
                borderWidth: 3,
                borderColor: '#fff',
                hoverBorderWidth: 4,
                hoverBorderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1,
            plugins: {
                legend: {
                    position: window.innerWidth > 768 ? 'right' : 'bottom',
                    align: 'center',
                    labels: {
                        padding: 15,
                        font: {
                            size: 14,
                            weight: '600',
                            family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                        },
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 12,
                        boxHeight: 12,
                        color: '#333',
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const meta = chart.getDatasetMeta(0);
                                    const style = meta.controller.getStyle(i);
                                    const value = data.datasets[0].data[i];
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percent = ((value / total) * 100).toFixed(1);
                                    
                                    return {
                                        text: `${label} (${percent}%)`,
                                        fillStyle: style.backgroundColor,
                                        strokeStyle: style.borderColor,
                                        lineWidth: style.borderWidth,
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const valor = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentual = ((valor / total) * 100).toFixed(1);
                            return `${label}: R$ ${valor.toFixed(2)} (${percentual}%)`;
                        }
                    }
                }
            }
        }
    });
}

// ============================================
// COMPARAÇÕES
// ============================================

function atualizarComparacoes(comparacoes) {
    // Comparação com Ontem
    document.getElementById('compOntemFaturamento').textContent = formatarMoeda(comparacoes.ontem.faturamento);
    atualizarBadgeComparacao('compOntemDiff', comparacoes.ontem.diff_faturamento);
    
    document.getElementById('compOntemAtendimentos').textContent = comparacoes.ontem.atendimentos;
    atualizarBadgeComparacaoNumero('compOntemAtendDiff', comparacoes.ontem.diff_atendimentos);
    
    document.getElementById('compOntemTicket').textContent = formatarMoeda(comparacoes.ontem.ticket_medio);
    atualizarBadgeComparacao('compOntemTicketDiff', comparacoes.ontem.diff_ticket);
    
    // Comparação com Semana Passada
    document.getElementById('compSemanaFaturamento').textContent = formatarMoeda(comparacoes.semana_passada.faturamento);
    atualizarBadgeComparacao('compSemanaDiff', comparacoes.semana_passada.diff_faturamento);
    
    document.getElementById('compSemanaAtendimentos').textContent = comparacoes.semana_passada.atendimentos;
    atualizarBadgeComparacaoNumero('compSemanaAtendDiff', comparacoes.semana_passada.diff_atendimentos);
    
    document.getElementById('compSemanaTicket').textContent = formatarMoeda(comparacoes.semana_passada.ticket_medio);
    atualizarBadgeComparacao('compSemanaTicketDiff', comparacoes.semana_passada.diff_ticket);
}

function atualizarBadgeComparacao(elementId, percentual) {
    const element = document.getElementById(elementId);
    const valor = parseFloat(percentual);
    
    const classe = valor >= 0 ? 'bg-success' : 'bg-danger';
    const sinal = valor > 0 ? '+' : '';
    
    element.className = `badge ${classe}`;
    element.textContent = `${sinal}${valor.toFixed(1)}%`;
}

function atualizarBadgeComparacaoNumero(elementId, diferenca) {
    const element = document.getElementById(elementId);
    const valor = parseInt(diferenca);
    
    const classe = valor >= 0 ? 'bg-success' : 'bg-danger';
    const sinal = valor > 0 ? '+' : '';
    
    element.className = `badge ${classe}`;
    element.textContent = `${sinal}${valor}`;
}

// ============================================
// HORÁRIO DE PICO
// ============================================

function atualizarHorarioPico(dados) {
    if (dados && dados.hora) {
        document.getElementById('horarioPico').textContent = dados.hora + 'h';
        document.getElementById('vendasPico').textContent = dados.quantidade;
    } else {
        document.getElementById('horarioPico').textContent = '--:--';
        document.getElementById('vendasPico').textContent = '0';
    }
}

// ============================================
// META DO DIA
// ============================================

function carregarMetaDiaria() {
    document.getElementById('metaDia').textContent = metaDiaria.toFixed(2);
}

function atualizarProgressoMeta(faturamentoAtual) {
    const percentual = (faturamentoAtual / metaDiaria) * 100;
    const percentualLimitado = Math.min(percentual, 100);
    const restante = Math.max(metaDiaria - faturamentoAtual, 0);
    
    const progressBar = document.getElementById('progressMeta');
    const progressTexto = document.getElementById('progressTexto');
    const metaRestante = document.getElementById('metaRestante');
    
    progressBar.style.width = percentualLimitado + '%';
    progressBar.setAttribute('aria-valuenow', percentualLimitado);
    progressTexto.textContent = percentualLimitado.toFixed(1) + '%';
    metaRestante.textContent = formatarMoeda(restante);
    
    // Mudar cor da barra conforme progresso
    progressBar.classList.remove('bg-danger', 'bg-warning', 'bg-success');
    
    if (percentual < 30) {
        progressBar.classList.add('bg-danger');
    } else if (percentual < 70) {
        progressBar.classList.add('bg-warning');
    } else {
        progressBar.classList.add('bg-success');
    }
    
    // Celebração ao atingir meta
    if (percentual >= 100 && !sessionStorage.getItem('metaCelebrada')) {
        celebrarMeta();
        sessionStorage.setItem('metaCelebrada', 'true');
    }
}

function editarMeta() {
    const modal = new bootstrap.Modal(document.getElementById('modalEditarMeta'));
    document.getElementById('inputMeta').value = metaDiaria.toFixed(2);
    modal.show();
}

function salvarMeta() {
    const novoValor = parseFloat(document.getElementById('inputMeta').value);
    
    if (isNaN(novoValor) || novoValor <= 0) {
        alert('Por favor, insira um valor válido maior que zero.');
        return;
    }
    
    metaDiaria = novoValor;
    localStorage.setItem('metaDiaria', metaDiaria);
    
    carregarMetaDiaria();
    carregarDadosDashboard(); // Atualiza o progresso
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarMeta'));
    modal.hide();
    
    // Feedback visual
    const metaCard = document.querySelector('.meta-content');
    metaCard.style.transform = 'scale(1.05)';
    setTimeout(() => {
        metaCard.style.transform = 'scale(1)';
    }, 200);
}

function celebrarMeta() {
    // Efeito de confete ou animação quando meta é atingida
    console.log('🎉 META ATINGIDA! Parabéns!');
    
    // Você pode adicionar uma biblioteca de confete aqui se desejar
    // Como: https://github.com/catdad/canvas-confetti
}

// ============================================
// CLIMA (OpenWeatherMap)
// ============================================

async function carregarClima() {
    // API Key do OpenWeatherMap (gratuita)
    // IMPORTANTE: Cadastre-se em openweathermap.org e substitua pela sua chave
    const apiKey = 'SUA_API_KEY_AQUI'; // Substitua pela sua chave
    const cidade = 'Praia Grande';
    const estado = 'SP';
    const pais = 'BR';
    
    // Se não tiver API key configurada, mostrar mensagem
    if (apiKey === 'SUA_API_KEY_AQUI') {
        document.getElementById('temperatura').textContent = '--°C';
        document.getElementById('climaDescricao').textContent = 'Configure API key';
        return;
    }
    
    try {
        const url = `https://api.openweathermap.org/data/2.5/weather?q=${cidade},${estado},${pais}&appid=${apiKey}&units=metric&lang=pt_br`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.cod === 200) {
            const temperatura = Math.round(data.main.temp);
            const descricao = data.weather[0].description;
            const iconeClima = data.weather[0].main;
            
            document.getElementById('temperatura').textContent = temperatura + '°C';
            document.getElementById('climaDescricao').textContent = descricao.charAt(0).toUpperCase() + descricao.slice(1);
            
            // Atualizar ícone baseado no clima
            atualizarIconeClima(iconeClima);
        } else {
            console.error('Erro ao buscar clima:', data.message);
            document.getElementById('temperatura').textContent = '--°C';
            document.getElementById('climaDescricao').textContent = 'Erro ao carregar';
        }
    } catch (error) {
        console.error('Erro ao buscar dados do clima:', error);
        document.getElementById('temperatura').textContent = '--°C';
        document.getElementById('climaDescricao').textContent = 'Sem conexão';
    }
}

function atualizarIconeClima(condicao) {
    const iconeElement = document.getElementById('climaIcon');
    const icones = {
        'Clear': 'bi-sun-fill',
        'Clouds': 'bi-cloud-sun-fill',
        'Rain': 'bi-cloud-rain-fill',
        'Drizzle': 'bi-cloud-drizzle-fill',
        'Thunderstorm': 'bi-cloud-lightning-rain-fill',
        'Snow': 'bi-snow',
        'Mist': 'bi-cloud-haze',
        'Fog': 'bi-cloud-fog-fill'
    };
    
    const icone = icones[condicao] || 'bi-cloud-sun';
    iconeElement.innerHTML = `<i class="bi ${icone}"></i>`;
}

// ============================================
// UTILITÁRIOS
// ============================================

function formatarMoeda(valor) {
    return 'R$ ' + parseFloat(valor).toFixed(2).replace('.', ',');
}

// Limpar celebração ao trocar de dia
function verificarNovaData() {
    const dataAtual = new Date().toDateString();
    const dataArmazenada = sessionStorage.getItem('dataAtual');
    
    if (dataArmazenada !== dataAtual) {
        sessionStorage.removeItem('metaCelebrada');
        sessionStorage.setItem('dataAtual', dataAtual);
    }
}

verificarNovaData();

// ============================================
// LIMPEZA AO SAIR
// ============================================

window.addEventListener('beforeunload', function() {
    if (intervalAtualizacao) {
        clearInterval(intervalAtualizacao);
    }
});
