<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="container-fluid">
    <div class="row">
        <!-- Mapa -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="bi bi-map"></i> Mapa Interativo</h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-primary" id="btnLocalizar" onclick="obterLocalizacao()">
                            <i class="bi bi-crosshair"></i> Minha Localização
                        </button>
                        <button class="btn btn-info" id="btnMarcar" onclick="toggleModoMarcacao()">
                            <i class="bi bi-pin-map"></i> Marcar no Mapa
                        </button>
                        <button class="btn btn-success" onclick="limparRotas()">
                            <i class="bi bi-signpost-2"></i> Limpar Rotas
                        </button>
                        <button class="btn btn-warning" onclick="limparMapa()">
                            <i class="bi bi-eraser"></i> Limpar Tudo
                        </button>
                        <button class="btn btn-secondary" onclick="corrigirMapa()" title="Corrigir renderização do mapa">
                            <i class="bi bi-arrow-repeat"></i> Corrigir
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 500px; width: 100%;"></div>
                </div>
                <div class="card-footer">
                    <div id="infoLocalizacao">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> 
                            Clique em "Minha Localização" para se localizar ou ative "Marcar no Mapa" para adicionar pontos.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Painel Lateral -->
        <div class="col-md-4">
            <!-- Informações da Localização -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5><i class="bi bi-geo-alt"></i> Localização Atual</h5>
                </div>
                <div class="card-body">
                    <div class="row" id="coordenadas" style="display: none;">
                        <div class="col-12 mb-2">
                            <div class="card bg-light">
                                <div class="card-body text-center py-2">
                                    <div id="coordenadasAtuais"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body text-center py-2">
                                    <div id="precisaoGPS"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center" id="semLocalizacao">
                        <button class="btn btn-success btn-sm w-100" id="btnSalvar" onclick="salvarLocalizacao()" disabled>
                            <i class="bi bi-bookmark-plus"></i> Salvar Localização Atual
                        </button>
                        <small class="text-muted d-block mt-2">Primeiro obtenha sua localização</small>
                    </div>
                </div>
            </div>
            
            <!-- Pontos Salvos -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5><i class="bi bi-bookmark-star"></i> Pontos Salvos</h5>
                </div>
                <div class="card-body">
                    <div id="pontosSalvos">
                        <p class="text-muted">Nenhum ponto salvo ainda.</p>
                        <small class="text-muted">Salve pontos importantes clicando no mapa ou obtendo sua localização.</small>
                    </div>
                </div>
            </div>
            
            <!-- Como Usar -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-question-circle"></i> Como Usar</h5>
                </div>
                <div class="card-body">
                    <ol class="small mb-0">
                        <li><strong>Minha Localização:</strong> Obtém sua posição atual</li>
                        <li><strong>Marcar no Mapa:</strong> Clique no mapa para adicionar pontos de venda</li>
                        <li><strong>Salvar Pontos:</strong> Clique nos popups para salvar permanentemente</li>
                        <li><strong>Criar Rotas:</strong> Clique no botão 🗺️ nos pontos salvos</li>
                        <li><strong>Navegar:</strong> Use a lista lateral ou clique nos marcadores</li>
                        <li><strong>Limpar Rotas:</strong> Remove apenas as rotas do mapa</li>
                        <li><strong>Limpar Tudo:</strong> Remove marcadores temporários e rotas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Leaflet Routing Machine para rotas -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
// Variáveis globais
let map;
let userMarker;
let accuracyCircle;
let userPosition = null;
let temporaryMarkers = [];
let savedMarkers = [];
let modoMarcacao = false;
let routingControl = null;

// Inicializar mapa
function initMap() {
    try {
        // Coordenadas iniciais (São Paulo - Brasil)
        const initialPosition = [-23.5505, -46.6333];
        
        // Criar mapa
        map = L.map('map').setView(initialPosition, 12);
        
        // Adicionar camada de tiles do OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Adicionar evento de clique no mapa
        map.on('click', function(e) {
            if (modoMarcacao) {
                marcarPontoNoMapa(e.latlng);
            }
        });
        
        // Carregar pontos salvos
        carregarPontosSalvos();
        
        console.log('Mapa inicializado com sucesso');
        
    } catch (error) {
        console.error('Erro ao inicializar mapa:', error);
        document.getElementById('map').innerHTML = `
            <div class="alert alert-danger h-100 d-flex align-items-center justify-content-center">
                <div class="text-center">
                    <i class="bi bi-exclamation-triangle fs-1"></i><br>
                    <strong>Erro ao carregar o mapa</strong><br>
                    <small>Verifique sua conexão com a internet</small><br>
                    <button class="btn btn-sm btn-primary mt-2" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Recarregar
                    </button>
                </div>
            </div>
        `;
    }
}

