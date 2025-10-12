<?php
// Inicializar sessão PHP para suporte multi-usuário
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Vendas - Carrinho de Praia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Layout com Header Azul e Sidebar */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding-bottom: 80px; /* Espaço para o footer */
        }
        
        /* Header Azul Fixo no Topo */
        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: linear-gradient(135deg, #0066cc, #0099ff);
            color: white;
            z-index: 1001;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-left {
            position: absolute;
            left: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-center {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
        }
        
        .sidebar-toggle {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            font-size: 1.4rem;
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-toggle:hover {
            background: rgba(255,255,255,0.25);
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(255,255,255,0.2);
        }
        
        .sidebar-toggle:active {
            transform: scale(0.95);
        }
        
        .sidebar-toggle i {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1.4rem;
        }
        
        /* Animação do ícone hamburger */
        .sidebar-toggle.collapsed i::before {
            content: "\F479"; /* bi-list */
        }
        
        .sidebar-toggle:not(.collapsed) i::before {
            content: "\F659"; /* bi-x-lg */
        }
        
        .brand {
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
        
        .brand i {
            font-size: 2rem;
            color: #ffffff;
            filter: drop-shadow(0 0 8px rgba(255,255,255,0.3));
        }
        
        .header-right {
            position: absolute;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar-header {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
            object-fit: cover;
        }
        
        .user-details-header h6 {
            margin: 0;
            font-weight: 600;
            color: white;
        }
        
        .user-details-header small {
            color: rgba(255,255,255,0.8);
        }
        
        .logout-header {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        
        .logout-header:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 70px;
            left: 0;
            height: calc(100vh - 70px - 60px); /* Ajuste para o footer */
            width: 280px;
            background: linear-gradient(180deg, #0066cc, #004499);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(0);
            box-shadow: 2px 0 15px rgba(0,0,0,0.3);
            display: block;
            visibility: visible;
        }
        
        .sidebar.collapsed {
            transform: translateX(-280px);
        }
        
        
        .brand {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
        
        .user-details h6 {
            margin: 0;
            font-weight: 600;
        }
        
        .user-details small {
            opacity: 0.8;
        }
        
        .sidebar-nav {
            padding: 0;
            margin: 0;
            list-style: none;
        }
        
        .sidebar-nav li {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-nav a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-nav a.active {
            background: rgba(255,255,255,0.2);
            border-right: 4px solid #ffffff;
        }
        
        .sidebar-nav i {
            width: 20px;
            margin-right: 15px;
            font-size: 1.1rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            margin-top: 70px;
            min-height: calc(100vh - 70px - 60px); /* Ajuste para o footer */
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 20px;
            padding-bottom: 80px; /* Espaço adicional para o footer */
        }
        
        .main-content.collapsed {
            margin-left: 0;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100vw;
            height: 60px;
            background: linear-gradient(135deg, #0066cc, #0099ff);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            border-top: 1px solid rgba(255,255,255,0.1);
            margin: 0;
            padding: 0;
        }
        
        .footer .container {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0;
        }
        
        .footer small {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.9);
            text-align: center;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        
        /* Modo Escuro Global */
        body.dark-mode {
            background-color: #1a1a1a !important;
            color: #ffffff !important;
        }
        
        body.dark-mode .main-header {
            background: linear-gradient(135deg, #1a1a1a, #2d2d2d) !important;
        }
        
        body.dark-mode .sidebar {
            background: linear-gradient(180deg, #1a1a1a, #000000) !important;
        }
        
        body.dark-mode .sidebar-nav a:hover {
            background: rgba(255,255,255,0.2) !important;
        }
        
        body.dark-mode .main-content {
            background-color: #1a1a1a !important;
        }
        
        body.dark-mode .card {
            background-color: #2d2d2d !important;
            border-color: #404040 !important;
            color: #ffffff !important;
        }
        
        body.dark-mode .card-header {
            background: linear-gradient(135deg, #333333, #404040) !important;
            border-color: #555555 !important;
        }
        
        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background-color: #404040 !important;
            border-color: #555555 !important;
            color: #ffffff !important;
        }
        
        body.dark-mode .form-control:focus,
        body.dark-mode .form-select:focus {
            background-color: #4a4a4a !important;
            border-color: #0066cc !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25) !important;
        }
        
        body.dark-mode .form-control:disabled,
        body.dark-mode .form-select:disabled {
            background-color: #333333 !important;
            border-color: #555555 !important;
            color: #cccccc !important;
        }
        
        body.dark-mode .btn-outline-primary {
            border-color: #0066cc !important;
            color: #0066cc !important;
        }
        
        body.dark-mode .btn-outline-primary:hover {
            background-color: #0066cc !important;
            color: #ffffff !important;
        }
        
        body.dark-mode .btn-outline-success {
            border-color: #28a745 !important;
            color: #28a745 !important;
        }
        
        body.dark-mode .btn-outline-success:hover {
            background-color: #28a745 !important;
            color: #ffffff !important;
        }
        
        body.dark-mode .btn-outline-info {
            border-color: #17a2b8 !important;
            color: #17a2b8 !important;
        }
        
        body.dark-mode .btn-outline-info:hover {
            background-color: #17a2b8 !important;
            color: #ffffff !important;
        }
        
        body.dark-mode .btn-outline-warning {
            border-color: #ffc107 !important;
            color: #ffc107 !important;
        }
        
        body.dark-mode .btn-outline-warning:hover {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }
        
        body.dark-mode .btn-outline-danger {
            border-color: #dc3545 !important;
            color: #dc3545 !important;
        }
        
        body.dark-mode .btn-outline-danger:hover {
            background-color: #dc3545 !important;
            color: #ffffff !important;
        }
        
        body.dark-mode .text-muted {
            color: #aaaaaa !important;
        }
        
        body.dark-mode .bg-light {
            background-color: #404040 !important;
        }
        
        body.dark-mode .border {
            border-color: #555555 !important;
        }
        
        body.dark-mode .table {
            color: #ffffff !important;
        }
        
        body.dark-mode .table-striped tbody tr:nth-of-type(odd) {
            background-color: #333333 !important;
        }
        
        body.dark-mode .alert {
            border-color: #555555 !important;
        }
        
        body.dark-mode .alert-info {
            background-color: #1e3a5f !important;
            border-color: #17a2b8 !important;
            color: #b3d7e6 !important;
        }
        
        body.dark-mode .alert-success {
            background-color: #1e4d2b !important;
            border-color: #28a745 !important;
            color: #b3e6c0 !important;
        }
        
        body.dark-mode .alert-warning {
            background-color: #5c4a1a !important;
            border-color: #ffc107 !important;
            color: #ffe4a3 !important;
        }
        
        body.dark-mode .alert-danger {
            background-color: #5a1e23 !important;
            border-color: #dc3545 !important;
            color: #f1b3ba !important;
        }
        
        body.dark-mode .footer {
            background: linear-gradient(135deg, #1a1a1a, #2d2d2d) !important;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            body {
                padding-bottom: 70px; /* Espaço maior para footer no mobile */
            }
            
            .sidebar {
                width: 280px;
                transform: translateX(-280px); /* Começa oculta no mobile */
            }
            
            .sidebar:not(.collapsed) {
                transform: translateX(0); /* Fica visível quando não colapsada */
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px 10px;
                padding-bottom: 100px; /* Espaço extra para footer */
            }
            
            .main-content.collapsed {
                margin-left: 0; /* Sempre 0 no mobile */
            }
            
            .user-details-header {
                display: none;
            }
            
            .brand {
                font-size: 1.4rem;
            }
            
            .brand i {
                font-size: 1.6rem;
            }
            
            .footer {
                height: 60px;
            }
            
            .footer small {
                font-size: 0.85rem;
                padding: 0 15px;
            }
            
            /* Melhor visibilidade do botão toggle no mobile */
            .sidebar-toggle {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }
            
            .sidebar-toggle:hover {
                background: rgba(255,255,255,0.3);
            }
            
            /* Alert banner ajustado no mobile */
            .alert-banner {
                left: 0 !important;
                right: 10px;
                margin: 0 5px;
            }
        }
        
        .content {
            padding: 30px;
        }
        
        .logout-btn {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }
        
        .logout-btn i {
            margin-right: 8px;
        }
        
        /* Overlay para mobile */
        .sidebar-overlay {
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        
        /* Alert de estoque */
        .alert-banner {
            position: fixed;
            top: 70px;
            left: 280px;
            right: 0;
            z-index: 999;
            border-radius: 0;
            margin: 0;
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .alert-banner.collapsed {
            left: 0;
        }
        
        @media (max-width: 768px) {
            .alert-banner {
                left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Overlay para mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Header Principal Azul -->
    <div class="main-header">
        <div class="header-left">
            <button class="sidebar-toggle collapsed" id="sidebarToggle" title="Mostrar/Ocultar Menu">
                <i class="bi"></i>
            </button>
        </div>
        
        <div class="header-center">
            <div class="brand">
                <i class="bi bi-cart4"></i>
                <span>Carrinho de Praia</span>
            </div>
        </div>
        
        <div class="header-right">
            <div class="user-info-header">
                <div class="user-details-header">
                    <h6 id="headerUserName">Carregando...</h6>
                    <small id="headerUserEmail">carregando@email.com</small>
                </div>
                <img id="headerUserAvatar" 
                     class="user-avatar-header" 
                     src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDUiIGhlaWdodD0iNDUiIHZpZXdCb3g9IjAgMCA0NSA0NSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjIuNSIgY3k9IjIyLjUiIHI9IjIyLjUiIGZpbGw9IiMwMDY2Q0MiLz4KPHN2ZyB4PSI5IiB5PSI5IiB3aWR0aD0iMjciIGhlaWdodD0iMjciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMmMwIDAgMy0zIDMtNS41UzE1IDMgMTIgM3MtMyAxLjUtMyAzLjUgMyA1LjUgMyA1LjV6IiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRjLTQuNSAwLTguMiAyLjMtOC4yIDUuMiAwIDEuMSA0LjcgMS44IDguMiAxLjhzOC4yLS43IDguMi0xLjhjMC0yLjktMy43LTUuMi04LjItNS4yeiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cjwvc3ZnPg=="
                     alt="Avatar"
                     title="Clique para acessar seu perfil"
                     style="cursor: pointer;"
                     onclick="showTab('perfil')">
            </div>
            <a href="#" class="logout-header" id="headerLogoutBtn">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sair</span>
            </a>
        </div>
    </div>
    
    <!-- Alert de baixo estoque -->
    <div id="alertLowStock" class="alert-banner alert alert-warning d-none" role="alert" style="top: 80px; left: 300px; right: 20px;">
        <i class="bi bi-exclamation-triangle"></i> <strong>Atenção!</strong> <span id="alertMessage"></span>
    </div>
    
    <!-- Sidebar -->
    <div class="sidebar collapsed" id="sidebar">
        
        <!-- Menu de Navegação -->
        <ul class="sidebar-nav">
            <li>
                <a href="#" onclick="showTab('vendas')" data-tab="vendas">
                    <i class="bi bi-cash"></i>
                    <span>Vendas</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="showTab('produtos')" data-tab="produtos">
                    <i class="bi bi-box"></i>
                    <span>Produtos</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="showTab('estoque')" data-tab="estoque">
                    <i class="bi bi-archive"></i>
                    <span>Estoque</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="showTab('relatorios')" data-tab="relatorios">
                    <i class="bi bi-graph-up"></i>
                    <span>Relatórios</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="showTab('localizacao')" data-tab="localizacao">
                    <i class="bi bi-geo-alt"></i>
                    <span>Localização</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="showTab('perfil')" data-tab="perfil">
                    <i class="bi bi-person-circle"></i>
                    <span>Perfil</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Conteúdo Principal -->
    <div class="main-content collapsed" id="mainContent">
            <div class="tab-content">
                <!-- Tab Vendas -->
                <div class="tab-pane fade" id="vendas">
                    <?php 
                    require_once '../config/database.php';
                    include '../src/Views/vendas.php';
                    ?>
                </div>

                <!-- Tab Produtos -->
                <div class="tab-pane fade" id="produtos">
                    <?php 
                    require_once '../config/database.php';
                    include '../src/Views/produtos.php';
                    ?>
                </div>

                <!-- Tab Estoque -->
                <div class="tab-pane fade" id="estoque">
                    <?php 
                    require_once '../config/database.php';
                    include '../src/Views/estoque.php';
                    ?>
                </div>

                <!-- Tab Relatórios -->
                <div class="tab-pane fade" id="relatorios">
                    <?php 
                    require_once '../config/database.php';
                    include '../src/Views/relatorios.php';
                    ?>
                </div>

                <!-- Tab Localização -->
                <div class="tab-pane fade" id="localizacao">
                    <?php 
                    require_once '../config/database.php';
                    include '../src/Views/localizacao.php';
                    ?>
                </div>

                <!-- Tab Perfil -->
                <div class="tab-pane fade" id="perfil">
                    <?php 
                    include '../src/Views/perfil.php';
                    ?>
                </div>
            </div>
    </div>

    <!-- Footer -->
    <div class="footer text-center">
        <div class="container">
            <small>Sistema de Gestão para Carrinhos de Praia | © 2025</small>
        </div>
    </div>

    <!-- Modais -->
    <?php include '../src/Views/modais.php'; ?>

    <!-- Scripts - ORDEM CORRETA -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/validation.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/produtos-actions.js"></script>
    <script src="assets/js/filtro-simple.js"></script>
    
    <!-- Scripts principais -->
    <script>
        // Função global removida para evitar conflitos com Google Maps
        
        // Função para mostrar a tab correta
        function showTab(tabName) {
            // Remover classe active de todas as tabs
            document.querySelectorAll('.tab-pane').forEach(tab => {
                tab.classList.remove('show', 'active');
            });
            
            // Remover classe active de todos os links da sidebar
            document.querySelectorAll('.sidebar-nav a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Adicionar classe active à tab selecionada
            const selectedTab = document.getElementById(tabName);
            if (selectedTab) {
                selectedTab.classList.add('show', 'active');
            }
            
            // Adicionar classe active ao link da sidebar
            const sidebarLink = document.querySelector(`[data-tab="${tabName}"]`);
            if (sidebarLink) {
                sidebarLink.classList.add('active');
            }
            
            // Atualizar título da página
            const pageTitle = document.getElementById('pageTitle');
            const titles = {
                'vendas': 'Vendas',
                'produtos': 'Produtos',
                'estoque': 'Estoque',
                'relatorios': 'Relatórios',
                'localizacao': 'Localização',
                'perfil': 'Meu Perfil'
            };
            if (pageTitle && titles[tabName]) {
                pageTitle.textContent = titles[tabName];
            }
            
            // Carregar gráficos específicos da aba quando necessário
            if (tabName === 'relatorios') {
                console.log('📈 Aba de relatórios ativada, carregando gráficos...');
                setTimeout(() => {
                    console.log('🔍 Verificando função atualizarGraficoVendas...', typeof atualizarGraficoVendas);
                    if (typeof atualizarGraficoVendas === 'function') {
                        console.log('✅ Chamando atualizarGraficoVendas...');
                        atualizarGraficoVendas();
                    } else {
                        console.error('❌ Função atualizarGraficoVendas não encontrada!');
                    }
                }, 100);
            }
            
            // Fechar sidebar após seleção se estiver em tela pequena
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.add('collapsed');
                    const mainContent = document.getElementById('mainContent');
                    if (mainContent) {
                        mainContent.classList.add('collapsed');
                    }
                }
            }
        }
        
        // Função para corrigir gráficos
        function corrigirGrafico() {
            if (typeof corrigirGraficoDashboard === 'function') {
                corrigirGraficoDashboard();
            } else if (typeof atualizarGraficoVendas === 'function') {
                atualizarGraficoVendas();
            } else {
                console.warn('Função de gráfico não encontrada');
            }
        }
        
        // Verificar login e exibir informações do usuário
        function checkLoginAndDisplayUser() {
            const user = sessionStorage.getItem('user');
            if (!user) {
                // Se não estiver logado, redirecionar para o login
                window.location.href = 'login.php';
                return;
            }
            
            const userData = JSON.parse(user);
            
            // Exibir informações do usuário no header
            const defaultAvatarSmall = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDUiIGhlaWdodD0iNDUiIHZpZXdCb3g9IjAgMCA0NSA0NSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjIuNSIgY3k9IjIyLjUiIHI9IjIyLjUiIGZpbGw9IiMwMDY2Q0MiLz4KPHN2ZyB4PSI5IiB5PSI5IiB3aWR0aD0iMjciIGhlaWdodD0iMjciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMmMwIDAgMy0zIDMtNS41UzE1IDMgMTIgM3MtMyAxLjUtMyAzLjUgMyA1LjUgMyA1LjV6IiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRjLTQuNSAwLTguMiAyLjMtOC4yIDUuMiAwIDEuMSA0LjcgMS44IDguMiAxLjhzOC4yLS43IDguMi0xLjhjMC0yLjktMy43LTUuMi04LjItNS4yeiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cjwvc3ZnPg==";
            document.getElementById('headerUserAvatar').src = userData.imageUrl || defaultAvatarSmall;
            document.getElementById('headerUserName').textContent = userData.name;
            document.getElementById('headerUserEmail').textContent = userData.email;
        }
        
        // Controle da sidebar otimizado
        function toggleSidebar() {
            console.log('🔄 Toggle sidebar clicado!');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleBtn = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');
            const alertBanner = document.getElementById('alertLowStock');
            
            if (!sidebar || !mainContent) {
                console.error('❌ Elementos não encontrados:', { sidebar, mainContent });
                return;
            }
            
            const isCurrentlyCollapsed = sidebar.classList.contains('collapsed');
            console.log('📊 Estado atual da sidebar:', isCurrentlyCollapsed ? 'oculta' : 'visível');
            
            if (isCurrentlyCollapsed) {
                // Mostrar sidebar
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('collapsed');
                if (toggleBtn) toggleBtn.classList.remove('collapsed');
                if (alertBanner) alertBanner.classList.remove('collapsed');
            } else {
                // Ocultar sidebar
                sidebar.classList.add('collapsed');
                mainContent.classList.add('collapsed');
                if (toggleBtn) toggleBtn.classList.add('collapsed');
                if (alertBanner) alertBanner.classList.add('collapsed');
            }
            
            // Gerenciar overlay em mobile
            if (overlay) {
                if (!sidebar.classList.contains('collapsed') && window.innerWidth <= 768) {
                    overlay.classList.add('show');
                } else {
                    overlay.classList.remove('show');
                }
            }
            
            console.log('✅ Sidebar estado:', sidebar.classList.contains('collapsed') ? 'oculta' : 'visível');
        }
        
        // Função para fechar sidebar
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('sidebarToggle');
            
            if (sidebar && !sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('collapsed');
                if (mainContent) mainContent.classList.add('collapsed');
                if (overlay) overlay.classList.remove('show');
                
                // Resetar ícone
                if (toggleBtn) {
                    const icon = toggleBtn.querySelector('i');
                    if (icon) icon.style.transform = 'rotate(0deg)';
                }
            }
        }
        
        
        // Eventos de logout
        document.getElementById('headerLogoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            sessionStorage.removeItem('user');
            window.location.href = 'login.php';
        });
        
        // Toggle da sidebar
        const toggleButton = document.getElementById('sidebarToggle');
        if (toggleButton) {
            console.log('✅ Botão toggle encontrado e evento adicionado');
            toggleButton.addEventListener('click', function(e) {
                console.log('💱 Clique detectado no botão toggle');
                e.preventDefault();
                e.stopPropagation();
                toggleSidebar();
            });
        } else {
            console.error('❌ Botão sidebarToggle não encontrado!');
        }
        
        // Event listener para o overlay
        const overlay = document.getElementById('sidebarOverlay');
        if (overlay) {
            overlay.addEventListener('click', function() {
                console.log('💆 Clique no overlay detectado');
                closeSidebar();
            });
        }
        
        // Funções globais para debug
        window.toggleSidebar = toggleSidebar;
        window.debugSidebar = function() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                const estiloComputado = window.getComputedStyle(sidebar);
                console.log('Debug Sidebar:', {
                    elemento: sidebar,
                    classes: Array.from(sidebar.classList),
                    estiloComputado: {
                        display: estiloComputado.display,
                        visibility: estiloComputado.visibility,
                        opacity: estiloComputado.opacity,
                        transform: estiloComputado.transform,
                        position: estiloComputado.position,
                        left: estiloComputado.left,
                        top: estiloComputado.top,
                        width: estiloComputado.width,
                        height: estiloComputado.height,
                        zIndex: estiloComputado.zIndex
                    }
                });
            } else {
                console.log('Sidebar não encontrada');
            }
        };
        
        // Fechar sidebar ao clicar fora (desktop)
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            // Se a sidebar está visível e clicou fora dela (apenas desktop)
            if (sidebar && !sidebar.classList.contains('collapsed') && window.innerWidth > 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    closeSidebar();
                }
            }
        });
        
        // Esc para fechar sidebar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });
        
        // Listener para redimensionamento da janela
        window.addEventListener('resize', function() {
            const overlay = document.getElementById('sidebarOverlay');
            
            // Remover overlay se mudou para desktop
            if (overlay && window.innerWidth > 768) {
                overlay.classList.remove('show');
            }
            
            console.log('🔄 Redimensionamento da janela');
        });
        
        // Carregar modo escuro global
        function carregarModoEscuroGlobal() {
            const configs = JSON.parse(localStorage.getItem('configuracoes') || '{}');
            if (configs.modoEscuro) {
                document.body.classList.add('dark-mode');
                console.log('🌙 Modo escuro ativado globalmente');
            }
        }
        
        // Aplicar modo escuro imediatamente se configurado
        carregarModoEscuroGlobal();
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 DOM carregado, inicializando...');
            checkLoginAndDisplayUser();
            
            // Aplicar modo escuro novamente após carregar DOM
            carregarModoEscuroGlobal();
            
            // Definir estado inicial da sidebar baseado no tamanho da tela
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleButton = document.getElementById('sidebarToggle');
            
            console.log('📊 Estado inicial:', {
                sidebar: !!sidebar,
                mainContent: !!mainContent,
                toggleButton: !!toggleButton,
                screenWidth: window.innerWidth
            });
            
            // Estado inicial baseado no tamanho da tela
            if (window.innerWidth > 768) {
                // Desktop: sidebar visível por padrão
                if (sidebar) sidebar.classList.remove('collapsed');
                if (mainContent) mainContent.classList.remove('collapsed');
                if (toggleButton) toggleButton.classList.remove('collapsed');
            } else {
                // Mobile: sidebar oculta por padrão
                if (sidebar) sidebar.classList.add('collapsed');
                if (mainContent) mainContent.classList.add('collapsed');
                if (toggleButton) toggleButton.classList.add('collapsed');
            }
            
            console.log('✅ Estado inicial configurado');
            showTab('vendas');
        });
    </script>
    
    <!-- OpenStreetMap usado na aba de localização (carregado via Leaflet) -->
</body>
</html>