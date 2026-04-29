@extends('layouts.admin')

@section('title', 'Chat de Soporte')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endpush

@section('content')
<div class="chat-container">
    <!-- Header -->
    <div class="chat-header">
        <div class="chat-header-left">
            <h1 class="chat-title">
                <i class="fa-solid fa-headset"></i> Centro de Mensajes
            </h1>
            <p class="chat-subtitle">Gestiona las conversaciones con los usuarios</p>
        </div>
        <div class="chat-header-right">
            <button onclick="recargarConversaciones()" class="chat-btn chat-btn-refresh" id="btn-recargar">
                <i class="fa-solid fa-rotate-right"></i> Recargar
            </button>
        </div>
    </div>

    <!-- Conversaciones y Chat -->
    <div class="chat-layout">
        <!-- Panel de conversaciones -->
        <div class="chat-sidebar">
            <div class="chat-sidebar-header">
                <i class="fa-solid fa-message"></i> Conversaciones
                <span class="chat-badge" id="total-conversaciones">0</span>
            </div>
            <div class="chat-conversations-list" id="lista-mensajes">
                <div class="chat-loading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <p>Cargando conversaciones...</p>
                </div>
            </div>
        </div>

        <!-- Panel de conversación activa -->
        <div class="chat-main">
            <div class="chat-main-header" id="chat-header">
                <div class="chat-user-info">
                    <div class="chat-user-avatar">
                        <i class="fa-solid fa-user-circle"></i>
                    </div>
                    <div>
                        <h3 class="chat-user-name" id="chat-usuario-nombre">Soporte Rumbero</h3>
                        <span class="chat-user-status" id="chat-estado">
                            <i class="fa-solid fa-circle"></i> Sin seleccionar
                        </span>
                    </div>
                </div>
                <div class="chat-actions">
                    <button class="chat-icon-btn" onclick="marcarConversacionLeida()" id="btn-marcar-leido" style="display: none;">
                        <i class="fa-regular fa-circle-check"></i>
                    </button>
                </div>
            </div>

            <div class="chat-messages-area" id="conversacion">
                <div class="chat-empty-state">
                    <i class="fa-regular fa-comment-dots"></i>
                    <h4>Ninguna conversación seleccionada</h4>
                    <p>Selecciona una conversación del panel izquierdo para comenzar a chatear</p>
                </div>
            </div>

            <div class="chat-input-area">
                <div class="chat-input-wrapper">
                    <textarea id="respuesta" 
                              class="chat-input" 
                              rows="2" 
                              placeholder="Escribe tu respuesta... (Ctrl+Enter para enviar)"
                              disabled></textarea>
                    <button class="chat-send-btn" onclick="enviarRespuesta()" id="btn-enviar" disabled>
                        <i class="fa-solid fa-paper-plane"></i> Enviar
                    </button>
                </div>
                <div class="chat-input-hint">
                    <i class="fa-regular fa-clock"></i> El usuario recibirá tu respuesta en su chat
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let sessionIdActual = null;
let intervaloRecarga = null;

function getToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function recargarConversaciones() {
    cargarConversaciones();
    if (sessionIdActual) cargarConversacion(sessionIdActual);
    const btn = document.getElementById('btn-recargar');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Recargando...';
    setTimeout(() => { btn.innerHTML = originalHtml; }, 1000);
}

function cargarConversaciones() {
    fetch('/api/rumbero-ai/admin/pendientes', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getToken(), 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        const totalSpan = document.getElementById('total-conversaciones');
        
        if (data.success && data.data && data.data.length > 0) {
            const html = data.data.map(conv => `
                <div class="chat-conversation-item ${sessionIdActual === conv.session_id ? 'active' : ''}" 
                     data-session="${conv.session_id}"
                     onclick="cargarConversacion('${conv.session_id}')">
                    <div class="chat-conversation-avatar">
                        <i class="fa-solid fa-user-circle"></i>
                    </div>
                    <div class="chat-conversation-info">
                        <div class="chat-conversation-name">${escapeHtml(conv.user_name)}</div>
                        <div class="chat-conversation-preview">${escapeHtml(conv.message.substring(0, 60))}${conv.message.length > 60 ? '...' : ''}</div>
                        <div class="chat-conversation-meta">
                            <span><i class="fa-regular fa-envelope"></i> ${conv.user_email}</span>
                            <span><i class="fa-regular fa-comment"></i> ${conv.total_messages} msgs</span>
                        </div>
                    </div>
                    <div class="chat-conversation-time">${conv.time_ago}</div>
                </div>
            `).join('');
            document.getElementById('lista-mensajes').innerHTML = html;
            totalSpan.textContent = data.data.length;
            totalSpan.style.display = 'inline-block';
        } else {
            document.getElementById('lista-mensajes').innerHTML = `
                <div class="chat-empty-list">
                    <i class="fa-regular fa-inbox"></i>
                    <p>No hay conversaciones</p>
                    <small>Los mensajes de usuarios aparecerán aquí</small>
                </div>
            `;
            totalSpan.textContent = '0';
            totalSpan.style.display = 'none';
        }
    })
    .catch(error => {
        document.getElementById('lista-mensajes').innerHTML = `
            <div class="chat-error-list">
                <i class="fa-solid fa-circle-exclamation"></i>
                <p>Error al cargar conversaciones</p>
                <small>Verifica tu conexión</small>
            </div>
        `;
    });
}