// Função para salvar a localização atual como um ponto
function salvarLocalizacaoAtual() {
    if (!userPosition) {
        mostrarAlerta('❌ Nenhuma localização disponível para salvar!', 'warning');
        return;
    }
    
    const nome = prompt('Digite um nome para este ponto:', `Ponto ${Date.now()}`);
    
    if (nome && nome.trim()) {
        const ponto = {
            id: Date.now().toString(),
            nome: nome.trim(),
            lat: userPosition.lat,
            lng: userPosition.lng,
            timestamp: new Date().toISOString(),
            tipo: 'salvo_automaticamente'
        };
        
        // Salvar no localStorage
        let pontosSalvos = JSON.parse(localStorage.getItem('pontosMapa') || '[]');
        pontosSalvos.push(ponto);
        localStorage.setItem('pontosMapa', JSON.stringify(pontosSalvos));
        
        // Criar marcador no mapa
        const marker = L.marker([ponto.lat, ponto.lng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            })
        }).addTo(map);
        
        marker.bindPopup(`
            <div class="text-center">
                <strong>📍 ${ponto.nome}</strong><br>
                <small>Salvo: ${new Date(ponto.timestamp).toLocaleString('pt-BR')}</small><br>
                <small>Lat: ${ponto.lat.toFixed(6)}, Lng: ${ponto.lng.toFixed(6)}</small><br>
                <button class="btn btn-sm btn-primary mt-1" onclick="criarRota(${ponto.lat}, ${ponto.lng}, '${ponto.nome}')">
                    🗺️ Criar Rota
                </button>
            </div>
        `);
        
        // Adicionar às estruturas de controle
        pontosMarcados.push({
            marker: marker,
            dados: ponto
        });
        
        // Atualizar lista lateral
        atualizarListaPontos();
        
        console.log(`💾 Ponto "${ponto.nome}" salvo com sucesso!`);
    }
}

// Função para obter localização com múltiplas tentativas
function obterLocalizacao() {
    const btnLocalizar = document.getElementById('btnLocalizar');
    
    if (!navigator.geolocation) {
        console.error('⚠️ Seu navegador não suporta geolocalização');
        alert('Seu navegador não suporta geolocalização');
        return;
    }
    
    // Mostrar carregamento
    btnLocalizar.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Buscando GPS...';
    btnLocalizar.disabled = true;
    
    // Implementar sistema de múltiplas tentativas para melhor precisão
    tentarLocalizacaoComPrecisao();
}

// Função para tentar obter melhor precisão com múltiplas tentativas
function tentarLocalizacaoComPrecisao(tentativa = 1, melhorPosicao = null) {
    const maxTentativas = 3;
    const btnLocalizar = document.getElementById('btnLocalizar');
    
    // Atualizar status
    btnLocalizar.innerHTML = `<i class="bi bi-arrow-repeat spin"></i> Tentativa ${tentativa}/${maxTentativas}...`;
    
    // Atualizar informações na tela
    document.getElementById('infoLocalizacao').innerHTML = `
        <div class="alert alert-info mb-0">
            <i class="bi bi-satellite"></i> <strong>Buscando precisão máxima...</strong><br>
            <div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: ${(tentativa/maxTentativas)*100}%"></div>
            </div>
            <small>Tentativa ${tentativa} de ${maxTentativas} - Aguarde para melhor precisão</small>
        </div>
    `;
    
    // Configurações diferenciadas por tentativa para otimizar precisão
    const options = {
        enableHighAccuracy: true,
        timeout: tentativa === 1 ? 15000 : (tentativa === 2 ? 25000 : 35000),
        maximumAge: 0
    };
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const novaLocalizacao = {
                coords: position.coords,
                timestamp: position.timestamp
            };
            
            // Se é a primeira tentativa ou se a nova localização é mais precisa
            if (!melhorPosicao || novaLocalizacao.coords.accuracy < melhorPosicao.coords.accuracy) {
                melhorPosicao = novaLocalizacao;
                
                // Mostrar progresso da precisão
                const precisao = Math.round(melhorPosicao.coords.accuracy);
                let statusPrecisao = 'Melhorando precisão...';
                let corStatus = 'info';
                
                if (precisao <= 5) {
                    statusPrecisao = 'Precisão excelente!';
                    corStatus = 'success';
                } else if (precisao <= 15) {
                    statusPrecisao = 'Boa precisão!';
                    corStatus = 'info';
                } else if (precisao <= 50) {
                    statusPrecisao = 'Precisão razoável';
                    corStatus = 'warning';
                }
                
                document.getElementById('infoLocalizacao').innerHTML = `
                    <div class="alert alert-${corStatus} mb-0">
                        <i class="bi bi-satellite"></i> <strong>${statusPrecisao}</strong><br>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: ${(tentativa/maxTentativas)*100}%"></div>
                        </div>
                        <small>Precisão atual: ±${precisao}m - Tentativa ${tentativa}/${maxTentativas}</small>
                    </div>
                `;
            }
            
            // Se ainda há tentativas e a precisão pode melhorar
            if (tentativa < maxTentativas && melhorPosicao.coords.accuracy > 10) {
                setTimeout(() => {
                    tentarLocalizacaoComPrecisao(tentativa + 1, melhorPosicao);
                }, 2000); // Aguardar 2 segundos entre tentativas
            } else {
                // Usar a melhor posição obtida
                processarLocalizacaoObtida(melhorPosicao.coords);
            }
        },
        (error) => {
            console.error(`Erro na tentativa ${tentativa}:`, error);
            
            // Se já temos uma posição anterior, usar ela
            if (melhorPosicao) {
                processarLocalizacaoObtida(melhorPosicao.coords);
                return;
            }
            
            // Se ainda há tentativas, tentar novamente
            if (tentativa < maxTentativas) {
                setTimeout(() => {
                    tentarLocalizacaoComPrecisao(tentativa + 1, melhorPosicao);
                }, 3000);
            } else {
                // Todas as tentativas falharam
                processarErroLocalizacao(error);
            }
        },
        options
    );
}

