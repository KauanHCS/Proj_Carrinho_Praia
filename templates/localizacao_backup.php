<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-geo-alt-fill"></i> Sua Localização</span>
                <div class="btn-group">
                    <button class="btn btn-sm btn-primary" onclick="obterLocalizacao()" id="btnLocalizar">
                        <i class="bi bi-crosshair"></i> Localizar-me
                    </button>
                    <button class="btn btn-sm btn-success" onclick="salvarLocalizacao()" id="btnSalvar" disabled>
                        <i class="bi bi-bookmark"></i> Salvar Ponto
                    </button>
                    <button class="btn btn-sm btn-info" onclick="toggleModoMarcacao()" id="btnMarcar">
                        <i class="bi bi-pin-map"></i> Marcar no Mapa
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="limparMapa()" id="btnLimpar">
                        <i class="bi bi-trash"></i> Limpar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="map" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                <div class="mt-3" id="infoLocalizacao">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> <strong>Como usar:</strong><br>
                        1. <strong>Localizar-me:</strong> Encontra sua posição atual com GPS<br>
                        2. <strong>Marcar no Mapa:</strong> Clique no mapa para marcar pontos específicos<br>
                        3. <strong>Salvar Ponto:</strong> Salva sua localização atual com nome personalizado<br>
                        4. <strong>Gerar Rota:</strong> Clique em qualquer ponto salvo para traçar uma rota<br>
                        5. <strong>Limpar:</strong> Remove marcadores temporários e rotas do mapa
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pin-map"></i> Pontos Salvos
            </div>
            <div class="card-body" id="pontosSalvos">
                <p class="text-muted">Nenhum ponto salvo ainda.</p>
                <small class="text-muted">Use o botão "Salvar Ponto" no mapa para marcar locais importantes.</small>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-lightbulb"></i> Dicas de Localização
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong>Praias movimentadas:</strong> Maior fluxo de clientes</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong>Perto de quiosques:</strong> Complementa serviços</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong>Áreas com sombra:</strong> Mais confortável</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong>Acesso fácil:</strong> Facilita transporte</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong>Visão ampla:</strong> Clientes te veem facilmente</li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Informações
            </div>
            <div class="card-body">
                <div id="coordenadasAtuais">
                    <strong>Coordenadas:</strong><br>
                    <small class="text-muted">Clique em "Localizar-me" para ver suas coordenadas</small>
                </div>
                <hr>
                <div id="precisaoGPS">
                    <strong>Precisão:</strong><br>
                    <small class="text-muted">-</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Variáveis globais
let map;
let userMarker;
let accuracyCircle;
let userPosition = null;
let routingControl;
let temporaryMarkers = [];
let savedMarkers = [];
let modoMarcacao = false;

