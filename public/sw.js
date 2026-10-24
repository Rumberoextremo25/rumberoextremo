/* ==========================================================================
   RUMBERO EXTREMO · Service Worker de Web Push
   --------------------------------------------------------------------------
   Solo gestiona eventos `push` y el clic en la notificación: NO intercepta
   `fetch`, así que no afecta al rendimiento ni a la navegación normal del
   sitio público.
   ========================================================================== */

self.addEventListener('install', function () {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', function (event) {
    var data = {};

    try {
        data = event.data ? event.data.json() : {};
    } catch (error) {
        data = { title: 'Rumbero Extremo', body: event.data ? event.data.text() : '' };
    }

    var title = data.title || 'Rumbero Extremo';
    var options = {
        body: data.body || '',
        icon: data.icon || '/assets/img/logo_sidebar.png',
        badge: data.badge || '/assets/img/logo_sidebar.png',
        tag: data.tag || 'rumbero-panel',
        renotify: true,
        data: {
            url: data.url || '/admin',
            timestamp: data.timestamp || Date.now()
        }
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    var url = (event.notification.data && event.notification.data.url) || '/admin';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            for (var i = 0; i < clientList.length; i++) {
                var client = clientList[i];

                if (client.url.indexOf(url) !== -1 && 'focus' in client) {
                    return client.focus();
                }
            }

            if (self.clients.openWindow) {
                return self.clients.openWindow(url);
            }
        })
    );
});