// Função para processar a localização obtida
function processarLocalizacaoObtida(coords) {
    const lat = coords.latitude;
    const lng = coords.longitude;
    const accuracy = coords.accuracy;
    
    console.log('Melhor localização obtida:', { lat, lng, accuracy });
    
    userPosition = { lat, lng };
    
    // Centralizar mapa na localização do usuário
    if (map) {
        map.setView([lat, lng], 18); // Zoom mais próximo para melhor visualização
    }
    
    // Remover marcador anterior se existir
    if (userMarker) {
        map.removeLayer(userMarker);
    }
    
    // Remover círculo de precisão anterior se existir
    if (accuracyCircle) {
        map.removeLayer(accuracyCircle);
    }
    
    // Criar marcador da localização atual
    userMarker = L.marker([lat, lng], {
        title: 'Sua Localização Atual'
    }).addTo(map);
    
    // Adicionar popup ao marcador
    userMarker.bindPopup(`
        <div class="text-center">
            <strong>📍 Sua Localização</strong><br>
            <small>Lat: ${lat.toFixed(6)}</small><br>
            <small>Lng: ${lng.toFixed(6)}</small><br>
            <small>Precisão: ±${Math.round(accuracy)}m</small><br>
            <button class="btn btn-sm btn-success mt-1" onclick="salvarLocalizacaoAtual()">
                💾 Salvar Este Ponto
            </button>
        </div>
    `);
    
    // Adicionar círculo de precisão com cor dinâmica
    let corCirculo = '#dc3545'; // Vermelho para precisão baixa
    if (accuracy <= 10) {
        corCirculo = '#28a745'; // Verde para excelente
    } else if (accuracy <= 25) {
        corCirculo = '#17a2b8'; // Azul para boa
    } else if (accuracy <= 50) {
        corCirculo = '#ffc107'; // Amarelo para regular
    }
    
    accuracyCircle = L.circle([lat, lng], {
        color: corCirculo,
        fillColor: corCirculo,
        fillOpacity: 0.15,
        radius: accuracy
    }).addTo(map);
    
    // Determinar qualidade da precisão
    let qualidadePrecisao, corPrecisao, iconePrecisao, dicaPrecisao;
    if (accuracy <= 5) {
        qualidadePrecisao = 'Excelente';
        corPrecisao = 'text-success';
        iconePrecisao = '🎯';
        dicaPrecisao = 'Precisão perfeita para navegação!';
    } else if (accuracy <= 15) {
        qualidadePrecisao = 'Muito Boa';
        corPrecisao = 'text-info';
        iconePrecisao = '🔵';
        dicaPrecisao = 'Boa precisão para uso geral';
    } else if (accuracy <= 50) {
        qualidadePrecisao = 'Razoável';
        corPrecisao = 'text-warning';
        iconePrecisao = '🟡';
        dicaPrecisao = 'Precisão adequada para áreas urbanas';
    } else {
        qualidadePrecisao = 'Baixa';
        corPrecisao = 'text-danger';
        iconePrecisao = '🔴';
        dicaPrecisao = 'Tente sair de ambientes fechados';
    }
    
    // Atualizar informações na interface
    document.getElementById('coordenadasAtuais').innerHTML = `
        <strong>📍 Coordenadas:</strong><br>
        <small>Lat: ${lat.toFixed(6)}</small><br>
        <small>Lng: ${lng.toFixed(6)}</small><br>
        <button class="btn btn-sm btn-outline-primary mt-1" onclick="copiarCoordenadas(${lat}, ${lng})">
            <i class="bi bi-clipboard"></i> Copiar
        </button>
    `;
    
    document.getElementById('precisaoGPS').innerHTML = `
        <strong>🎯 Precisão:</strong><br>
        <small class="${corPrecisao}">${iconePrecisao} ±${Math.round(accuracy)}m</small><br>
        <small class="${corPrecisao}">(${qualidadePrecisao})</small><br>
        <small class="text-muted">${dicaPrecisao}</small>
    `;
    
    // Mostrar seção de coordenadas
    document.getElementById('coordenadas').style.display = 'block';
    document.getElementById('semLocalizacao').style.display = 'block';
    
    // Atualizar info principal
    document.getElementById('infoLocalizacao').innerHTML = `
        <div class="alert alert-success mb-0">
            <i class="bi bi-check-circle"></i> <strong>Localização encontrada!</strong><br>
            ${iconePrecisao} Precisão: ±${Math.round(accuracy)}m (${qualidadePrecisao})<br>
            <small class="text-muted">${dicaPrecisao}</small>
        </div>
    `;
    
    // Habilitar botão salvar
    document.getElementById('btnSalvar').disabled = false;
    
    // Restaurar botão localizar
    const btnLocalizar = document.getElementById('btnLocalizar');
    btnLocalizar.innerHTML = '<i class="bi bi-crosshair"></i> Minha Localização';
    btnLocalizar.disabled = false;
    
    mostrarAlerta(`🎯 Localização obtida com precisão de ±${Math.round(accuracy)}m!`, 'success');
}

