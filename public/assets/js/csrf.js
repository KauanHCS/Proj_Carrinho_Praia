/**
 * csrf.js - Injetor automático de token CSRF.
 *
 * Lê o token de <meta name="csrf-token"> e instala wrappers em window.fetch e
 * XMLHttpRequest para que toda requisição POST/PUT/PATCH/DELETE para o mesmo
 * origin envie o header X-CSRF-Token automaticamente.
 *
 * Para FormData, também injeta `csrf_token` no corpo, garantindo compatibilidade
 * com handlers que ainda leem $_POST['csrf_token'].
 */
(function () {
    'use strict';

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    const STATE_CHANGING_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    function isStateChanging(method) {
        return STATE_CHANGING_METHODS.includes((method || 'GET').toUpperCase());
    }

    function isSameOrigin(url) {
        try {
            const u = new URL(url, window.location.href);
            return u.origin === window.location.origin;
        } catch (e) {
            // URL relativa → mesmo origin
            return true;
        }
    }

    // Wrap fetch
    if (typeof window.fetch === 'function') {
        const originalFetch = window.fetch;
        window.fetch = function (input, init) {
            init = init || {};
            const method = (init.method || (typeof input !== 'string' ? input.method : 'GET') || 'GET').toUpperCase();
            const url    = typeof input === 'string' ? input : input.url;

            if (isStateChanging(method) && isSameOrigin(url)) {
                const token = getCsrfToken();
                if (token) {
                    init.headers = new Headers(init.headers || {});
                    if (!init.headers.has('X-CSRF-Token')) {
                        init.headers.set('X-CSRF-Token', token);
                    }
                    // Se body é FormData, injeta também como campo (compatibilidade).
                    if (init.body instanceof FormData && !init.body.has('csrf_token')) {
                        init.body.append('csrf_token', token);
                    }
                }
            }
            return originalFetch.call(this, input, init);
        };
    }

    // Wrap XMLHttpRequest
    if (typeof window.XMLHttpRequest === 'function') {
        const origOpen = XMLHttpRequest.prototype.open;
        const origSend = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.open = function (method, url) {
            this.__csrfMethod = method;
            this.__csrfUrl    = url;
            return origOpen.apply(this, arguments);
        };

        XMLHttpRequest.prototype.send = function (body) {
            if (isStateChanging(this.__csrfMethod) && isSameOrigin(this.__csrfUrl)) {
                const token = getCsrfToken();
                if (token) {
                    try { this.setRequestHeader('X-CSRF-Token', token); } catch (e) { /* já enviado */ }
                    if (body instanceof FormData && !body.has('csrf_token')) {
                        body.append('csrf_token', token);
                    }
                }
            }
            return origSend.call(this, body);
        };
    }

    // Expor utilitário global (caso código antigo precise montar formulário manual)
    window.__csrfToken = getCsrfToken;
})();
