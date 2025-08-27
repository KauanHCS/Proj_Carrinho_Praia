<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Vendas - Carrinho de Praia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Alert de baixo estoque -->
    <div id="alertLowStock" class="alert-banner alert alert-warning d-none" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <strong>Atenção!</strong> <span id="alertMessage"></span>
    </div>

    <!-- Navbar  dsaufsdhfisdfu-->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-cart"></i> Carrinho de Praia
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#vendas" onclick="showTab('vendas')">
                            <i class="bi bi-cash"></i> Vendas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#produtos" onclick="showTab('produtos')">
                            <i class="bi bi-box"></i> Produtos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#estoque" onclick="showTab('estoque')">
                            <i class="bi bi-archive"></i> Estoque
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#relatorios" onclick="showTab('relatorios')">
                            <i class="bi bi-graph-up"></i> Relatórios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#localizacao" onclick="showTab('localizacao')">
                            <i class="bi bi-geo-alt"></i> Localização
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <div class="tab-content">
            <!-- Tab Vendas -->
            <div class="tab-pane fade" id="vendas">
                <?php 
                require_once 'config/database.php';
                include 'templates/vendas.php'; 
                ?>
            </div>

            <!-- Tab Produtos -->
            <div class="tab-pane fade" id="produtos">
                <?php 
                require_once 'config/database.php';
                include 'templates/produtos.php'; 
                ?>
            </div>

            <!-- Tab Estoque -->
            <div class="tab-pane fade" id="estoque">
                <?php 
                require_once 'config/database.php';
                include 'templates/estoque.php'; 
                ?>
            </div>

            <!-- Tab Relatórios -->
            <div class="tab-pane fade" id="relatorios">
                <?php 
                require_once 'config/database.php';
                include 'templates/relatorios.php'; 
                ?>
            </div>

            <!-- Tab Localização -->
            <div class="tab-pane fade" id="localizacao">
                <?php 
                require_once 'config/database.php';
                include 'templates/localizacao.php'; 
                ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer text-center">
        <div class="container">
            <small>Sistema de Gestão para Carrinhos de Praia | © 2023</small>
        </div>
    </div>

    <!-- Modais -->
    <?php include 'templates/modais.php'; ?>

    <!-- Scripts - ORDEM CORRETA -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    
    <!-- Google Maps com callback -->
    <script>
        // Função para inicializar o mapa quando o Google Maps estiver disponível
        function initMap() {
            const centro = { lat: -23.550520, lng: -46.633308 };
            
            const map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: centro
            });
            
            // Verifique se AdvancedMarkerElement está disponível
            if (typeof google.maps.marker !== 'undefined' && typeof google.maps.marker.AdvancedMarkerElement !== 'undefined') {
                // Use AdvancedMarkerElement se disponível
                const marker = new google.maps.marker.AdvancedMarkerElement({
                    position: centro,
                    map: map,
                    title: 'Ponto de Venda'
                });
            } else {
                // Use o Marker antigo como fallback
                const marker = new google.maps.Marker({
                    position: centro,
                    map: map,
                    title: 'Ponto de Venda'
                });
            }
        }
        
        // Carregar o Google Maps com callback
        function loadGoogleMaps() {
            if (typeof google !== 'undefined' && google.maps) {
                initMap();
            } else {
                // Tentar novamente após 1 segundo se ainda não estiver disponível
                setTimeout(loadGoogleMaps, 1000);
            }
        }
        
        // Função para mostrar a tab correta
        function showTab(tabName) {
            // Remover classe active de todas as tabs
            document.querySelectorAll('.tab-pane').forEach(tab => {
                tab.classList.remove('show', 'active');
            });
            
            // Remover classe active de todos os links da navbar
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Adicionar classe active à tab selecionada
            const selectedTab = document.getElementById(tabName);
            if (selectedTab) {
                selectedTab.classList.add('show', 'active');
            }
            
            // Adicionar classe active ao link da navbar
            const navLink = document.querySelector(`[data-bs-target="#${tabName}"]`);
            if (navLink) {
                navLink.classList.add('active');
            }
        }
        
        // Inicializar com a tab de vendas ativa
        document.addEventListener('DOMContentLoaded', function() {
            showTab('vendas');
        });
    </script>
    
    <!-- Script do Google Maps com sua chave e bibliotecas adicionais -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAd_IHsV5NuNkz2xAt-etL9x5eJcsaO3VQ&libraries=visualization,marker&callback=loadGoogleMaps" loading="eager"></script>
</body>
</html>