// Função para processar erros de localização
function processarErroLocalizacao(error) {
    console.error('Erro final de geolocalização:', error);
    
    const btnLocalizar = document.getElementById('btnLocalizar');
    btnLocalizar.innerHTML = '<i class="bi bi-crosshair"></i> Tentar Novamente';
    btnLocalizar.disabled = false;
    
    let mensagem = 'Erro ao obter localização: ';
    let sugestoes = [];
    
    switch(error.code) {
        case error.PERMISSION_DENIED:
            mensagem += 'Permissão negada pelo usuário.';
            sugestoes = [
                'Clique no ícone de cadeado na barra de endereço',
                'Selecione "Permitir" para localização',
                'Recarregue a página e tente novamente'
            ];
            break;
        case error.POSITION_UNAVAILABLE:
            mensagem += 'Localização indisponível.';
            sugestoes = [
                'Verifique se o GPS está ativado no dispositivo',
                'Tente sair de ambientes fechados',
                'Aguarde alguns minutos e tente novamente'
            ];
            break;
        case error.TIMEOUT:
            mensagem += 'Tempo limite esgotado.';
            sugestoes = [
                'Verifique sua conexão com a internet',
                'Tente em um local com melhor sinal',
                'Aguarde e tente novamente'
            ];
            break;
        default:
            mensagem += 'Erro desconhecido.';
            sugestoes = [
                'Recarregue a página',
                'Verifique as permissões do navegador',
                'Tente usar outro navegador'
            ];
            break;
    }
    
    mostrarAlerta(mensagem, 'danger', 8000);
    
    document.getElementById('infoLocalizacao').innerHTML = `
        <div class="alert alert-warning mb-0">
            <i class="bi bi-exclamation-triangle"></i> <strong>Não foi possível obter sua localização</strong><br>
            <strong>🔧 Como resolver:</strong><br>
            ${sugestoes.map((sugestao, index) => `${index + 1}. ${sugestao}`).join('<br>')}<br>
            <div class="mt-2">
                <button class="btn btn-sm btn-primary" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Recarregar Página
                </button>
                <button class="btn btn-sm btn-info ms-1" onclick="obterLocalizacao()">
                    <i class="bi bi-crosshair"></i> Tentar Novamente
                </button>
            </div>
        </div>
    `;
}
    
    // Configurações otimizadas para máxima precisão
    const options = {
        enableHighAccuracy: true,    // Usar GPS de alta precisão
        timeout: 30000,             // Aumentar timeout para 30 segundos
        maximumAge: 0                // Sempre buscar localização nova (não usar cache)
    };
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const accuracy = position.coords.accuracy;
            
            userPosition = { lat, lng };
            
            // Centralizar mapa na localização do usuário
            if (map) {
                map.setView([lat, lng], 16);
            }
            
            // Remover marcador anterior se existir
            if (userMarker) {
                map.removeLayer(userMarker);
            }
            
            // Remover círculo de precisão anterior se existir
            if (accuracyCircle) {
                map.removeLayer(accuracyCircle);
            }
            
            // Criar marcador da localização atual
            userMarker = L.marker([lat, lng], {
                title: 'Sua Localização Atual'
            }).addTo(map);
            
            // Adicionar popup ao marcador
            userMarker.bindPopup(`
                <div class="text-center">
                    <strong>📍 Sua Localização</strong><br>
                    <small>Lat: ${lat.toFixed(6)}</small><br>
                    <small>Lng: ${lng.toFixed(6)}</small><br>
                    <button class="btn btn-sm btn-success mt-1" onclick="salvarLocalizacaoAtual()">
                        💾 Salvar Este Ponto
                    </button>
                </div>
            `);
            
            // Adicionar círculo de precisão
            accuracyCircle = L.circle([lat, lng], {
                color: '#0066cc',
                fillColor: '#0066cc',
                fillOpacity: 0.1,
                radius: accuracy
            }).addTo(map);
            
            // Determinar qualidade da precisão
            let qualidadePrecisao, corPrecisao, iconePrecisao;
            if (accuracy <= 10) {
                qualidadePrecisao = 'Excelente';
                corPrecisao = 'text-success';
                iconePrecisao = '🎯';
            } else if (accuracy <= 50) {
                qualidadePrecisao = 'Boa';
                corPrecisao = 'text-info';
                iconePrecisao = '🟢';
            } else if (accuracy <= 100) {
                qualidadePrecisao = 'Regular';
                corPrecisao = 'text-warning';
                iconePrecisao = '🟡';
            } else {
                qualidadePrecisao = 'Baixa';
                corPrecisao = 'text-danger';
                iconePrecisao = '🔴';
            }
            
            // Atualizar informações na interface
            document.getElementById('coordenadasAtuais').innerHTML = `
                <strong>📍 Coordenadas:</strong><br>
                <small>Lat: ${lat.toFixed(6)}</small><br>
                <small>Lng: ${lng.toFixed(6)}</small><br>
                <button class="btn btn-sm btn-outline-primary mt-1" onclick="copiarCoordenadas(${lat}, ${lng})">
                    <i class="bi bi-clipboard"></i> Copiar
                </button>
            `;
            
            document.getElementById('precisaoGPS').innerHTML = `
                <strong>🎯 Precisão:</strong><br>
                <small class="${corPrecisao}">${iconePrecisao} ±${Math.round(accuracy)}m</small><br>
                <small class="${corPrecisao}">(${qualidadePrecisao})</small>
            `;
            
            // Mostrar seção de coordenadas
            document.getElementById('coordenadas').style.display = 'block';
            document.getElementById('semLocalizacao').style.display = 'block';
            
            // Atualizar info principal
            document.getElementById('infoLocalizacao').innerHTML = `
                <div class="alert alert-success mb-0">
                    <i class="bi bi-check-circle"></i> <strong>Localização encontrada!</strong><br>
                    ${iconePrecisao} Precisão: ±${Math.round(accuracy)}m (${qualidadePrecisao})
                </div>
            `;
            
            // Habilitar botão salvar
            document.getElementById('btnSalvar').disabled = false;
            
            mostrarAlerta('🎯 Localização encontrada com sucesso!', 'success');
        },
        (error) => {
            console.error('Erro de geolocalização:', error);
            
            let mensagem = 'Erro ao obter localização: ';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    mensagem += 'Permissão negada pelo usuário.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    mensagem += 'Localização indisponível.';
                    break;
                case error.TIMEOUT:
                    mensagem += 'Tempo limite esgotado.';
                    break;
                default:
                    mensagem += 'Erro desconhecido.';
                    break;
            }
            
            mostrarAlerta(mensagem, 'danger', 8000);
            
            document.getElementById('infoLocalizacao').innerHTML = `
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle"></i> <strong>Erro ao obter localização</strong><br>
                    <small>Permita o acesso à localização e tente novamente</small>
                </div>
            `;
        },
        options
    );
    
    // Restaurar botão
    setTimeout(() => {
        btnLocalizar.innerHTML = '<i class="bi bi-crosshair"></i> Minha Localização';
        btnLocalizar.disabled = false;
    }, 5000);


