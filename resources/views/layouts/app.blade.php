<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Título de la página --}}
    <title>@yield('title', config('app.name', 'Rumbero Extremo'))</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/affiliate-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    @stack('styles')
</head>

<body class="antialiased">
    <div id="app">
        {{-- Importar la Navbar --}}
        @include('layouts.nav.navbar')

        {{-- Contenido principal de la página --}}
        <main class="py-4">
            @yield('content')
        </main>

        {{-- Importar el Footer --}}
        @include('layouts.footer')
    </div>

    {{-- BURBUJA DE CHAT FLOTANTE (FAB que se expande al pasar el cursor) --}}
    <div id="chat-bubble" class="rumbero-chat-bubble" role="button" tabindex="0"
        aria-label="Abrir chat de soporte" aria-haspopup="dialog"
        onclick="toggleChatModal()"
        onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); toggleChatModal(); }">
        <span class="bubble-icon" aria-hidden="true">
            <i class="fas fa-comment-dots"></i>
        </span>
        <span class="bubble-text" aria-hidden="true">
            <span class="bubble-title">Soporte</span>
            <span class="bubble-subtitle">Habla con nosotros</span>
        </span>
        <span class="bubble-notification" id="chat-notification" style="display: none;">1</span>
    </div>

    {{-- MODAL DEL CHAT --}}
    <div id="chat-modal" class="rumbero-chat-modal">
        {{-- Header del modal --}}
        <div class="modal-header">
            <div class="header-left">
                <div class="header-avatar">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="header-info">
                    <h3 class="header-title">Soporte Rumbero</h3>
                    <div class="header-status">
                        <span class="status-dot"></span>
                        <span class="status-text" id="chat-status">Conectando...</span>
                    </div>
                </div>
            </div>

            <button onclick="toggleChatModal()" class="close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Cuerpo del modal con mensajes --}}
        <div class="modal-body" id="chat-modal-messages">
            <div class="message welcome-message">
                <div class="message-avatar support-avatar">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <span class="message-sender">Soporte Rumbero</span>
                    </div>
                    <div class="message-bubble welcome">
                        <div class="welcome-text">
                            <span class="welcome-emoji">🎉🔥</span>
                            <p>¡Qué hubo parce! Soy del equipo de soporte. ¿En qué puedo ayudarte hoy?</p>
                        </div>
                        <div class="welcome-features">
                            <span class="feature"><i class="fas fa-tag"></i> Promociones</span>
                            <span class="feature"><i class="fas fa-percent"></i> Descuentos</span>
                            <span class="feature"><i class="fas fa-map-pin"></i> Lugares</span>
                            <span class="feature"><i class="fas fa-music"></i> Eventos</span>
                        </div>
                    </div>
                    <span class="message-time">ahora</span>
                </div>
            </div>
        </div>

        {{-- Footer del modal --}}
        <div class="modal-footer">
            <div class="quick-options">
                <button class="quick-option" onclick="sendQuickMessage('¿Tienen promociones en farmacias?')">
                    <i class="fas fa-pills"></i>
                    <span>Farmacias</span>
                </button>
                <button class="quick-option" onclick="sendQuickMessage('¿Qué restaurantes tienen ofertas?')">
                    <i class="fas fa-utensils"></i>
                    <span>Restaurantes</span>
                </button>
                <button class="quick-option" onclick="sendQuickMessage('¿Dónde hay rumba esta noche?')">
                    <i class="fas fa-music"></i>
                    <span>Discotecas</span>
                </button>
                <button class="quick-option" onclick="sendQuickMessage('¿Cómo activo un descuento?')">
                    <i class="fas fa-question-circle"></i>
                    <span>Ayuda</span>
                </button>
            </div>

            <div class="input-container">
                <input type="text" id="chat-modal-input" 
                       placeholder="Escribe tu mensaje..." 
                       class="chat-input"
                       onkeypress="if(event.key==='Enter') sendModalMessage()">
                <button onclick="sendModalMessage()" class="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>

            {{-- Elección del usuario: seguir en el chat o continuar por WhatsApp --}}
            <div class="chat-channel-choice" id="chat-channel-choice">
                <button type="button" class="channel-btn channel-btn-chat is-active" onclick="continuarEnChat()">
                    <i class="fas fa-comments"></i> Seguir en el chat
                </button>

                <a href="{{ app(\App\Services\ChatSupportService::class)->whatsappUrl() ?? 'https://wa.me/' }}"
                   class="channel-btn channel-btn-whatsapp" id="chat-whatsapp-btn" target="_blank" rel="noopener">
                    <i class="fab fa-whatsapp"></i> Continuar por WhatsApp
                </a>
            </div>

            <div class="footer-note" id="chat-footer-note">
                <i class="fas fa-robot"></i>
                <span id="chat-footer-text">Respuesta automática con IA</span>
            </div>
        </div>
    </div>

    {{-- OVERLAY PARA MÓVILES --}}
    <div id="chat-overlay" class="chat-overlay" onclick="toggleChatModal()"></div>

    {{-- Scripts --}}
    @stack('scripts')

    {{-- JavaScript del Chat --}}
    <script>
        // Variables globales
        let isModalOpen = false;
        let sessionId = localStorage.getItem('chat_session_id') || generateSessionId();
        let lastMessageId = null;
        let pollingInterval = null;
        let renderedIds = new Set();
        let currentChannel = 'ai';
        
        // Guardar session ID
        localStorage.setItem('chat_session_id', sessionId);
        
        function generateSessionId() {
            return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }
        
        // Función para abrir/cerrar el modal
        function toggleChatModal() {
            const modal = document.getElementById('chat-modal');
            const overlay = document.getElementById('chat-overlay');
            const bubble = document.getElementById('chat-bubble');
            
            if (modal) {
                modal.classList.toggle('show');
                if (overlay) overlay.classList.toggle('show');
                if (bubble) bubble.classList.toggle('active');
                
                isModalOpen = modal.classList.contains('show');
                
                if (isModalOpen) {
                    setTimeout(() => {
                        document.getElementById('chat-modal-input')?.focus();
                    }, 300);
                    
                    // Marcar mensajes como leídos
                    markMessagesAsRead();
                    
                    // Iniciar polling para recibir respuestas
                    startPolling();
                } else {
                    // Detener polling cuando se cierra
                    if (pollingInterval) {
                        clearInterval(pollingInterval);
                        pollingInterval = null;
                    }
                }
            }
        }
        
        // Marcar mensajes como leídos
        function markMessagesAsRead() {
            // Aquí podrías enviar una petición para marcar como leídos
            const notification = document.getElementById('chat-notification');
            if (notification) notification.style.display = 'none';
        }
        
        // Iniciar polling para recibir respuestas del admin
        function startPolling() {
            if (pollingInterval) clearInterval(pollingInterval);
            
            // Polling cada 5 segundos
            pollingInterval = setInterval(() => {
                checkForNewMessages();
            }, 5000);
        }
        
        // Verificar mensajes nuevos (respuestas del asesor o de la IA)
        async function checkForNewMessages() {
            try {
                const response = await fetch('{{ url("/api/rumbero-ai/conversacion") }}?session_id=' + encodeURIComponent(sessionId), {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    data.data.forEach(msg => {
                        if (msg.sender === 'user') return;
                        if (renderedIds.has(String(msg.id))) return;

                        renderedIds.add(String(msg.id));
                        addModalMessage(msg.message, msg.is_admin ? 'assistant' : 'ai');

                        // Si el modal está cerrado, avisar en la burbuja
                        if (!isModalOpen) {
                            const notification = document.getElementById('chat-notification');
                            if (notification) notification.style.display = 'flex';
                        }
                    });

                    if (data.meta) updateChatChannel(data.meta.mode);
                }
            } catch (error) {
                console.error('Error polling:', error);
            }
        }
        
        // Función para enviar mensaje desde el modal
        async function sendModalMessage() {
            const input = document.getElementById('chat-modal-input');
            const mensaje = input.value.trim();
            
            if (!mensaje) return;
            
            // Agregar mensaje del usuario
            addModalMessage(mensaje, 'user');
            input.value = '';
            
            // Actualizar estado
            updateChatStatus('enviando...');
            
            try {
                const response = await fetch('{{ url("/api/rumbero-ai/chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Chat-Session': sessionId
                    },
                    body: JSON.stringify({
                        mensaje: mensaje,
                        session_id: sessionId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    updateChatStatus('conectado');

                    const payload = data.data || {};
                    lastMessageId = payload.message_id;

                    if (payload.message_id) renderedIds.add(String(payload.message_id));

                    // IA o asesor: se muestra con su etiqueta correspondiente
                    addModalMessage(
                        payload.respuesta || '¡Mensaje recibido! Un asesor te responderá en breve.',
                        payload.mode === 'ai' ? 'ai' : 'assistant'
                    );

                    // Enlace de WhatsApp con la consulta precargada
                    if (payload.whatsapp_url) {
                        const wa = document.getElementById('chat-whatsapp-btn');
                        if (wa) wa.href = payload.whatsapp_url;
                    }

                    updateChatChannel(payload.mode || 'human');
                } else {
                    addModalMessage('Lo siento, hubo un error. Intenta de nuevo.', 'assistant');
                    updateChatStatus('error');
                }
            } catch (error) {
                console.error('Error:', error);
                addModalMessage('Error de conexión. ¿Tienes internet?', 'assistant');
                updateChatStatus('offline');
            }
        }

        // Refleja si responde la IA o un asesor, y ajusta el pie del chat
        function updateChatChannel(mode) {
            currentChannel = mode === 'ai' ? 'ai' : 'human';

            const note = document.getElementById('chat-footer-text');
            const choice = document.getElementById('chat-channel-choice');

            if (note) {
                note.textContent = currentChannel === 'ai'
                    ? 'Respuesta automática con IA · puedes pedir un asesor cuando quieras'
                    : 'Un asesor del equipo te responderá por aquí en breve';
            }

            if (choice) {
                choice.classList.toggle('is-handoff', currentChannel === 'human');
            }
        }

        // El usuario decide seguir en el chat
        function continuarEnChat() {
            const input = document.getElementById('chat-modal-input');
            if (input) input.focus();

            const note = document.getElementById('chat-footer-text');
            if (note && currentChannel === 'human') {
                note.textContent = 'Seguimos por aquí: un asesor te responderá en este chat';
            }
        }
        
        // Función para enviar mensajes rápidos
        function sendQuickMessage(message) {
            document.getElementById('chat-modal-input').value = message;
            sendModalMessage();
        }
        
        // Función para agregar mensajes al modal
        function addModalMessage(text, type) {
            const container = document.getElementById('chat-modal-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type === 'user' ? 'user-message-wrapper' : ''}`;
            
            const time = new Date().toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            if (type === 'user') {
                messageDiv.innerHTML = `
                    <div class="user-message-container">
                        <div class="user-message">
                            <p>${escapeHtml(text)}</p>
                        </div>
                        <span class="message-time">${time}</span>
                    </div>
                `;
            } else {
                const isAi = type === 'ai';

                messageDiv.innerHTML = `
                    <div class="message-avatar ${isAi ? 'ai-avatar' : 'support-avatar'}">
                        <i class="fas ${isAi ? 'fa-robot' : 'fa-headset'}"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-sender">${isAi ? 'Rumbero AI' : 'Soporte'}</span>
                            ${isAi ? '<span class="sender-badge">IA</span>' : ''}
                        </div>
                        <div class="message-bubble">
                            <p>${formatMessage(text)}</p>
                        </div>
                        <span class="message-time">${time}</span>
                    </div>
                `;
            }
            
            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
        }
        
        // Actualizar estado del chat
        function updateChatStatus(status) {
            const statusElement = document.getElementById('chat-status');
            if (!statusElement) return;
            
            const statusMap = {
                'conectado': 'En línea · respuesta rápida',
                'enviando...': '📤 Enviando...',
                'error': '🔴 Error • Intenta de nuevo',
                'offline': '⚠️ Sin conexión'
            };
            
            statusElement.textContent = statusMap[status] || status;
            
            // Cambiar color del dot
            const dot = document.querySelector('.status-dot');
            if (dot) {
                const dotColors = {
                    'conectado': '#2ecc71',
                    'enviando...': '#f39c12',
                    'error': '#e74c3c',
                    'offline': '#95a5a6'
                };
                dot.style.backgroundColor = dotColors[status] || '#95a5a6';
            }
        }
        
        // Función para escapar HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Formato mínimo para las respuestas de la IA: escapa el HTML y
        // convierte **negrita** y saltos de línea (Markdown no se interpreta).
        function formatMessage(text) {
            let html = escapeHtml(String(text ?? ''));
            html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/\n/g, '<br>');
            return html;
        }
        
        // Cargar historial de conversación
        async function loadConversationHistory() {
            try {
                const response = await fetch('{{ url("/api/rumbero-ai/conversacion") }}?session_id=' + sessionId, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success && data.data && data.data.length > 0) {
                    // Limpiar mensajes excepto el de bienvenida
                    const container = document.getElementById('chat-modal-messages');
                    const welcomeMsg = container.querySelector('.welcome-message');
                    container.innerHTML = '';
                    if (welcomeMsg) container.appendChild(welcomeMsg);
                    
                    // Agregar historial
                    data.data.forEach(msg => {
                        if (msg.sender === 'user') {
                            addModalMessage(msg.message, 'user');
                        } else if (msg.sender === 'admin') {
                            addModalMessage(msg.message, 'assistant');
                        }
                        lastMessageId = msg.id;
                    });
                }
            } catch (error) {
                console.error('Error cargando historial:', error);
            }
        }
        
        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isModalOpen) {
                toggleChatModal();
            }
        });
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            console.log('💬 Chat de soporte listo');
            updateChatStatus('conectado');
            
            // Cargar historial si existe
            loadConversationHistory();
            
            // Ocultar notificación después de 5 segundos
            setTimeout(() => {
                const notif = document.querySelector('.bubble-notification');
                if (notif) notif.style.display = 'none';
            }, 5000);
        });
    </script>
</body>
</html>
