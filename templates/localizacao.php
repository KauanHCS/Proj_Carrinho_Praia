<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-geo-alt-fill"></i> Sua Localização</span>
                <div>
                    <button class="btn btn-sm btn-primary" onclick="obterLocalizacao()" id="btnLocalizar">
                        <i class="bi bi-crosshair"></i> Localizar-me
                    </button>
                    <button class="btn btn-sm btn-success" onclick="salvarLocalizacao()" id="btnSalvar" disabled>
                        <i class="bi bi-bookmark"></i> Salvar Ponto
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="map" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                <div class="mt-3" id="infoLocalizacao">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> <strong>Como usar:</strong><br>
                        1. Clique em "Localizar-me" para encontrar sua posição atual<br>
                        2. O mapa mostrará sua localização com precisão<br>
                        3. Use "Salvar Ponto" para marcar locais importantes de venda
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

<!-- OpenStreetMap com Leaflet (Gratuito) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map;
let userMarker;
let savedMarkers = [];
let userPosition = null;
let accuracyCircle;

// Inicializar mapa usando OpenStreetMap
function initMap() {
    console.log('Inicializando OpenStreetMap...');
    
    try {
        // Coordenadas iniciais (Brasil - centro)
        const initialPosition = [-15.7942, -47.8822];
        
        // Criar mapa
        map = L.map('map').setView(initialPosition, 4);
        
        // Adicionar layer do OpenStreetMap
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        
        console.log('Mapa inicializado com sucesso');
        
        // Verificar se geolocalização está disponível
        if (navigator.geolocation) {
            console.log('Geolocation API disponível');
        } else {
            mostrarAlerta('⚠️ Geolocalização não suportada neste navegador', 'warning');
        }
        
        // Carregar pontos salvos do localStorage
        carregarPontosSalvos();
        
    } catch (error) {
        console.error('Erro ao inicializar mapa:', error);
        document.getElementById('map').innerHTML = '<div class="alert alert-danger">Erro ao carregar o mapa. Verifique sua conexão com a internet.</div>';
    }
}