// Função para alternar modo de marcação
function toggleModoMarcacao() {
    const btnMarcar = document.getElementById('btnMarcar');
    modoMarcacao = !modoMarcacao;
    
    if (modoMarcacao) {
        btnMarcar.classList.remove('btn-info');
        btnMarcar.classList.add('btn-success');
        btnMarcar.innerHTML = '<i class="bi bi-pin-map-fill"></i> Modo Ativo';
        map.getContainer().style.cursor = 'crosshair';
        mostrarAlerta('🎯 Clique no mapa para marcar pontos!', 'info', 3000);
    } else {
        btnMarcar.classList.remove('btn-success');
        btnMarcar.classList.add('btn-info');
        btnMarcar.innerHTML = '<i class="bi bi-pin-map"></i> Marcar no Mapa';
        map.getContainer().style.cursor = '';
        mostrarAlerta('👆 Modo de marcação desativado', 'info', 2000);
    }
}

// Função para marcar ponto no mapa
function marcarPontoNoMapa(latlng) {
    const lat = latlng.lat;
    const lng = latlng.lng;
    const index = temporaryMarkers.length + 1;
    
    // Criar marcador temporário
    const marker = L.marker([lat, lng], {
        title: `Ponto Temporário ${index}`,
        draggable: true
    }).addTo(map);
    
    // Criar popup
    const popupContent = `
        <div class="text-center">
            <strong>📍 Ponto ${index}</strong><br>
            <small>Lat: ${lat.toFixed(6)}</small><br>
            <small>Lng: ${lng.toFixed(6)}</small><br>
            <div class="mt-2">
                <button class="btn btn-xs btn-success me-1" onclick="salvarPontoTemporario(${temporaryMarkers.length})" style="font-size:10px; padding:2px 6px;">
                    <i class="bi bi-floppy"></i> Salvar
                </button>
                <button class="btn btn-xs btn-danger" onclick="removerMarcadorTemp(${temporaryMarkers.length})" style="font-size:10px; padding:2px 6px;">
                    <i class="bi bi-trash"></i> Remover
                </button>
            </div>
        </div>
    `;
    
    marker.bindPopup(popupContent).openPopup();
    
    // Salvar referência
    temporaryMarkers.push({ marker, lat, lng });
    
    mostrarAlerta(`📍 Ponto ${index} marcado! Clique nele para opções`, 'info');
}

// Função para salvar ponto temporário
function salvarPontoTemporario(index) {
    if (!temporaryMarkers[index]) return;
    
    const nome = prompt('Nome para este ponto:');
    if (!nome) return;
    
    const { lat, lng } = temporaryMarkers[index];
    
    salvarPonto(nome, lat, lng);
    
    // Remover marcador temporário
    map.removeLayer(temporaryMarkers[index].marker);
    temporaryMarkers.splice(index, 1);
}

// Função para salvar localização (alias para compatibilidade)
function salvarLocalizacao() {
    salvarLocalizacaoAtual();
}

// Função para salvar localização atual
function salvarLocalizacaoAtual() {
    if (!userPosition) {
        mostrarAlerta('⚠️ Primeiro localize sua posição atual', 'warning');
        return;
    }
    
    const nome = prompt('Nome para este ponto de venda:');
    if (!nome) return;
    
    salvarPonto(nome, userPosition.lat, userPosition.lng);
}