function cargarConversacion(sessionId) {
    sessionIdActual = sessionId;
    document.getElementById('btn-enviar').disabled = false;
    document.getElementById('respuesta').disabled = false;
    document.getElementById('respuesta').focus();
    document.getElementById('btn-marcar-leido').style.display = 'flex';
    
    fetch(`/api/rumbero-ai/conversacion?session_id=${sessionId}`, {
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getToken(), 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.data) {
            const userMessages = data.data.filter(msg => !msg.is_admin);
            const usuarioNombre = userMessages.length > 0 ? (userMessages[0]?.user_name || 'Usuario') : 'Usuario';
            
            document.getElementById('chat-usuario-nombre').innerHTML = `<i class="fa-regular fa-circle-user"></i> ${escapeHtml(usuarioNombre)}`;
            document.getElementById('chat-estado').innerHTML = `<i class="fa-solid fa-circle" style="color: #22c55e;"></i> Activo`;
            
            const html = data.data.map(msg => `
                <div class="chat-message ${msg.is_admin ? 'message-admin' : 'message-user'}">
                    <div class="chat-message-bubble">
                        <div class="chat-message-header">
                            <strong>${msg.is_admin ? '<i class="fa-solid fa-user-tie"></i> Administrador' : '<i class="fa-solid fa-user"></i> ' + escapeHtml(msg.user_name || 'Usuario')}</strong>
                        </div>
                        <div class="chat-message-text">${escapeHtml(msg.message)}</div>
                        <div class="chat-message-time">${msg.created_at}</div>
                    </div>
                </div>
            `).join('');
            
            document.getElementById('conversacion').innerHTML = html;
            document.getElementById('conversacion').scrollTop = document.getElementById('conversacion').scrollHeight;
            
            document.querySelectorAll('.chat-conversation-item').forEach(el => {
                if (el.getAttribute('data-session') === sessionId) {
                    el.classList.add('active');
                } else {
                    el.classList.remove('active');
                }
            });
        }
    })
    .catch(error => {
        document.getElementById('conversacion').innerHTML = `<div class="chat-error-state"><i class="fa-solid fa-circle-exclamation"></i><p>Error al cargar la conversación</p></div>`;
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function enviarRespuesta() {
    const respuesta = document.getElementById('respuesta').value.trim();
    if (!respuesta || !sessionIdActual) return;
    
    const btn = document.getElementById('btn-enviar');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enviando...';
    
    fetch('/api/rumbero-ai/admin/responder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getToken(), 'Accept': 'application/json' },
        body: JSON.stringify({ session_id: sessionIdActual, respuesta: respuesta })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('respuesta').value = '';
            cargarConversacion(sessionIdActual);
            cargarConversaciones();
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Enviado!';
            setTimeout(() => { btn.innerHTML = originalText; btn.disabled = false; }, 2000);
        } else {
            alert('Error: ' + (data.message || 'No se pudo enviar la respuesta'));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        alert('Error al enviar la respuesta');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function marcarConversacionLeida() {
    if (!sessionIdActual) return;
    fetch('/api/rumbero-ai/admin/marcar-conversacion-leida', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getToken(), 'Accept': 'application/json' },
        body: JSON.stringify({ session_id: sessionIdActual })
    }).then(() => cargarConversaciones()).catch(() => {});
}

document.getElementById('respuesta')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        enviarRespuesta();
    }
});

cargarConversaciones();
intervaloRecarga = setInterval(cargarConversaciones, 10000);
window.addEventListener('beforeunload', () => clearInterval(intervaloRecarga));
</script>
@endsection