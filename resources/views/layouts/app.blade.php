<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Título de la página --}}
    <title>@yield('title', config('app.name', 'Laravel App'))</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
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
    <div id="chat-bubble" class="chat-bubble" onclick="toggleChatModal()">
        <div class="chat-bubble-icon">
            <i class="fas fa-robot"></i>
        </div>
        <div class="chat-bubble-text">
            <span class="font-bold">RumberoAI</span>
            <span class="text-xs">Pregunta aquí</span>
        </div>
        <div class="chat-bubble-notification">1</div>
    </div>

    {{-- MODAL DEL CHAT CON SELECTOR DE IA --}}
    <div id="chat-modal" class="chat-modal">
        <div class="chat-modal-header">
            <div class="flex items-center gap-3">
                <div class="chat-modal-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h3 class="font-bold text-white">RumberoAI</h3>
                    <p class="text-xs text-yellow-200">Online • Responde al instante</p>
                </div>
            </div>

            {{-- SELECTOR DE IA COMPACTO --}}
            <div class="modal-ia-selector">
                <button class="modal-ia-option" onclick="setActiveIA('deepseek')" id="modal-ia-deepseek">
                    <i class="fas fa-robot"></i>
                    <span>DS</span>
                </button>
                <button class="modal-ia-option active" onclick="setActiveIA('gemini')" id="modal-ia-gemini">
                    <i class="fas fa-brain"></i>
                    <span>GM</span>
                </button>
            </div>

            <button onclick="toggleChatModal()" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        {{-- Cuerpo del modal con mensajes --}}
        <div class="chat-modal-body" id="chat-modal-messages">
            <div class="message">
                <div class="message-avatar assistant-avatar gemini">
                    <i class="fas fa-brain"></i>
                </div>
                <div class="message-bubble assistant-message deepseek">
                    <div class="flex items-center gap-1 mb-1">
                        <span class="font-semibold">RumberoAI</span>
                        <span class="modal-message-ia-badge gemini">
                            <i class="fas fa-brain mr-1"></i> Gemini
                        </span>
                    </div>
                    <p>🎉 ¡Hola! Soy RumberoAI. ¿Qué descuento buscas hoy?</p>
                    <span class="message-time">ahora</span>
                </div>
            </div>
        </div>

        {{-- Footer del modal --}}
        <div class="chat-modal-footer">
            <div class="quick-options">
                <span class="quick-option" onclick="sendQuickMessage('💊 Farmacias')">💊 Farmacias</span>
                <span class="quick-option" onclick="sendQuickMessage('🍔 Restaurantes')">🍔 Restaurantes</span>
                <span class="quick-option" onclick="sendQuickMessage('🎉 Discotecas')">🎉 Discotecas</span>
                <span class="quick-option" onclick="sendQuickMessage('🏨 Posadas')">🏨 Posadas</span>
            </div>
            <div class="modal-input-wrapper">
                <input type="text" id="chat-modal-input" placeholder="Escribe tu mensaje..."
                    onkeypress="if(event.key==='Enter') sendModalMessage()">
                <button onclick="sendModalMessage()" class="modal-send-button">
                    <i class="fas fa-paper-plane"></i>
                </button>
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
        let currentIA = 'gemini'; // IA activa

        // Función para abrir/cerrar el modal
        function toggleChatModal() {
            const modal = document.getElementById('chat-modal');
            const overlay = document.getElementById('chat-overlay');

            if (modal) {
                modal.classList.toggle('show');
                if (overlay) {
                    overlay.classList.toggle('show');
                }
                isModalOpen = modal.classList.contains('show');

                // Si se abre el modal, enfocar el input
                if (isModalOpen) {
                    setTimeout(() => {
                        document.getElementById('chat-modal-input')?.focus();
                    }, 300);
                }
            }
        }

        // Función para cambiar de IA
        function setActiveIA(ia) {
            currentIA = ia;

            // Actualizar clases de los botones
            const deepseekBtn = document.getElementById('modal-ia-deepseek');
            const geminiBtn = document.getElementById('modal-ia-gemini');

            if (ia === 'deepseek') {
                deepseekBtn.classList.add('active');
                geminiBtn.classList.remove('active');
                addSystemMessage('🔄 Cambiaste a DeepSeek');
            } else {
                geminiBtn.classList.add('active');
                deepseekBtn.classList.remove('active');
                addSystemMessage('🔄 Cambiaste a Gemini');
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

            addModalMessage(mensaje, 'user');
            input.value = '';
            showModalTyping();

            try {
                const response = await fetch('{{ url('/api/ia/chat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        mensaje: mensaje,
                        ia_preferida: currentIA
                    })
                });

                const data = await response.json();
                hideModalTyping();

                if (data.success) {
                    const respuesta = data.data?.respuesta || data.mensaje || 'Respuesta generada';
                    const iaUsada = data.ia_utilizada || currentIA;
                    addModalMessage(respuesta, 'assistant', iaUsada);

                    // Si la IA usada es diferente a la seleccionada, actualizar selector
                    if (iaUsada.includes('gemini') && currentIA !== 'gemini') {
                        setActiveIA('gemini');
                    } else if (iaUsada.includes('deepseek') && currentIA !== 'deepseek') {
                        setActiveIA('deepseek');
                    }
                } else {
                    addModalMessage('Lo siento, hubo un error. Intenta de nuevo.', 'assistant');
                }
            } catch (error) {
                hideModalTyping();
                addModalMessage('Error de conexión. Intenta de nuevo.', 'assistant');
                console.error('Error:', error);
            }
        }

        // Función para agregar mensajes al modal
        function addModalMessage(text, type, iaType = null) {
            const container = document.getElementById('chat-modal-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type === 'user' ? 'justify-end' : ''}`;

            const time = new Date().toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });

            if (type === 'user') {
                messageDiv.innerHTML = `
                    <div class="message-bubble user-message">
                        <p>${escapeHtml(text)}</p>
                        <span class="message-time">${time}</span>
                    </div>
                    <div class="message-avatar user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                `;
            } else {
                const iaClass = iaType && iaType.includes('gemini') ? 'gemini' : 'deepseek';
                const iaName = iaType && iaType.includes('gemini') ? 'Gemini' : 'DeepSeek';
                const iaIcon = iaType && iaType.includes('gemini') ? 'fa-brain' : 'fa-robot';

                messageDiv.innerHTML = `
                    <div class="message-avatar assistant-avatar ${iaClass}">
                        <i class="fas ${iaIcon}"></i>
                    </div>
                    <div class="message-bubble assistant-message ${iaClass}">
                        <div class="flex items-center gap-1 mb-1">
                            <span class="font-semibold">RumberoAI</span>
                            <span class="modal-message-ia-badge ${iaClass}">
                                <i class="fas ${iaIcon} mr-1"></i> ${iaName}
                            </span>
                        </div>
                        <p>${escapeHtml(text)}</p>
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
            systemDiv.className = 'flex justify-center my-2';
            systemDiv.innerHTML = `
                <div class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs">
                    ${text}
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
            typingDiv.className = 'message';

            const iaIcon = currentIA === 'gemini' ? 'fa-brain' : 'fa-robot';
            const iaClass = currentIA === 'gemini' ? 'gemini' : 'deepseek';

            typingDiv.innerHTML = `
                <div class="message-avatar assistant-avatar ${iaClass}">
                    <i class="fas ${iaIcon}"></i>
                </div>
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <span class="text-xs text-gray-500 ml-2">${currentIA === 'gemini' ? 'Gemini' : 'DeepSeek'} pensando...</span>
                </div>
            `;
            container.appendChild(typingDiv);
            container.scrollTop = container.scrollHeight;
        }

        // Función para ocultar indicador de typing
        function hideModalTyping() {
            const typing = document.getElementById('modal-typing');
            if (typing) {
                typing.remove();
            }
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
            console.log('Chat RumberoAI inicializado');

            // Ocultar notificación después de 5 segundos
            setTimeout(() => {
                const notif = document.querySelector('.chat-bubble-notification');
                if (notif) {
                    notif.style.display = 'none';
                }
            }, 5000);
        });
    </script>
</body>

</html>