// Função genérica para salvar ponto
function salvarPonto(nome, lat, lng) {
    const ponto = {
        id: Date.now(),
        nome: nome,
        lat: lat,
        lng: lng,
        data: new Date().toLocaleString('pt-BR')
    };
    
    // Salvar no localStorage
    let pontosSalvos = JSON.parse(localStorage.getItem('pontos_venda') || '[]');
    pontosSalvos.push(ponto);
    localStorage.setItem('pontos_venda', JSON.stringify(pontosSalvos));
    
    // Criar marcador permanente
    adicionarMarcadorSalvo(ponto);
    
    // Atualizar lista
    atualizarListaPontos();
    
    mostrarAlerta(`💾 Ponto "${nome}" salvo com sucesso!`, 'success');
}

// Função para adicionar marcador salvo
function adicionarMarcadorSalvo(ponto) {
    const marker = L.marker([ponto.lat, ponto.lng], {
        title: ponto.nome
    }).addTo(map);
    
    // Ícone diferente para pontos salvos
    marker.setIcon(L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    }));
    
    const popupContent = `
        <div class="text-center">
            <strong>${ponto.nome}</strong><br>
            <small>Salvo em: ${ponto.data}</small><br>
            <small>Lat: ${ponto.lat.toFixed(6)}</small><br>
            <small>Lng: ${ponto.lng.toFixed(6)}</small><br>
            <div class="mt-2">
                <button class="btn btn-xs btn-primary me-1" onclick="irParaPonto(${ponto.lat}, ${ponto.lng})" style="font-size:10px; padding:2px 6px;">
                    👁️ Ver
                </button>
                <button class="btn btn-xs btn-success" onclick="criarRota(${ponto.lat}, ${ponto.lng}, '${ponto.nome.replace(/'/g, "\\'")}')"
                    title="Criar rota">
                    🗺️ Rota
                </button>
            </div>
        </div>
    `;
    
    marker.bindPopup(popupContent);
    
    savedMarkers.push({ marker, ponto });
}

// Função para carregar pontos salvos
function carregarPontosSalvos() {
    const pontosSalvos = JSON.parse(localStorage.getItem('pontos_venda') || '[]');
    
    pontosSalvos.forEach(ponto => {
        adicionarMarcadorSalvo(ponto);
    });
    
    atualizarListaPontos();
}

