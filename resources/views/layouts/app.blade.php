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

    {{-- BURBUJA DE CHAT FLOTANTE --}}
    <div id="chat-bubble" class="rumbero-chat-bubble" onclick="toggleChatModal()">
        <div class="bubble-content">
            <div class="bubble-icon">
                <i class="fas fa-fire"></i>
            </div>
            <div class="bubble-text">
                <span class="bubble-title">RumberoAI</span>
                <span class="bubble-subtitle">Pregunta aquí</span>
            </div>
        </div>
        <div class="bubble-pulse"></div>
        <div class="bubble-notification">1</div>
    </div>

    {{-- MODAL DEL CHAT RUMBERO --}}
    <div id="chat-modal" class="rumbero-chat-modal">
        {{-- Header del modal --}}
        <div class="modal-header">
            <div class="header-left">
                <div class="header-avatar">
                    <i class="fas fa-fire"></i>
                </div>
                <div class="header-info">
                    <h3 class="header-title">RumberoAI</h3>
                    <div class="header-status">
                        <span class="status-dot"></span>
                        <span class="status-text">Online • 24/7</span>
                    </div>
                </div>
            </div>

            {{-- Selector de IA --}}
            <div class="ia-selector">
                <button class="ia-option active" onclick="setActiveIA('gemini')" id="ia-gemini">
                    <i class="fas fa-brain"></i>
                    <span class="ia-label">GM</span>
                    <span class="ia-tooltip">Gemini</span>
                </button>
                <button class="ia-option" onclick="setActiveIA('deepseek')" id="ia-deepseek">
                    <i class="fas fa-robot"></i>
                    <span class="ia-label">DS</span>
                    <span class="ia-tooltip">DeepSeek</span>
                </button>
            </div>

            <button onclick="toggleChatModal()" class="close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Cuerpo del modal con mensajes --}}
        <div class="modal-body" id="chat-modal-messages">
            <div class="message welcome-message">
                <div class="message-avatar gemini-avatar">
                    <i class="fas fa-brain"></i>
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <span class="message-sender">RumberoAI</span>
                        <span class="message-ia-badge gemini">
                            <i class="fas fa-brain"></i> Gemini
                        </span>
                    </div>
                    <div class="message-bubble welcome">
                        <div class="welcome-text">
                            <span class="welcome-emoji">🎉🔥</span>
                            <p>¡Qué hubo parce! Soy RumberoAI, tu asistente para encontrar las mejores ofertas y descuentos.</p>
                        </div>
                        <div class="welcome-features">
                            <span class="feature"><i class="fas fa-tag"></i> Ofertas</span>
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
                <button class="quick-option" onclick="sendQuickMessage('💊 Farmacias con descuento')">
                    <i class="fas fa-pills"></i>
                    <span>Farmacias</span>
                </button>
                <button class="quick-option" onclick="sendQuickMessage('🍔 Restaurantes con ofertas')">
                    <i class="fas fa-utensils"></i>
                    <span>Restaurantes</span>
                </button>
                <button class="quick-option" onclick="sendQuickMessage('🎉 Discotecas y rumbas')">
                    <i class="fas fa-music"></i>
                    <span>Discotecas</span>
                </button>
                <button class="quick-option" onclick="sendQuickMessage('🏨 Posadas y hospedajes')">
                    <i class="fas fa-bed"></i>
                    <span>Posadas</span>
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

            <div class="footer-note">
                <i class="fas fa-shield-alt"></i>
                <span>Tu información es segura</span>
            </div>
        </div>
    </div>

    {{-- OVERLAY PARA MÓVILES --}}
    <div id="chat-overlay" class="chat-overlay" onclick="toggleChatModal()"></div>

    {{-- Scripts --}}
    @stack('scripts')

    {{-- JavaScript del Chat Rumbero --}}
    <script>
        // Variables globales
        let isModalOpen = false;
        let currentIA = 'gemini';
        let conversationHistory = [];

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
                    
                    // Ocultar notificación
                    document.querySelector('.bubble-notification')?.remove();
                }
            }
        }

        // Función para cambiar de IA
        function setActiveIA(ia) {
            currentIA = ia;

            // Actualizar clases de los botones
            const geminiBtn = document.getElementById('ia-gemini');
            const deepseekBtn = document.getElementById('ia-deepseek');

            if (ia === 'gemini') {
                geminiBtn.classList.add('active');
                deepseekBtn.classList.remove('active');
                addSystemMessage('¡Cambiaste a Gemini! 🧠');
            } else {
                deepseekBtn.classList.add('active');
                geminiBtn.classList.remove('active');
                addSystemMessage('¡Cambiaste a DeepSeek! 🤖');
            }
        }

        // Función para enviar mensajes rápidos
        function sendQuickMessage(message) {
            document.getElementById('chat-modal-input').value = message;
            sendModalMessage();
        }

        // Función para enviar mensaje desde el modal
        async function sendModalMessage() {
            const input = document.getElementById('chat-modal-input');
            const mensaje = input.value.trim();

            if (!mensaje) return;

            // Agregar mensaje del usuario
            addModalMessage(mensaje, 'user');
            input.value = '';
            
            // Mostrar typing
            showModalTyping();

            // Guardar en historial
            conversationHistory.push({ role: 'user', content: mensaje });

            try {
                const response = await fetch('{{ url('/api/ia/chat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        mensaje: mensaje,
                        ia_preferida: currentIA,
                        historial: conversationHistory.slice(-5)
                    })
                });

                const data = await response.json();
                hideModalTyping();

                if (data.success) {
                    const respuesta = data.data?.respuesta || data.mensaje || 'Aquí tienes la información:';
                    const iaUsada = data.ia_utilizada || currentIA;
                    
                    addModalMessage(respuesta, 'assistant', iaUsada);
                    conversationHistory.push({ role: 'assistant', content: respuesta });

                    // Si la IA usada es diferente a la seleccionada, actualizar selector
                    if (iaUsada.includes('gemini') && currentIA !== 'gemini') {
                        setActiveIA('gemini');
                    } else if (iaUsada.includes('deepseek') && currentIA !== 'deepseek') {
                        setActiveIA('deepseek');
                    }
                } else {
                    addModalMessage('¡Ay parce! Algo salió mal. Intenta de nuevo.', 'assistant');
                }
            } catch (error) {
                hideModalTyping();
                addModalMessage('Error de conexión. ¿Tienes internet?', 'assistant');
                console.error('Error:', error);
            }
        }

        // Función para agregar mensajes al modal
        function addModalMessage(text, type, iaType = null) {
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
                const iaClass = iaType && iaType.includes('gemini') ? 'gemini' : 'deepseek';
                const iaName = iaType && iaType.includes('gemini') ? 'Gemini' : 'DeepSeek';
                const iaIcon = iaType && iaType.includes('gemini') ? 'fa-brain' : 'fa-robot';

                messageDiv.innerHTML = `
                    <div class="message-avatar ${iaClass}">
                        <i class="fas ${iaIcon}"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-sender">RumberoAI</span>
                            <span class="message-ia-badge ${iaClass}">
                                <i class="fas ${iaIcon}"></i> ${iaName}
                            </span>
                        </div>
                        <div class="message-bubble">
                            <p>${escapeHtml(text)}</p>
                        </div>
                        <span class="message-time">${time}</span>
                    </div>
                `;
            }

            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
        }

        // Función para agregar mensajes del sistema
        function addSystemMessage(text) {
            const container = document.getElementById('chat-modal-messages');
            const systemDiv = document.createElement('div');
            systemDiv.className = 'system-message';
            systemDiv.innerHTML = `
                <div class="system-content">
                    <i class="fas fa-sync-alt"></i>
                    <span>${text}</span>
                </div>
            `;
            container.appendChild(systemDiv);
            container.scrollTop = container.scrollHeight;
        }

        // Función para mostrar indicador de typing
        function showModalTyping() {
            const container = document.getElementById('chat-modal-messages');
            const typingDiv = document.createElement('div');
            typingDiv.id = 'modal-typing';
            typingDiv.className = 'message typing-container';

            const iaClass = currentIA === 'gemini' ? 'gemini' : 'deepseek';
            const iaIcon = currentIA === 'gemini' ? 'fa-brain' : 'fa-robot';

            typingDiv.innerHTML = `
                <div class="message-avatar ${iaClass}">
                    <i class="fas ${iaIcon}"></i>
                </div>
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <span class="typing-text">${currentIA === 'gemini' ? 'Gemini' : 'DeepSeek'} está pensando...</span>
                </div>
            `;
            container.appendChild(typingDiv);
            container.scrollTop = container.scrollHeight;
        }

        // Función para ocultar indicador de typing
        function hideModalTyping() {
            const typing = document.getElementById('modal-typing');
            if (typing) typing.remove();
        }

        // Función para escapar HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isModalOpen) {
                toggleChatModal();
            }
        });

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎉 RumberoAI listo para la rumba');

            // Ocultar notificación después de 5 segundos
            setTimeout(() => {
                const notif = document.querySelector('.bubble-notification');
                if (notif) notif.style.display = 'none';
            }, 5000);
        });
    </script>
</body>
</html>