// Função para obter localização quando o usuário clicar
function obterLocalizacao() {
    const btnLocalizar = document.getElementById('btnLocalizar');
    
    if (!navigator.geolocation) {
        mostrarAlerta('⚠️ Seu navegador não suporta geolocalização', 'warning');
        return;
    }
    
    // Alterar botão para mostrar carregamento
    btnLocalizar.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Localizando...';
    btnLocalizar.disabled = true;
    
    const options = {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 300000 // 5 minutos
    };
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            console.log('Localização obtida:', position);
            
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const accuracy = position.coords.accuracy;
            
            userPosition = { lat, lng };
            
            // Centralizar mapa na localização do usuário
            map.setView([lat, lng], 16);
            
            // Remover marcador anterior se existir
            if (userMarker) {
                map.removeLayer(userMarker);
            }
            
            // Remover círculo de precisão anterior se existir
            if (accuracyCircle) {
                map.removeLayer(accuracyCircle);
            }
            
            // Criar ícone customizado para localização atual
            const userIcon = L.divIcon({
                html: `
                    <div style="
                        width: 20px;
                        height: 20px;
                        background: #0066cc;
                        border: 3px solid #ffffff;
                        border-radius: 50%;
                        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                    "></div>
                `,
                className: 'user-location-marker',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
            
            // Criar marcador da localização atual
            userMarker = L.marker([lat, lng], {
                icon: userIcon,
                title: 'Sua Localização Atual'
            }).addTo(map);
            
            // Adicionar círculo de precisão
            accuracyCircle = L.circle([lat, lng], {
                color: '#0066cc',
                fillColor: '#0066cc',
                fillOpacity: 0.1,
                radius: accuracy,
                weight: 1
            }).addTo(map);
            
            // Atualizar informações na interface
            document.getElementById('coordenadasAtuais').innerHTML = `
                <strong>Coordenadas:</strong><br>
                <small>Latitude: ${lat.toFixed(6)}</small><br>
                <small>Longitude: ${lng.toFixed(6)}</small>
            `;
            
            document.getElementById('precisaoGPS').innerHTML = `
                <strong>Precisão:</strong><br>
                <small class="text-success">±${Math.round(accuracy)}m</small>
            `;
            
            // Atualizar info
            document.getElementById('infoLocalizacao').innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <strong>Localização encontrada!</strong><br>
                    Precisão de ±${Math.round(accuracy)} metros<br>
                    <small>Use "Salvar Ponto" para marcar este local</small>
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
                    mensagem += 'Permissão negada. Permita o acesso à localização nas configurações do navegador.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    mensagem += 'Localização indisponível.';
                    break;
                case error.TIMEOUT:
                    mensagem += 'Tempo limite esgotado. Tente novamente.';
                    break;
                default:
                    mensagem += 'Erro desconhecido.';
                    break;
            }
            
            mostrarAlerta(mensagem, 'danger', 8000);
            
            // Mostrar instruções para habilitar localização
            document.getElementById('infoLocalizacao').innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> <strong>Não foi possível obter sua localização</strong><br>
                    <strong>Como resolver:</strong><br>
                    1. Clique no ícone de cadeado na barra de endereço<br>
                    2. Permita o acesso à localização<br>
                    3. Recarregue a página e tente novamente<br>
                    4. Ou use HTTPS em vez de HTTP
                </div>
            `;
        },
        options
    );
    
    // Restaurar botão
    setTimeout(() => {
        btnLocalizar.innerHTML = '<i class="bi bi-crosshair"></i> Localizar-me';
        btnLocalizar.disabled = false;
    }, 3000);
}

// Função para salvar ponto atual
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
    
    // Criar ícone customizado para pontos salvos
    const savedIcon = L.divIcon({
        html: `
            <div style="
                width: 0;
                height: 0;
                border-left: 12px solid transparent;
                border-right: 12px solid transparent;
                border-top: 20px solid #dc3545;
                position: relative;
            ">
                <div style="
                    position: absolute;
                    top: -18px;
                    left: -6px;
                    width: 12px;
                    height: 12px;
                    background: #ffffff;
                    border-radius: 50%;
                    border: 2px solid #dc3545;
                "></div>
            </div>
        `,
        className: 'saved-location-marker',
        iconSize: [24, 24],
        iconAnchor: [12, 24]
    });
    
    // Adicionar marcador no mapa
    const marker = L.marker([ponto.lat, ponto.lng], {
        icon: savedIcon,
        title: ponto.nome
    }).addTo(map);
    
    // Adicionar popup com informações do ponto
    marker.bindPopup(`
        <div>
            <strong>${ponto.nome}</strong><br>
            <small>Salvo em: ${ponto.data}</small><br>
            <small>Lat: ${ponto.lat.toFixed(6)}</small><br>
            <small>Lng: ${ponto.lng.toFixed(6)}</small>
        </div>
    `);
    
    savedMarkers.push({ marker, ponto });
    
    // Atualizar lista de pontos
    atualizarListaPontos();
    
    mostrarAlerta(`📍 Ponto "${nome}" salvo com sucesso!`, 'success');
}

// Carregar pontos salvos do localStorage
function carregarPontosSalvos() {
    const pontosSalvos = JSON.parse(localStorage.getItem('pontos_venda') || '[]');
    
    pontosSalvos.forEach(ponto => {
        // Criar ícone customizado para pontos salvos
        const savedIcon = L.divIcon({
            html: `
                <div style="
                    width: 0;
                    height: 0;
                    border-left: 12px solid transparent;
                    border-right: 12px solid transparent;
                    border-top: 20px solid #dc3545;
                    position: relative;
                ">
                    <div style="
                        position: absolute;
                        top: -18px;
                        left: -6px;
                        width: 12px;
                        height: 12px;
                        background: #ffffff;
                        border-radius: 50%;
                        border: 2px solid #dc3545;
                    "></div>
                </div>
            `,
            className: 'saved-location-marker',
            iconSize: [24, 24],
            iconAnchor: [12, 24]
        });
        
        const marker = L.marker([ponto.lat, ponto.lng], {
            icon: savedIcon,
            title: ponto.nome
        }).addTo(map);
        
        // Adicionar popup com informações do ponto
        marker.bindPopup(`
            <div>
                <strong>${ponto.nome}</strong><br>
                <small>Salvo em: ${ponto.data}</small><br>
                <small>Lat: ${ponto.lat.toFixed(6)}</small><br>
                <small>Lng: ${ponto.lng.toFixed(6)}</small>
            </div>
        `);
        
        savedMarkers.push({ marker, ponto });
    });
    
    atualizarListaPontos();
}

// Atualizar lista de pontos salvos
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
    pontosSalvos.forEach((ponto, index) => {
        html += `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                <div>
                    <strong>${ponto.nome}</strong><br>
                    <small class="text-muted">${ponto.data}</small>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-primary" onclick="irParaPonto(${ponto.lat}, ${ponto.lng})">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="removerPonto(${ponto.id})">
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
    map.setView([lat, lng], 18);
    
    // Encontrar e abrir popup do marcador
    savedMarkers.forEach(item => {
        if (item.ponto.lat === lat && item.ponto.lng === lng) {
            item.marker.openPopup();
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
            map.removeLayer(item.marker);
            return false;
        }
        return true;
    });
    
    // Atualizar lista
    atualizarListaPontos();
    
    mostrarAlerta('Ponto removido com sucesso!', 'success');
}

// CSS para animação de carregamento
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

// Inicializar quando o Leaflet estiver pronto
if (typeof L !== 'undefined') {
    // Leaflet já está carregado
    document.addEventListener('DOMContentLoaded', function() {
        // Aguardar um pouco para garantir que o elemento está renderizado
        setTimeout(initMap, 100);
    });
} else {
    // Aguardar o Leaflet carregar
    document.addEventListener('DOMContentLoaded', function() {
        const checkLeaflet = setInterval(function() {
            if (typeof L !== 'undefined') {
                clearInterval(checkLeaflet);
                initMap();
            }
        }, 100);
    });
}

// Registrar função global para compatibilidade
window.initLocalizacaoMap = initMap;
</script>