// Função para atualizar lista de pontos salvos
function atualizarListaPontos() {
    const container = document.getElementById('pontosSalvos');
    const pontosSalvos = JSON.parse(localStorage.getItem('pontos_venda') || '[]');
    
    if (pontosSalvos.length === 0) {
        container.innerHTML = `
            <p class="text-muted">Nenhum ponto salvo ainda.</p>
            <small class="text-muted">Salve pontos clicando no mapa ou obtendo sua localização.</small>
        `;
        return;
    }
    
    let html = '';
    pontosSalvos.forEach((ponto) => {
        html += `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                <div>
                    <strong>${ponto.nome}</strong><br>
                    <small class="text-muted">${ponto.data}</small>
                </div>
                <div class="btn-group-vertical">
                    <button class="btn btn-sm btn-outline-primary mb-1" onclick="irParaPonto(${ponto.lat}, ${ponto.lng})" title="Ver no mapa">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-success mb-1" onclick="criarRota(${ponto.lat}, ${ponto.lng}, '${ponto.nome.replace(/'/g, "\\'")}')"
                        title="Criar rota">
                        <i class="bi bi-signpost-2"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info mb-1" onclick="copiarCoordenadas(${ponto.lat}, ${ponto.lng})" title="Copiar coordenadas">
                        <i class="bi bi-clipboard"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="removerPonto(${ponto.id})" title="Remover">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Função para ir para um ponto específico
function irParaPonto(lat, lng) {
    if (map) {
        map.setView([lat, lng], 18);
        
        // Encontrar e abrir popup do marcador
        savedMarkers.forEach(item => {
            if (item.ponto.lat === lat && item.ponto.lng === lng) {
                item.marker.openPopup();
            }
        });
    }
}

// Função para remover um ponto
function removerPonto(id) {
    if (!confirm('Deseja remover este ponto?')) return;
    
    // Remover do localStorage
    let pontosSalvos = JSON.parse(localStorage.getItem('pontos_venda') || '[]');
    pontosSalvos = pontosSalvos.filter(ponto => ponto.id !== id);
    localStorage.setItem('pontos_venda', JSON.stringify(pontosSalvos));
    
    // Remover marcador do mapa
    savedMarkers = savedMarkers.filter(item => {
        if (item.ponto.id === id) {
            map.removeLayer(item.marker);
            return false;
        }
        return true;
    });
    
    // Atualizar lista
    atualizarListaPontos();
    
    mostrarAlerta('Ponto removido com sucesso!', 'success');
}

// Função para remover marcador temporário
function removerMarcadorTemp(index) {
    if (temporaryMarkers[index]) {
        map.removeLayer(temporaryMarkers[index].marker);
        temporaryMarkers.splice(index, 1);
        mostrarAlerta('🗑️ Marcador removido!', 'info');
    }
}

// Função para criar rota
function criarRota(lat, lng, nome) {
    if (!userPosition) {
        mostrarAlerta('⚠️ Primeiro obtenha sua localização atual para criar uma rota!', 'warning');
        document.getElementById('btnLocalizar').classList.add('btn-pulse');
        setTimeout(() => {
            document.getElementById('btnLocalizar').classList.remove('btn-pulse');
        }, 3000);
        return;
    }
    
    // Remover rota anterior se existir
    if (routingControl) {
        map.removeControl(routingControl);
    }
    
    mostrarAlerta('🗺️ Calculando rota...', 'info', 2000);
    
    try {
        // Criar controle de rota
        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(userPosition.lat, userPosition.lng), // Origem: sua localização
                L.latLng(lat, lng) // Destino: ponto selecionado
            ],
            routeWhileDragging: true,
            addWaypoints: false,
            createMarker: function() { return null; }, // Não criar marcadores extras
            lineOptions: {
                styles: [{
                    color: '#FF4444',
                    weight: 5,
                    opacity: 0.8
                }]
            }
        });
        
        // Adicionar eventos ao controle de rota
        routingControl.on('routesfound', function(e) {
            const route = e.routes[0];
            const summary = route.summary;
            
            // Converter distância e tempo
            const distanciaKm = (summary.totalDistance / 1000).toFixed(2);
            const tempoMin = Math.round(summary.totalTime / 60);
            
            mostrarAlerta(
                `🏁 Rota para "${nome}" criada!<br>` +
                `📏 Distância: ${distanciaKm} km<br>` +
                `⏱️ Tempo estimado: ${tempoMin} min`,
                'success', 6000
            );
            
            // Atualizar informações na interface
            document.getElementById('infoLocalizacao').innerHTML = `
                <div class="alert alert-success mb-0">
                    <i class="bi bi-check-circle"></i> <strong>Rota ativa para: ${nome}</strong><br>
                    📏 ${distanciaKm} km | ⏱️ ${tempoMin} min<br>
                    <button class="btn btn-sm btn-warning mt-1" onclick="limparRotas()">
                        <i class="bi bi-x-circle"></i> Remover Rota
                    </button>
                </div>
            `;
        });
        
        routingControl.on('routingerror', function(e) {
            mostrarAlerta('❌ Não foi possível calcular a rota. Tente novamente.', 'danger');
            console.error('Erro na rota:', e);
        });
        
        // Adicionar ao mapa
        routingControl.addTo(map);
        
    } catch (error) {
        console.error('Erro ao criar rota:', error);
        mostrarAlerta('❌ Erro ao calcular rota: ' + error.message, 'danger');
    }
}

// Função para limpar rotas
function limparRotas() {
    if (routingControl) {
        map.removeControl(routingControl);
        routingControl = null;
        
        // Restaurar informações de localização
        if (userPosition) {
            document.getElementById('infoLocalizacao').innerHTML = `
                <div class="alert alert-success mb-0">
                    <i class="bi bi-check-circle"></i> <strong>Localização encontrada!</strong><br>
                    Clique nos pontos salvos para criar rotas
                </div>
            `;
        } else {
            document.getElementById('infoLocalizacao').innerHTML = `
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> 
                    Clique em "Minha Localização" para se localizar ou ative "Marcar no Mapa" para adicionar pontos.
                </div>
            `;
        }
        
        mostrarAlerta('🗺️ Rota removida!', 'info', 2000);
    } else {
        mostrarAlerta('ℹ️ Nenhuma rota ativa para remover', 'info', 2000);
    }
}

// Função para limpar mapa (agora limpa tudo)
function limparMapa() {
    // Remover marcadores temporários
    temporaryMarkers.forEach(item => {
        if (item.marker) {
            map.removeLayer(item.marker);
        }
    });
    temporaryMarkers = [];
    
    // Remover rotas
    limparRotas();
    
    // Desativar modo marcação
    if (modoMarcacao) {
        toggleModoMarcacao();
    }
    
    mostrarAlerta('🧹 Mapa limpo! Marcadores temporários e rotas removidos', 'success');
}

// Função para copiar coordenadas
function copiarCoordenadas(lat, lng) {
    const coordenadas = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    const coordenadasDetalhadas = `Coordenadas:\nLatitude: ${lat.toFixed(6)}\nLongitude: ${lng.toFixed(6)}\nGoogle Maps: https://www.google.com/maps?q=${lat},${lng}\nWaze: https://waze.com/ul?ll=${lat}%2C${lng}&navigate=yes`;
    
    if (navigator.clipboard) {
        // Tentar copiar primeiro as coordenadas detalhadas
        navigator.clipboard.writeText(coordenadasDetalhadas).then(() => {
            mostrarAlerta(`📋 Coordenadas detalhadas copiadas!\nLat: ${lat.toFixed(6)} | Lng: ${lng.toFixed(6)}\n+ Links para navegação`, 'success', 4000);
        }).catch(() => {
            // Fallback: copiar apenas as coordenadas básicas
            navigator.clipboard.writeText(coordenadas).then(() => {
                mostrarAlerta(`📋 Coordenadas básicas copiadas: ${coordenadas}`, 'info', 3000);
            });
        });
    } else {
        // Fallback para navegadores antigos
        const textarea = document.createElement('textarea');
        textarea.value = coordenadasDetalhadas;
        textarea.style.position = 'fixed';
        textarea.style.left = '-999999px';
        textarea.style.top = '-999999px';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        
        try {
            const sucesso = document.execCommand('copy');
            if (sucesso) {
                mostrarAlerta(`📋 Coordenadas detalhadas copiadas!\n${coordenadas}`, 'success', 3000);
            } else {
                // Mostrar coordenadas para cópia manual
                mostrarCoordenadaManual(lat, lng);
            }
        } catch (err) {
            mostrarCoordenadaManual(lat, lng);
        } finally {
            document.body.removeChild(textarea);
        }
    }
}

// Função para mostrar coordenadas para cópia manual
function mostrarCoordenadaManual(lat, lng) {
    const modal = `
        <div class="modal fade" id="modalCoordenadas" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">📋 Coordenadas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Copie as coordenadas abaixo:</strong></p>
                        <div class="form-group mb-2">
                            <label>Coordenadas simples:</label>
                            <input type="text" class="form-control" value="${lat.toFixed(6)}, ${lng.toFixed(6)}" readonly onclick="this.select()">
                        </div>
                        <div class="form-group mb-2">
                            <label>Google Maps:</label>
                            <input type="text" class="form-control" value="https://www.google.com/maps?q=${lat},${lng}" readonly onclick="this.select()">
                        </div>
                        <div class="form-group">
                            <label>Waze:</label>
                            <input type="text" class="form-control" value="https://waze.com/ul?ll=${lat}%2C${lng}&navigate=yes" readonly onclick="this.select()">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal existente
    const modalExistente = document.getElementById('modalCoordenadas');
    if (modalExistente) {
        modalExistente.remove();
    }
    
    // Adicionar novo modal
    document.body.insertAdjacentHTML('beforeend', modal);
    
    // Mostrar modal
    const modalElement = new bootstrap.Modal(document.getElementById('modalCoordenadas'));
    modalElement.show();
    
    // Remover modal após fechar
    document.getElementById('modalCoordenadas').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
    
    mostrarAlerta('ℹ️ Coordenadas disponíveis para cópia manual', 'info', 3000);
}

// Função para mostrar alerta
function mostrarAlerta(mensagem, tipo = 'info', duracao = 4000) {
    // Usar a função global se existir
    if (typeof window.mostrarAlerta === 'function') {
        window.mostrarAlerta(mensagem, tipo, duracao);
        return;
    }
    
    // Fallback simples
    const alertClass = {
        'success': 'alert-success',
        'danger': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass[tipo] || 'alert-info'} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
    alertDiv.innerHTML = `
        ${mensagem}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, duracao);
}

// Função para corrigir renderização do mapa
function corrigirMapa() {
    if (map) {
        console.log('Corrigindo renderização do mapa...');
        
        // Invalidar tamanho do mapa
        setTimeout(() => {
            map.invalidateSize(true);
            console.log('Tamanho do mapa invalidado');
        }, 100);
        
        // Forçar redesenho
        const mapContainer = document.getElementById('map');
        if (mapContainer) {
            const display = mapContainer.style.display;
            mapContainer.style.display = 'none';
            mapContainer.offsetHeight; // Forçar reflow
            mapContainer.style.display = display;
        }
        
        // Segunda invalidação após reflow
        setTimeout(() => {
            if (map) {
                map.invalidateSize(true);
                mostrarAlerta('🔧 Renderização do mapa corrigida!', 'success', 2000);
            }
        }, 200);
    } else {
        mostrarAlerta('❌ Mapa não inicializado', 'warning', 2000);
    }
}

// CSS para animação
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .btn-pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
        100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
    }
    
    .btn-xs {
        padding: 2px 6px;
        font-size: 0.75rem;
        line-height: 1.2;
        border-radius: 3px;
    }
    
    .leaflet-routing-container {
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
`;
document.head.appendChild(style);

// Função para criar pontos de exemplo se não houver nenhum
function criarPontosExemplo() {
    const pontosExistentes = JSON.parse(localStorage.getItem('pontos_venda') || '[]');
    
    if (pontosExistentes.length === 0) {
        const pontosExemplo = [
            {
                id: Date.now(),
                nome: "Praia de Copacabana",
                lat: -22.9711,
                lng: -43.1822,
                data: new Date(Date.now() - 86400000).toLocaleString('pt-BR') // 1 dia atrás
            },
            {
                id: Date.now() + 1,
                nome: "Praia de Ipanema",
                lat: -22.9838,
                lng: -43.2096,
                data: new Date(Date.now() - 172800000).toLocaleString('pt-BR') // 2 dias atrás
            },
            {
                id: Date.now() + 2,
                nome: "Barra da Tijuca",
                lat: -23.0129,
                lng: -43.3203,
                data: new Date(Date.now() - 259200000).toLocaleString('pt-BR') // 3 dias atrás
            }
        ];
        
        localStorage.setItem('pontos_venda', JSON.stringify(pontosExemplo));
        console.log('📍 Pontos de exemplo criados para demonstração');
    }
}

// Inicializar quando carregar
document.addEventListener('DOMContentLoaded', function() {
    // Criar pontos de exemplo se necessário
    criarPontosExemplo();
    
    // Verificar se Leaflet carregou
    if (typeof L !== 'undefined') {
        initMap();
        atualizarListaPontos();
    } else {
        // Aguardar carregar
        setTimeout(() => {
            if (typeof L !== 'undefined') {
                initMap();
                atualizarListaPontos();
            } else {
                document.getElementById('map').innerHTML = `
                    <div class="alert alert-danger h-100 d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <i class="bi bi-exclamation-triangle fs-1"></i><br>
                            <strong>Erro ao carregar bibliotecas do mapa</strong><br>
                            <button class="btn btn-sm btn-primary mt-2" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Recarregar
                            </button>
                        </div>
                    </div>
                `;
            }
        }, 1000);
    }
});
</script>