/* ==========================================================================
   RUMBERO EXTREMO · PANEL ADMIN
   admin-shell.js  ·  Comportamiento del shell (sin dependencias)
   --------------------------------------------------------------------------
   Responsabilidades:
     1. Drawer del sidebar en móvil/tablet + colapso en escritorio.
     2. Bloqueo de scroll compatible con iOS (evita el "scroll bleed").
     3. Trampa de foco y accesibilidad (aria-expanded, ESC, focus return).
     4. Acordeón del submenú "Gestión de contenido".
     5. Desplegables de la toolbar (búsqueda, notificaciones, usuario).
     6. Envoltura automática de tablas en un contenedor con scroll táctil.
     7. Protección anti doble-envío en formularios POST.
   ========================================================================== */
(function () {
    'use strict';

    var MOBILE_QUERY = '(max-width: 1023.98px)';
    var doc = document;
    var root = doc.documentElement;
    var body = doc.body;

    /* ---------------------------------------------------------------------
       0. Utilidades
       ------------------------------------------------------------------ */
    function mq() {
        return window.matchMedia(MOBILE_QUERY).matches;
    }

    function $(sel, ctx) {
        return (ctx || doc).querySelector(sel);
    }

    function $$(sel, ctx) {
        return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel));
    }

    function on(el, evt, fn, opts) {
        if (el) {
            el.addEventListener(evt, fn, opts || false);
        }
    }

    function setExpanded(el, value) {
        if (el) {
            el.setAttribute('aria-expanded', value ? 'true' : 'false');
        }
    }

    /* ---------------------------------------------------------------------
       1. Altura real del viewport (100vh miente en iOS/Android)
       ------------------------------------------------------------------ */
    function syncViewportHeight() {
        var vh = (window.visualViewport ? window.visualViewport.height : window.innerHeight) * 0.01;
        root.style.setProperty('--rx-vh', vh + 'px');
    }

    /* ---------------------------------------------------------------------
       2. Bloqueo de scroll (iOS friendly)
       ------------------------------------------------------------------ */
    var scrollLock = {
        locked: false,
        y: 0,
        lock: function () {
            if (this.locked) return;
            this.y = window.pageYOffset || root.scrollTop;
            root.classList.add('rx-scroll-locked');
            body.style.top = '-' + this.y + 'px';
            this.locked = true;
        },
        unlock: function () {
            if (!this.locked) return;
            root.classList.remove('rx-scroll-locked');
            body.style.top = '';
            window.scrollTo(0, this.y);
            this.locked = false;
        }
    };

    /* ---------------------------------------------------------------------
       3. Sidebar (drawer + colapso de escritorio)
       ------------------------------------------------------------------ */
    var sidebar = $('#adminSidebar') || $('.admin-sidebar');
    var overlay = $('.sidebar-overlay');

    function isDrawerOpen() {
        return !!(sidebar && sidebar.classList.contains('open'));
    }

    function openDrawer() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        if (overlay) overlay.classList.add('active');
        scrollLock.lock();
        setExpanded($$('[data-rx-toggle="sidebar"]')[0], true);

        // Enfoca el primer enlace para lectores de pantalla
        var first = $('.sidebar-nav-link', sidebar);
        if (first) {
            window.setTimeout(function () {
                first.focus({ preventScroll: true });
            }, 120);
        }
    }

    function closeDrawer(returnFocus) {
        if (!sidebar) return;
        if (!isDrawerOpen()) return;
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
        scrollLock.unlock();
        setExpanded($$('[data-rx-toggle="sidebar"]')[0], false);

        if (returnFocus !== false) {
            var btn = $('[data-rx-toggle="sidebar"]');
            if (btn) btn.focus({ preventScroll: true });
        }
    }

    function toggleCollapsed() {
        var collapsed = body.classList.toggle('rx-sidebar-collapsed');
        try {
            localStorage.setItem('sidebarCollapsed', collapsed ? 'true' : 'false');
        } catch (e) { /* modo privado */ }
        var btn = $('[data-rx-collapse="sidebar"]') || $('[data-rx-toggle="sidebar"]');
        setExpanded(btn, collapsed);
    }

    function initSidebar() {
        // Restaurar preferencia de colapso SOLO en escritorio
        try {
            if (!mq() && localStorage.getItem('sidebarCollapsed') === 'true') {
                body.classList.add('rx-sidebar-collapsed');
            }
        } catch (e) { /* ignore */ }

        $$('[data-rx-toggle="sidebar"]').forEach(function (btn) {
            on(btn, 'click', function (evt) {
                evt.preventDefault();
                if (mq()) {
                    isDrawerOpen() ? closeDrawer() : openDrawer();
                } else {
                    toggleCollapsed();
                }
            });
        });

        $$('[data-rx-collapse="sidebar"]').forEach(function (btn) {
            on(btn, 'click', function (evt) {
                evt.preventDefault();
                mq() ? (isDrawerOpen() ? closeDrawer() : openDrawer()) : toggleCollapsed();
            });
        });

        on(overlay, 'click', function () {
            closeDrawer();
        });

        // Cerrar el drawer al navegar (enlaces reales)
        $$('.sidebar-nav-link, .sidebar-logout-link', sidebar).forEach(function (link) {
            on(link, 'click', function () {
                if (mq() && link.getAttribute('href') && link.getAttribute('href') !== '#') {
                    // Deja que el navegador navegue y libera el scroll antes
                    scrollLock.unlock();
                    sidebar.classList.remove('open');
                    if (overlay) overlay.classList.remove('active');
                }
            });
        });

        // Al pasar de móvil a escritorio (o al revés) se limpia el estado
        var mql = window.matchMedia(MOBILE_QUERY);
        var onChange = function () {
            if (!mql.matches) {
                closeDrawer(false);
                try {
                    if (localStorage.getItem('sidebarCollapsed') === 'true') {
                        body.classList.add('rx-sidebar-collapsed');
                    }
                } catch (e) { /* ignore */ }
            } else {
                body.classList.remove('rx-sidebar-collapsed');
            }
        };
        if (typeof mql.addEventListener === 'function') {
            mql.addEventListener('change', onChange);
        } else if (typeof mql.addListener === 'function') {
            mql.addListener(onChange);
        }

        // Trampa de foco dentro del drawer
        on(doc, 'keydown', function (evt) {
            if (evt.key !== 'Tab' || !mq() || !isDrawerOpen() || !sidebar) return;

            var focusables = $$(
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
                sidebar
            ).filter(function (el) {
                return el.offsetParent !== null;
            });

            if (!focusables.length) return;

            var first = focusables[0];
            var last = focusables[focusables.length - 1];

            if (!evt.shiftKey && doc.activeElement === last) {
                evt.preventDefault();
                first.focus();
            } else if (evt.shiftKey && doc.activeElement === first) {
                evt.preventDefault();
                last.focus();
            }
        });
    }

    /* ---------------------------------------------------------------------
       4. Acordeón del submenú
       ------------------------------------------------------------------ */
    function initSubmenus() {
        $$('.sidebar-parent-item').forEach(function (item) {
            var header = $('.sidebar-nav-header', item);
            if (!header) return;

            var hasActiveChild = !!$('.sidebar-nav-submenu .active', item);
            var stored = null;
            try {
                stored = localStorage.getItem('rxSubmenuContenido');
            } catch (e) { /* ignore */ }

            if (hasActiveChild || stored === 'open') {
                item.classList.add('is-open');
            } else if (stored === 'closed') {
                item.classList.remove('is-open');
            }

            header.setAttribute('role', 'button');
            header.setAttribute('tabindex', '0');
            setExpanded(header, item.classList.contains('is-open'));

            var toggle = function (evt) {
                if (evt) evt.preventDefault();
                var open = item.classList.toggle('is-open');
                setExpanded(header, open);
                try {
                    localStorage.setItem('rxSubmenuContenido', open ? 'open' : 'closed');
                } catch (e) { /* ignore */ }
            };

            on(header, 'click', toggle);
            on(header, 'keydown', function (evt) {
                if (evt.key === 'Enter' || evt.key === ' ' || evt.key === 'Spacebar') {
                    toggle(evt);
                }
            });
        });
    }

    /* ---------------------------------------------------------------------
       5. Desplegables de la toolbar
       ------------------------------------------------------------------ */
    var openDropdown = null;

    function closeDropdowns(except) {
        $$('[data-rx-dropdown]').forEach(function (panel) {
            if (panel === except) return;
            panel.classList.remove('show');
            var toggle = doc.querySelector('[data-rx-dropdown-toggle="' + panel.getAttribute('data-rx-dropdown') + '"]');
            setExpanded(toggle, false);
        });
        if (!except) openDropdown = null;
    }

    function initDropdowns() {
        $$('[data-rx-dropdown-toggle]').forEach(function (toggle) {
            var name = toggle.getAttribute('data-rx-dropdown-toggle');
            var panel = doc.querySelector('[data-rx-dropdown="' + name + '"]');
            if (!panel) return;

            setExpanded(toggle, false);

            on(toggle, 'click', function (evt) {
                evt.preventDefault();
                evt.stopPropagation();
                var willOpen = !panel.classList.contains('show');
                closeDropdowns(panel);
                panel.classList.toggle('show', willOpen);
                setExpanded(toggle, willOpen);
                openDropdown = willOpen ? panel : null;

                if (willOpen) {
                    var focusable = $('a, button, input', panel);
                    if (focusable && window.innerWidth >= 1024) {
                        window.setTimeout(function () {
                            focusable.focus({ preventScroll: true });
                        }, 80);
                    }
                }
            });

            // Evita que el click dentro cierre el panel
            on(panel, 'click', function (evt) {
                evt.stopPropagation();
            });
        });

        on(doc, 'click', function () {
            closeDropdowns();
        });

        /* Búsqueda en móvil: despliega el mismo bloque bajo la toolbar */
        $$('[data-rx-search-toggle]').forEach(function (btn) {
            var toolbar = $('.admin-toolbar');
            if (!toolbar) return;
            setExpanded(btn, false);

            on(btn, 'click', function (evt) {
                evt.preventDefault();
                evt.stopPropagation();
                var open = toolbar.classList.toggle('search-open');
                setExpanded(btn, open);
                if (open) {
                    var input = $('.search-input');
                    if (input) {
                        window.setTimeout(function () {
                            input.focus();
                        }, 90);
                    }
                }
            });
        });

        /* Búsqueda global: mostrar sugerencias + atajo ⌘K / Ctrl+K */
        var searchInput = $('.search-input');
        var searchPanel = $('.search-dropdown');

        if (searchInput && searchPanel) {
            on(searchInput, 'focus', function () {
                searchPanel.classList.add('show');
            });

            on(searchInput, 'keydown', function (evt) {
                if (evt.key === 'Escape') {
                    searchPanel.classList.remove('show');
                    searchInput.blur();
                }
            });

            on(searchPanel, 'click', function (evt) {
                evt.stopPropagation();
            });

            on(doc, 'click', function () {
                searchPanel.classList.remove('show');
            });
        }

        on(doc, 'keydown', function (evt) {
            // ESC: cierra en cascada (dropdown -> drawer -> búsqueda móvil)
            if (evt.key === 'Escape') {
                if (openDropdown) {
                    closeDropdowns();
                } else if (isDrawerOpen()) {
                    closeDrawer();
                } else {
                    var toolbarEl = $('.admin-toolbar');
                    if (toolbarEl && toolbarEl.classList.contains('search-open')) {
                        toolbarEl.classList.remove('search-open');
                        setExpanded($('[data-rx-search-toggle]'), false);
                    }
                }
            }

            // Ctrl/Cmd + K
            if ((evt.ctrlKey || evt.metaKey) && (evt.key === 'k' || evt.key === 'K')) {
                var input = $('.search-input');
                if (!input) return;
                evt.preventDefault();

                var toolbarEl = $('.admin-toolbar');
                var searchToggle = $('[data-rx-search-toggle]');
                if (mq() && toolbarEl && searchToggle) {
                    toolbarEl.classList.add('search-open');
                    setExpanded(searchToggle, true);
                }

                window.setTimeout(function () {
                    input.focus();
                    input.select();
                }, 100);
            }
        });
    }

    /* ---------------------------------------------------------------------
       5.5 NOTIFICACIONES REALES (bandeja del administrador)
       ------------------------------------------------------------------ */
    function initNotifications() {
        var root = $('[data-rx-notifications]');
        if (!root) return;

        var feedUrl = root.getAttribute('data-feed-url');
        var readUrl = root.getAttribute('data-read-url');
        var readAllUrl = root.getAttribute('data-read-all-url');
        var badge = $('[data-rx-notification-badge]', root);
        var list = $('[data-rx-notification-list]', root);
        var panel = $('[data-rx-dropdown="notifications"]', root);
        var readAllBtn = $('[data-rx-notification-read-all]', root);
        var csrfMeta = doc.querySelector('meta[name="csrf-token"]');
        var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

        function esc(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function setBadge(count) {
            if (!badge) return;
            count = Number(count) || 0;
            badge.textContent = count > 99 ? '99+' : String(count);
            if (count === 0) {
                badge.setAttribute('hidden', 'hidden');
            } else {
                badge.removeAttribute('hidden');
            }
        }

        function itemHtml(n) {
            var d = n.data || {};
            return '' +
                '<a href="' + esc(d.url || '#') + '" class="notification-item' + (n.read ? '' : ' unread') + '"' +
                ' data-rx-notification-id="' + esc(n.id) + '">' +
                '<span class="notification-icon ' + esc(d.color || 'info') + '">' +
                '<i class="' + esc(d.icon || 'fa-solid fa-bell') + '" aria-hidden="true"></i></span>' +
                '<div class="notification-content">' +
                '<p>' + esc(d.title || 'Actividad') + '</p>' +
                '<span class="notification-message">' + esc(d.message || '') + '</span>' +
                '<span class="notification-time"><i class="fa-regular fa-clock" aria-hidden="true"></i> ' +
                esc(n.created_human || '') + '</span>' +
                '</div></a>';
        }

        function render(items) {
            if (!list) return;

            if (!items || !items.length) {
                list.innerHTML = '<div class="notification-empty">' +
                    '<i class="fa-regular fa-bell-slash" aria-hidden="true"></i>' +
                    '<p>Sin actividad todavía</p>' +
                    '<small>Aquí verás cada usuario, aliado, promoción o banner que se cree, edite o elimine.</small>' +
                    '</div>';
                return;
            }

            list.innerHTML = items.map(itemHtml).join('');
        }

        function request(url, method) {
            return fetch(url, {
                method: method || 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                }
            }).then(function (response) {
                return response.ok ? response.json() : null;
            }).catch(function () {
                return null;
            });
        }

        function refresh() {
            if (!feedUrl) return;
            request(feedUrl).then(function (data) {
                if (!data) return;
                setBadge(data.unread);
                render(data.items);
            });
        }

        // Refresca al abrir la campana
        var toggle = $('[data-rx-dropdown-toggle="notifications"]', root);
        on(toggle, 'click', refresh);

        // Marca como leída al abrir una notificación (y deja navegar)
        on(list, 'click', function (evt) {
            var item = evt.target.closest('[data-rx-notification-id]');
            if (!item) return;

            var id = item.getAttribute('data-rx-notification-id');
            if (!id || !item.classList.contains('unread')) return;

            item.classList.remove('unread');
            request((readUrl || '').replace('__ID__', id), 'POST').then(function (data) {
                if (data) setBadge(data.unread);
            });
        });

        // Marcar todas como leídas
        on(readAllBtn, 'click', function (evt) {
            evt.preventDefault();
            request(readAllUrl, 'POST').then(function (data) {
                if (!data) return;
                setBadge(0);
                if (list) {
                    $$('.notification-item', list).forEach(function (el) {
                        el.classList.remove('unread');
                    });
                }
            });
        });

        // Actualización periódica (solo con la pestaña visible)
        window.setInterval(function () {
            if (doc.visibilityState === 'visible') refresh();
        }, 60000);

        // Si el panel venía marcado como abierto, sincroniza al cargar
        if (panel && panel.classList.contains('show')) refresh();
    }

    /* ---------------------------------------------------------------------
       5.7 MODO OSCURO POR USUARIO
       El tema se resuelve en el servidor (sin parpadeo). Aquí se aplica al
       instante cuando el usuario lo cambia, sin recargar y en todo el panel.
       ------------------------------------------------------------------ */
    function initTheme() {
        var schemeMeta = doc.querySelector('meta[name="color-scheme"]');
        var themeColorMeta = doc.querySelector('meta[name="theme-color"]');

        function apply(dark) {
            dark = !!dark;
            root.classList.toggle('dark-mode', dark);
            if (body) body.classList.toggle('dark-mode', dark);

            if (schemeMeta) schemeMeta.setAttribute('content', dark ? 'dark' : 'light');
            if (themeColorMeta) themeColorMeta.setAttribute('content', dark ? '#0f172a' : '#1A123C');

            $$('[data-rx-dark-toggle]').forEach(function (el) {
                el.checked = dark;
            });
        }

        // La página de Configuración dispara este evento tras guardar
        on(doc, 'dark-mode-changed', function (evt) {
            if (evt.detail && typeof evt.detail.enabled !== 'undefined') {
                apply(evt.detail.enabled);
            }
        });

        // Interruptor rápido de la toolbar
        $$('[data-rx-dark-toggle]').forEach(function (toggle) {
            on(toggle, 'change', function () {
                var enabled = toggle.checked;
                var url = toggle.getAttribute('data-url');

                apply(enabled); // feedback inmediato

                if (!url) return;

                fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': (doc.querySelector('meta[name="csrf-token"]') || {}).content || ''
                    },
                    body: JSON.stringify({ dark_mode_enabled: enabled })
                }).then(function (response) {
                    if (!response.ok) throw new Error('theme');
                    return response.json();
                }).then(function (data) {
                    if (data && typeof data.dark_mode_enabled !== 'undefined') {
                        apply(data.dark_mode_enabled);
                    }
                }).catch(function () {
                    // Si falla, se revierte el interruptor
                    apply(!enabled);
                });
            });
        });

        // Expuesto para la vista de Configuración
        window.RumberoTheme = { apply: apply };
    }

    /* ---------------------------------------------------------------------
       5.8 WEB PUSH (suscripción del navegador)
       Solo se activa si la página incluye el bloque [data-rx-push]
       (Configuración). Requiere service worker + permisos del usuario.
       ------------------------------------------------------------------ */
    function initPush() {
        var config = $('[data-rx-push]');
        if (!config) return;

        var toggle = $('[data-rx-push-toggle]', config);
        var statusEl = $('[data-rx-push-status]', config);
        var testBtn = $('[data-rx-push-test]', config);
        var devicesEl = $('[data-rx-push-devices]', config);

        var publicKey = config.getAttribute('data-public-key') || '';
        var swUrl = config.getAttribute('data-sw-url') || '/sw.js';
        var subscribeUrl = config.getAttribute('data-subscribe-url');
        var unsubscribeUrl = config.getAttribute('data-unsubscribe-url');
        var testUrl = config.getAttribute('data-test-url');
        var csrf = (doc.querySelector('meta[name="csrf-token"]') || {}).content || '';

        var supported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;

        if (!supported) {
            config.classList.add('is-unsupported');
            if (toggle) {
                toggle.disabled = true;
                toggle.checked = false;
            }
            setStatus('unsupported', 'Tu navegador no soporta notificaciones push. En iPhone necesitas iOS 16.4 o superior y añadir el panel a la pantalla de inicio.');
            if (testBtn) testBtn.disabled = true;
            return;
        }

        function setStatus(state, message) {
            config.setAttribute('data-state', state);
            if (statusEl) statusEl.textContent = message;
            if (testBtn) testBtn.disabled = state !== 'active';
        }

        function post(url, payload) {
            return fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify(payload || {})
            }).then(function (response) {
                return response.json().catch(function () {
                    return null;
                });
            }).catch(function () {
                return null;
            });
        }

        function base64ToUint8(base64) {
            var padding = '='.repeat((4 - (base64.length % 4)) % 4);
            var normalized = (base64 + padding).replace(/-/g, '+').replace(/_/g, '/');
            var raw = window.atob(normalized);
            var output = new Uint8Array(raw.length);

            for (var i = 0; i < raw.length; i++) {
                output[i] = raw.charCodeAt(i);
            }

            return output;
        }

        function registerWorker() {
            return navigator.serviceWorker.register(swUrl, { scope: '/' });
        }

        function getSubscription() {
            return navigator.serviceWorker.getRegistration('/').then(function (reg) {
                return reg ? reg.pushManager.getSubscription() : null;
            });
        }

        function enable() {
            if (Notification.permission === 'denied') {
                if (toggle) toggle.checked = false;
                setStatus('denied', 'Bloqueaste las notificaciones en este navegador. Habilítalas desde el candado de la barra de direcciones.');

                return Promise.resolve();
            }

            var request = Notification.permission === 'granted'
                ? Promise.resolve('granted')
                : Notification.requestPermission();

            return request.then(function (permission) {
                if (permission !== 'granted') {
                    if (toggle) toggle.checked = false;
                    setStatus('denied', 'Permiso denegado. Sin permiso no se pueden mostrar notificaciones.');

                    return null;
                }

                return registerWorker().then(function (reg) {
                    return navigator.serviceWorker.ready.then(function () {
                        return reg.pushManager.getSubscription().then(function (existing) {
                            if (existing) return existing;

                            return reg.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: base64ToUint8(publicKey)
                            });
                        });
                    });
                }).then(function (subscription) {
                    return post(subscribeUrl, subscription.toJSON());
                }).then(function (response) {
                    if (response && response.success) {
                        setStatus('active', 'Activadas en este dispositivo' + (response.devices > 1 ? ' (' + response.devices + ' dispositivos)' : ''));
                        if (devicesEl) devicesEl.textContent = response.devices || 1;
                    } else {
                        if (toggle) toggle.checked = false;
                        setStatus('error', (response && response.message) || 'No se pudo completar la suscripción.');
                    }
                });
            }).catch(function () {
                if (toggle) toggle.checked = false;
                setStatus('error', 'El navegador rechazó la suscripción push.');
            });
        }

        function disable() {
            return getSubscription().then(function (subscription) {
                var endpoint = subscription ? subscription.endpoint : null;
                var unsubscribed = subscription ? subscription.unsubscribe() : Promise.resolve(true);

                return unsubscribed.then(function () {
                    return post(unsubscribeUrl, { endpoint: endpoint });
                }).then(function (response) {
                    setStatus('off', 'Desactivadas en este dispositivo');
                    if (devicesEl) devicesEl.textContent = (response && response.devices) || 0;
                });
            }).catch(function () {
                setStatus('off', 'Desactivadas');
            });
        }

        on(toggle, 'change', function () {
            if (toggle.checked) {
                enable();
            } else {
                disable();
            }
        });

        on(testBtn, 'click', function () {
            if (testBtn) testBtn.disabled = true;

            post(testUrl, {}).then(function (response) {
                setStatus(
                    response && response.success ? 'active' : 'error',
                    (response && response.message) || 'No se pudo enviar la prueba.'
                );
                if (testBtn) testBtn.disabled = false;
            });
        });

        // Estado inicial real (permiso + suscripción existente)
        if (Notification.permission === 'denied') {
            if (toggle) toggle.checked = false;
            setStatus('denied', 'Notificaciones bloqueadas por el navegador.');
        } else {
            getSubscription().then(function (subscription) {
                if (subscription) {
                    if (toggle) toggle.checked = true;
                    setStatus('active', 'Activadas en este dispositivo');
                } else {
                    if (toggle) toggle.checked = false;
                    setStatus('off', 'Desactivadas en este dispositivo');
                }
            }).catch(function () {
                setStatus('off', 'Desactivadas en este dispositivo');
            });
        }
    }

    /* ---------------------------------------------------------------------
       6. Tablas: contenedor con scroll táctil automático
       ------------------------------------------------------------------ */
    function wrapTables() {
        var content = $('.page-content');
        if (!content) return;

        $$('table', content).forEach(function (table) {
            if (table.closest('.rx-table-scroll, .table-responsive, .table-scroll, .table-wrapper')) {
                return;
            }

            var wrapper = doc.createElement('div');
            wrapper.className = 'rx-table-scroll rx-scroll-hint';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);

            // Etiquetas para tablas apiladas (opt-in con .rx-stack-table)
            if (table.classList.contains('rx-stack-table')) {
                var headers = $$('thead th', table).map(function (th) {
                    return th.textContent.trim();
                });
                $$('tbody tr', table).forEach(function (tr) {
                    $$('td', tr).forEach(function (td, i) {
                        if (headers[i]) td.setAttribute('data-label', headers[i]);
                    });
                });
            }
        });
    }

    /* ---------------------------------------------------------------------
       7. Anti doble-envío en formularios POST
       ------------------------------------------------------------------ */
    function initFormGuards() {
        $$('form').forEach(function (form) {
            var method = (form.getAttribute('method') || 'GET').toUpperCase();
            if (method !== 'POST') return;
            if (form.hasAttribute('data-no-guard')) return;

            on(form, 'submit', function (evt) {
                if (form.dataset.rxSubmitting === '1') {
                    evt.preventDefault();
                    return;
                }

                window.setTimeout(function () {
                    // Si otro handler canceló el envío, no bloqueamos el botón
                    if (evt.defaultPrevented) return;

                    form.dataset.rxSubmitting = '1';
                    var submitter = evt.submitter || $('[type="submit"]', form);
                    if (submitter) {
                        submitter.classList.add('is-loading');
                        submitter.setAttribute('aria-busy', 'true');
                    }
                }, 0);
            });
        });

        // Al volver con el botón "atrás", se restaura el estado
        on(window, 'pageshow', function () {
            $$('form').forEach(function (form) {
                delete form.dataset.rxSubmitting;
            });
            $$('.is-loading').forEach(function (el) {
                el.classList.remove('is-loading');
                el.removeAttribute('aria-busy');
            });
        });
    }

    /* ---------------------------------------------------------------------
       8. Sombra de la toolbar al hacer scroll
       ------------------------------------------------------------------ */
    function initToolbarShadow() {
        var toolbar = $('.admin-toolbar');
        if (!toolbar) return;

        var ticking = false;
        var update = function () {
            toolbar.classList.toggle('is-scrolled', window.pageYOffset > 6);
            ticking = false;
        };

        on(window, 'scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(update);
                ticking = true;
            }
        }, { passive: true });

        update();
    }

    /* ---------------------------------------------------------------------
       9. Arranque
       ------------------------------------------------------------------ */
    function boot() {
        syncViewportHeight();
        root.classList.add('rx-js-ready');

        initSidebar();
        initSubmenus();
        initDropdowns();
        initNotifications();
        initTheme();
        initPush();
        wrapTables();
        initFormGuards();
        initToolbarShadow();

        on(window, 'resize', syncViewportHeight, { passive: true });
        on(window, 'orientationchange', function () {
            window.setTimeout(syncViewportHeight, 120);
        });
        if (window.visualViewport) {
            on(window.visualViewport, 'resize', syncViewportHeight, { passive: true });
        }
    }

    if (doc.readyState === 'loading') {
        doc.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
