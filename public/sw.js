// Service Worker para PWA - Sistema Carrinho de Praia
const CACHE_NAME = 'carrinho-praia-v1.3.0'; // Incrementado para forçar atualização
const OFFLINE_PAGE = '/offline.html';

// Recursos para cache
const urlsToCache = [
    './',
    './index.php',
    './login.php',
    './assets/css/style.css',
    './assets/js/main.js',
    './assets/js/validation.js',
    './assets/js/produtos-actions.js',
    './assets/js/filtro-simple.js',
    // CDN resources
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/chart.js'
];

// Install event - cache resources
self.addEventListener('install', event => {
    console.log('SW: Install event');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('SW: Cache opened');
                return cache.addAll(urlsToCache.map(url => new Request(url, {
                    credentials: 'same-origin'
                })));
            })
            .then(() => {
                console.log('SW: All resources cached');
                return self.skipWaiting();
            })
            .catch(err => {
                console.error('SW: Cache failed:', err);
            })
    );
});

// Activate event - clean old caches
self.addEventListener('activate', event => {
    console.log('SW: Activate event');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('SW: Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                return self.clients.claim();
            })
    );
});

// Fetch event - serve cached resources
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip Chrome extensions
    if (url.protocol === 'chrome-extension:') {
        return;
    }
    
    // Skip external domains except CDNs
    if (!url.href.startsWith(self.location.origin) && 
        !url.href.includes('cdn.jsdelivr.net') &&
        !url.href.includes('fonts.googleapis.com') &&
        !url.href.includes('fonts.gstatic.com')) {
        return;
    }
    
    // NUNCA cachear actions.php - sempre buscar da rede
    if (url.pathname.includes('actions.php')) {
        event.respondWith(
            fetch(request)
                .then(response => response)
                .catch(error => {
                    console.error('SW: API request failed:', error);
                    return new Response(JSON.stringify({
                        success: false,
                        message: 'Erro de conexão. Verifique sua internet.'
                    }), {
                        status: 503,
                        headers: { 'Content-Type': 'application/json' }
                    });
                })
        );
        return;
    }
    
    event.respondWith(
        caches.match(request)
            .then(response => {
                // Return cached version if available
                if (response) {
                    console.log('SW: Serving from cache:', request.url);
                    return response;
                }
                
                // Otherwise fetch from network
                return fetch(request)
                    .then(response => {
                        // Check if we received a valid response
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }
                        
                        // Clone the response for caching
                        const responseToCache = response.clone();
                        
                        // Cache successful GET requests
                        if (request.method === 'GET') {
                            caches.open(CACHE_NAME)
                                .then(cache => {
                                    cache.put(request, responseToCache);
                                })
                                .catch(err => console.warn('SW: Cache put failed:', err));
                        }
                        
                        return response;
                    })
                    .catch(error => {
                        console.warn('SW: Fetch failed:', error);
                        
                        // Return offline page for HTML requests
                        if (request.headers.get('accept').includes('text/html')) {
                            return caches.match(OFFLINE_PAGE)
                                .then(response => {
                                    return response || new Response('Offline - Sistema indisponível', {
                                        status: 503,
                                        statusText: 'Service Unavailable',
                                        headers: new Headers({
                                            'Content-Type': 'text/html; charset=utf-8'
                                        })
                                    });
                                });
                        }
                        
                        // For other requests, just fail
                        return Promise.reject(error);
                    });
            })
    );
});

// Background sync for offline actions
self.addEventListener('sync', event => {
    console.log('SW: Background sync:', event.tag);
    
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

// Push notifications
self.addEventListener('push', event => {
    console.log('SW: Push received');
    
    if (event.data) {
        const data = event.data.json();
        
        const options = {
            body: data.body || 'Nova notificação do sistema',
            icon: '/icon-192.png',
            badge: '/badge-72.png',
            tag: data.tag || 'general',
            actions: [
                {
                    action: 'open',
                    title: 'Abrir Sistema'
                },
                {
                    action: 'dismiss',
                    title: 'Dispensar'
                }
            ],
            data: data
        };
        
        event.waitUntil(
            self.registration.showNotification(
                data.title || 'Sistema Carrinho de Praia',
                options
            )
        );
    }
});

// Notification click handler
self.addEventListener('notificationclick', event => {
    console.log('SW: Notification click');
    
    event.notification.close();
    
    if (event.action === 'open' || !event.action) {
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

// Background sync function
function doBackgroundSync() {
    console.log('SW: Performing background sync');
    
    // Get offline actions from IndexedDB and sync them
    return new Promise((resolve) => {
        // Implementation would go here for syncing offline actions
        // For now, just resolve
        setTimeout(resolve, 1000);
    });
}

// Message handler for communication with main thread
self.addEventListener('message', event => {
    console.log('SW: Message received:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'GET_CACHE_STATUS') {
        caches.has(CACHE_NAME)
            .then(hasCache => {
                event.ports[0].postMessage({
                    type: 'CACHE_STATUS',
                    hasCache,
                    cacheName: CACHE_NAME
                });
            });
    }
});

// Error handler
self.addEventListener('error', event => {
    console.error('SW: Error:', event.error);
});

// Unhandled rejection handler
self.addEventListener('unhandledrejection', event => {
    console.error('SW: Unhandled rejection:', event.reason);
    event.preventDefault();
});