// Inicializar Leaflet (OpenStreetMap)
function initMap() {
    console.log('Inicializando Leaflet Map...');
    
    try {
        // Coordenadas iniciais (São Paulo - Brasil)
        const initialPosition = [-23.5505, -46.6333];
        
        // Criar mapa
        map = L.map('map').setView(initialPosition, 10);
        
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
        
        console.log('Leaflet Map inicializado com sucesso');
        
    } catch (error) {
        console.error('Erro ao inicializar Leaflet Map:', error);
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

// Função para obter localização
function obterLocalizacao() {
    const btnLocalizar = document.getElementById('btnLocalizar');
    
    if (!navigator.geolocation) {
        mostrarAlerta('⚠️ Seu navegador não suporta geolocalização', 'warning');
        return;
    }
    
    // Mostrar carregamento
    btnLocalizar.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Localizando...';
    btnLocalizar.disabled = true;
    
    // Atualizar informações iniciais
    document.getElementById('infoLocalizacao').innerHTML = `
        <div class="alert alert-info">
            <i class="bi bi-geo-alt"></i> <strong>Buscando localização...</strong><br>
            <div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 50%"></div>
            </div>
            <small>Aguarde, isso pode levar alguns segundos</small>
        </div>
    `;
    
    const options = {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 60000
    };
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            console.log('Localização obtida:', position);
            
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const accuracy = position.coords.accuracy;
            
            userPosition = { lat, lng };
            
            // Centralizar mapa na localização do usuário (Leaflet)
            if (map && typeof map.setView === 'function') {
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
                iconePrecisao = '🎆';
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
            
            // Atualizar informações
            document.getElementById('coordenadasAtuais').innerHTML = `
                <strong>Coordenadas:</strong><br>
                <small>Latitude: ${lat.toFixed(6)}</small><br>
                <small>Longitude: ${lng.toFixed(6)}</small>
            `;
            
            document.getElementById('precisaoGPS').innerHTML = `
                <strong>Precisão:</strong><br>
                <small class="${corPrecisao}">${iconePrecisao} ±${Math.round(accuracy)}m (${qualidadePrecisao})</small>
            `;
            
            // Atualizar info com mais detalhes
            document.getElementById('infoLocalizacao').innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <strong>Localização encontrada!</strong><br>
                    ${iconePrecisao} Precisão: ±${Math.round(accuracy)}m (${qualidadePrecisao})<br>
                    🗺️ Coordenadas: ${lat.toFixed(6)}, ${lng.toFixed(6)}<br>
                    <div class="mt-2">
                        <button class="btn btn-sm btn-success me-1" onclick="salvarLocalizacao()">
                            💾 Salvar Ponto
                        </button>
                        <button class="btn btn-sm btn-info" onclick="toggleModoMarcacao()">
                            🎯 Marcar Outros
                        </button>
                    </div>
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
                    mensagem += 'Permissão negada. Permita o acesso à localização.';
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
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> <strong>Não foi possível obter sua localização</strong><br>
                    <strong>Como resolver:</strong><br>
                    1. Clique no ícone de cadeado na barra de endereço<br>
                    2. Permita o acesso à localização<br>
                    3. Recarregue a página e tente novamente
                </div>
            `;
        },
        options
    );
    
    // Restaurar botão
    setTimeout(() => {
        btnLocalizar.innerHTML = '<i class="bi bi-crosshair"></i> Localizar-me';
        btnLocalizar.disabled = false;
    }, 5000);
}

// Função para salvar localização atual
function salvarLocalizacao() {
    if (!userPosition) {
        mostrarAlerta('⚠️ Primeiro localize sua posição atual', 'warning');
        return;
    }
    
    const nome = prompt('Nome para este ponto de venda:');
    if (!nome) return;
    
    const ponto = {
        id: Date.now(),
        nome: nome,
        lat: userPosition.lat,
        lng: userPosition.lng,
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
    
    mostrarAlerta(`📍 Ponto "${nome}" salvo com sucesso!`, 'success');
}

// Função para alternar modo de marcação
function toggleModoMarcacao() {
    const btnMarcar = document.getElementById('btnMarcar');
    modoMarcacao = !modoMarcacao;
    
    if (modoMarcacao) {
        btnMarcar.classList.remove('btn-info');
        btnMarcar.classList.add('btn-success');
        btnMarcar.innerHTML = '<i class="bi bi-pin-map-fill"></i> Modo Ativo';
        map.getDiv().style.cursor = 'crosshair';
        mostrarAlerta('🎯 Clique no mapa para marcar pontos!', 'info', 3000);
    } else {
        btnMarcar.classList.remove('btn-success');
        btnMarcar.classList.add('btn-info');
        btnMarcar.innerHTML = '<i class="bi bi-pin-map"></i> Marcar no Mapa';
        map.getDiv().style.cursor = '';
    }
}

// Função para marcar ponto específico no mapa
function marcarPontoNoMapa(latLng) {
    const nome = prompt('Nome para este ponto:');
    if (!nome) return;
    
    // Criar marcador temporário
    const marker = new google.maps.Marker({
        position: latLng,
        map: map,
        title: nome,
        icon: {
            url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
        }
    });
    
    // Criar InfoWindow com opções
    const infoWindow = new google.maps.InfoWindow({
        content: `
            <div>
                <strong>${nome}</strong><br>
                <small>Marcado manualmente</small><br>
                <small>Lat: ${latLng.lat().toFixed(6)}</small><br>
                <small>Lng: ${latLng.lng().toFixed(6)}</small><br>
                <div class="mt-2">
                    <button class="btn btn-xs btn-primary me-1" onclick="gerarRota(${latLng.lat()}, ${latLng.lng()}, '${nome.replace(/'/g, "\\'")}')" style="font-size:10px; padding:2px 6px;">📍 Rota</button>
                    <button class="btn btn-xs btn-success me-1" onclick="salvarPontoMarcado(${latLng.lat()}, ${latLng.lng()}, '${nome.replace(/'/g, "\\'")}')" style="font-size:10px; padding:2px 6px;">💾 Salvar</button>
                    <button class="btn btn-xs btn-danger" onclick="removerMarcadorTemp(${temporaryMarkers.length})" style="font-size:10px; padding:2px 6px;">🗑️ Remover</button>
                </div>
            </div>
        `
    });
    
    marker.addListener('click', () => {
        infoWindow.open(map, marker);
    });
    
    // Abrir automaticamente
    infoWindow.open(map, marker);
    
    // Adicionar à lista de marcadores temporários
    temporaryMarkers.push({
        marker: marker,
        infoWindow: infoWindow,
        nome: nome,
        lat: latLng.lat(),
        lng: latLng.lng()
    });
    
    mostrarAlerta(`📍 Ponto "${nome}" marcado!`, 'success', 3000);
}

// Função para gerar rota
function gerarRota(lat, lng, nome) {
    if (!userPosition) {
        mostrarAlerta('⚠️ Primeiro localize sua posição atual!', 'warning');
        return;
    }
    
    mostrarAlerta('🗺️ Gerando rota...', 'info', 2000);
    
    const request = {
        origin: userPosition,
        destination: { lat: lat, lng: lng },
        travelMode: google.maps.TravelMode.DRIVING
    };
    
    directionsService.route(request, (result, status) => {
        if (status === 'OK') {
            directionsRenderer.setDirections(result);
            
            const route = result.routes[0];
            const leg = route.legs[0];
            
            mostrarAlerta(`🎯 Rota para "${nome}" criada! Distância: ${leg.distance.text}, Tempo: ${leg.duration.text}`, 'success', 5000);
        } else {
            mostrarAlerta(`❌ Não foi possível calcular a rota: ${status}`, 'danger');
            console.error('Erro na rota:', status);
        }
    });
}

// Função para salvar ponto marcado
function salvarPontoMarcado(lat, lng, nome) {
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
    
    mostrarAlerta(`💾 Ponto "${nome}" salvo permanentemente!`, 'success');
}

// Função auxiliar para adicionar marcador salvo
function adicionarMarcadorSalvo(ponto) {
    const marker = new google.maps.Marker({
        position: { lat: ponto.lat, lng: ponto.lng },
        map: map,
        title: ponto.nome,
        icon: {
            url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
        }
    });
    
    const infoWindow = new google.maps.InfoWindow({
        content: `
            <div>
                <strong>${ponto.nome}</strong><br>
                <small>Salvo em: ${ponto.data}</small><br>
                <small>Lat: ${ponto.lat.toFixed(6)}</small><br>
                <small>Lng: ${ponto.lng.toFixed(6)}</small><br>
                <div class="mt-2">
                    <button class="btn btn-xs btn-primary" onclick="gerarRota(${ponto.lat}, ${ponto.lng}, '${ponto.nome.replace(/'/g, "\\'")}')" style="font-size:10px; padding:2px 6px;">🗺️ Gerar Rota</button>
                </div>
            </div>
        `
    });
    
    marker.addListener('click', () => {
        infoWindow.open(map, marker);
    });
    
    savedMarkers.push({ marker, ponto, infoWindow });
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
            <small class="text-muted">Use o botão "Salvar Ponto" no mapa para marcar locais importantes.</small>
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
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" onclick="irParaPonto(${ponto.lat}, ${ponto.lng})" title="Ver no mapa">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info" onclick="gerarRota(${ponto.lat}, ${ponto.lng}, '${ponto.nome.replace(/'/g, "\\\'")}')" title="Gerar rota">
                        <i class="bi bi-signpost-2"></i>
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
    if (map && typeof map.setCenter === 'function') {
        map.setCenter({ lat: lat, lng: lng });
        map.setZoom(18);
    }
    
    // Encontrar e abrir InfoWindow do marcador
    savedMarkers.forEach(item => {
        if (item.ponto.lat === lat && item.ponto.lng === lng) {
            item.infoWindow.open(map, item.marker);
        }
    });
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
            item.marker.setMap(null);
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
        temporaryMarkers[index].marker.setMap(null);
        temporaryMarkers.splice(index, 1);
        mostrarAlerta('🗑️ Marcador removido!', 'info');
    }
}

// Função para limpar mapa
function limparMapa() {
    // Remover marcadores temporários
    temporaryMarkers.forEach(item => {
        if (item.marker) {
            item.marker.setMap(null);
        }
    });
    temporaryMarkers = [];
    
    // Remover rotas
    if (directionsRenderer && typeof directionsRenderer.setDirections === 'function') {
        directionsRenderer.setDirections({routes: []});
    }
    
    // Desativar modo marcação
    if (modoMarcacao) {
        toggleModoMarcacao();
    }
    
    mostrarAlerta('🧹 Mapa limpo!', 'success');
}

// CSS melhorado
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .btn-xs {
        padding: 2px 6px;
        font-size: 0.75rem;
        line-height: 1.2;
        border-radius: 3px;
    }
`;
document.head.appendChild(style);

// Registrar função global
window.initMap = initMap;

// Verificar se já tem Google Maps carregado (removido - será carregado via observer)
</script>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- OpenStreetMap com Leaflet - carregamento otimizado -->
<script>
// Função para carregar Leaflet (OpenStreetMap)
function loadLeafletMap() {
    return new Promise((resolve, reject) => {
        // Verificar se já foi carregado
        if (typeof L !== 'undefined') {
            resolve();
            return;
        }
        
        // Esperar um pouco mais se ainda não carregou
        setTimeout(() => {
            if (typeof L !== 'undefined') {
                resolve();
            } else {
                reject(new Error('Leaflet não carregou corretamente'));
            }
        }, 500);
    });
}

// Inicializar quando a aba for ativada
document.addEventListener('DOMContentLoaded', function() {
    // Observar mudanças nas abas
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const target = mutation.target;
                if (target.id === 'localizacao' && target.classList.contains('active')) {
                    // Aba de localização foi ativada
                    if (!window.leafletLoaded) {
                        window.leafletLoaded = true;
                        loadLeafletMap()
                            .then(() => {
                                console.log('Leaflet carregado com sucesso');
                                setTimeout(initMap, 100);
                            })
                            .catch(error => {
                                console.error('Erro ao carregar Leaflet:', error);
                                document.getElementById('map').innerHTML = `
                                    <div class="alert alert-danger h-100 d-flex align-items-center justify-content-center">
                                        <div class="text-center">
                                            <i class="bi bi-exclamation-triangle fs-1"></i><br>
                                            <strong>Erro ao carregar o mapa</strong><br>
                                            <small>${error.message}</small><br>
                                            <button class="btn btn-sm btn-primary mt-2" onclick="location.reload()">
                                                <i class="bi bi-arrow-clockwise"></i> Recarregar
                                            </button>
                                        </div>
                                    </div>
                                `;
                            });
                    }
                }
            }
        });
    });
    
    // Observar mudanças nas abas
    const localizacaoTab = document.getElementById('localizacao');
    if (localizacaoTab) {
        observer.observe(localizacaoTab, { attributes: true });
    }
});
</script>
