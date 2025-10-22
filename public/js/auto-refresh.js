/**
 * Sistema de Atualização Automática da Página
 * 
 * Detecta se elementos essenciais não foram carregados corretamente
 * e força um reload da página automaticamente.
 */

(function() {
    'use strict';
    
    // Configurações
    const CONFIG = {
        // Tempo limite para verificar se os elementos carregaram (em ms)
        checkTimeout: 3000,
        
        // Intervalo entre verificações (em ms)
        checkInterval: 1000,
        
        // Máximo de tentativas de reload automático
        maxRetries: 3,
        
        // Chave para armazenar o contador de tentativas
        retryCountKey: 'autoRefreshRetries',
        
        // Elementos essenciais que devem estar presentes
        essentialElements: [
            'body',
            '.content, .container, .main-container',
            'nav, .navbar, .header'
        ],
        
        // Verificações específicas para diferentes páginas
        pageChecks: {
            'index.php': [
                '.user-info, .usuario-info',
                '.nav-tabs, .navigation'
            ],
            'login.php': [
                '#loginForm, #registerForm'
            ]
        }
    };
    
    let checkCount = 0;
    let retryCount = parseInt(sessionStorage.getItem(CONFIG.retryCountKey) || '0');
    
    /**
     * Verifica se um elemento existe na página
     */
    function elementExists(selector) {
        return document.querySelector(selector) !== null;
    }
    
    /**
     * Verifica se pelo menos um elemento de um grupo existe
     */
    function anyElementExists(selectors) {
        return selectors.some(selector => elementExists(selector));
    }
    
    /**
     * Obtém o nome da página atual
     */
    function getCurrentPage() {
        const path = window.location.pathname;
        return path.substring(path.lastIndexOf('/') + 1);
    }
    
    /**
     * Verifica se a página carregou corretamente
     */
    function checkPageLoad() {
        const currentPage = getCurrentPage();
        let isPageHealthy = true;
        const issues = [];
        
        // Verificar elementos essenciais básicos
        CONFIG.essentialElements.forEach(selector => {
            if (!anyElementExists(selector.split(', '))) {
                isPageHealthy = false;
                issues.push(`Elemento essencial não encontrado: ${selector}`);
            }
        });
        
        // Verificar elementos específicos da página
        const pageSpecificChecks = CONFIG.pageChecks[currentPage];
        if (pageSpecificChecks) {
            pageSpecificChecks.forEach(selector => {
                if (!anyElementExists(selector.split(', '))) {
                    isPageHealthy = false;
                    issues.push(`Elemento específico da página não encontrado: ${selector}`);
                }
            });
        }
        
        // Verificar se o usuário está logado (para páginas que requerem)
        if (currentPage !== 'login.php' && !elementExists('body[data-no-auth]')) {
            const user = sessionStorage.getItem('user');
            if (!user || user === 'null') {
                // Verificar se não está na tela de login
                if (!elementExists('#loginForm')) {
                    isPageHealthy = false;
                    issues.push('Usuário não está logado e não está na tela de login');
                }
            }
        }
        
        // Verificar se há erros JavaScript críticos
        if (window.hasJSErrors) {
            isPageHealthy = false;
            issues.push('Erros JavaScript críticos detectados');
        }
        
        return { isHealthy: isPageHealthy, issues };
    }
    
    /**
     * Força o reload da página
     */
    function forceReload() {
        console.log('🔄 Auto-refresh: Forçando reload da página...');
        
        // Incrementar contador de tentativas
        retryCount++;
        sessionStorage.setItem(CONFIG.retryCountKey, retryCount.toString());
        
        // Limpar cache se possível
        if ('caches' in window) {
            caches.keys().then(function(names) {
                names.forEach(function(name) {
                    caches.delete(name);
                });
            });
        }
        
        // Forçar reload com bypass de cache
        window.location.reload(true);
    }
    
    /**
     * Reseta o contador de tentativas
     */
    function resetRetryCount() {
        retryCount = 0;
        sessionStorage.removeItem(CONFIG.retryCountKey);
    }
    
    /**
     * Executa a verificação periódica
     */
    function performCheck() {
        checkCount++;
        const pageHealth = checkPageLoad();
        
        if (!pageHealth.isHealthy) {
            console.warn('⚠️ Auto-refresh: Problemas detectados na página:', pageHealth.issues);
            
            // Se ainda não excedeu o número máximo de tentativas
            if (retryCount < CONFIG.maxRetries) {
                forceReload();
                return;
            } else {
                console.error('❌ Auto-refresh: Máximo de tentativas excedido. Parando verificações automáticas.');
                // Mostrar notificação ao usuário
                showUserNotification();
                return;
            }
        }
        
        // Se chegou aqui, a página está saudável
        if (checkCount === 1) {
            resetRetryCount();
            console.log('✅ Auto-refresh: Página carregada corretamente');
        }
        
        // Continuar verificando por um tempo
        if (checkCount * CONFIG.checkInterval < CONFIG.checkTimeout) {
            setTimeout(performCheck, CONFIG.checkInterval);
        }
    }
    
    /**
     * Mostra notificação ao usuário sobre problemas
     */
    function showUserNotification() {
        const notification = document.createElement('div');
        notification.innerHTML = `
            <div style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: #f8d7da;
                color: #721c24;
                padding: 15px;
                border: 1px solid #f5c6cb;
                border-radius: 5px;
                z-index: 9999;
                max-width: 350px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            ">
                <strong>⚠️ Problema detectado</strong><br>
                A página pode não estar funcionando corretamente. 
                <br><br>
                <button onclick="window.location.reload(true)" style="
                    background: #721c24;
                    color: white;
                    border: none;
                    padding: 5px 10px;
                    border-radius: 3px;
                    cursor: pointer;
                    margin-right: 10px;
                ">Recarregar</button>
                <button onclick="this.parentElement.parentElement.remove()" style="
                    background: transparent;
                    color: #721c24;
                    border: 1px solid #721c24;
                    padding: 5px 10px;
                    border-radius: 3px;
                    cursor: pointer;
                ">Fechar</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remover após 30 segundos
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 30000);
    }
    
    /**
     * Detecta erros JavaScript críticos
     */
    function setupErrorDetection() {
        window.addEventListener('error', function(e) {
            // Marcar que houve erro JS crítico
            if (e.error && e.error.stack) {
                window.hasJSErrors = true;
                console.error('Auto-refresh: Erro JavaScript detectado:', e.error);
            }
        });
        
        window.addEventListener('unhandledrejection', function(e) {
            // Marcar que houve erro de Promise não tratada
            window.hasJSErrors = true;
            console.error('Auto-refresh: Promise rejeitada não tratada:', e.reason);
        });
    }
    
    /**
     * Inicializa o sistema de auto-refresh
     */
    function initialize() {
        // Só executar se não estivermos em modo de desenvolvimento
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.log('🔧 Auto-refresh: Modo de desenvolvimento detectado');
        }
        
        setupErrorDetection();
        
        // Aguardar o DOM estar pronto
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(performCheck, 500);
            });
        } else {
            setTimeout(performCheck, 500);
        }
        
        console.log('🚀 Auto-refresh: Sistema inicializado');
    }
    
    // Inicializar quando o script for carregado
    initialize();
    
})();