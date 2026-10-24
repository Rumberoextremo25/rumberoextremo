@extends('layouts.admin')

@section('title', 'Chat de Soporte')

@section('page_title_toolbar', 'Chat de Soporte')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endpush

@section('content')
<div class="chat-container">
    {{-- ==================================================================
         Cabecera + datos de soporte
         ================================================================== --}}
    <div class="chat-header">
        <div class="chat-header-left">
            <h1 class="chat-title">
                <i class="fa-solid fa-headset"></i> Centro de Mensajes
            </h1>
            <p class="chat-subtitle">
                Las conversaciones llegan aquí cuando la IA no responde o el usuario pide un asesor.
            </p>
        </div>

        <div class="chat-header-right">
            <span class="chat-ai-state {{ $aiAvailable ? 'is-on' : 'is-off' }}">
                <i class="fa-solid fa-robot"></i>
                IA {{ $aiAvailable ? 'configurada' : 'desactivada' }}
            </span>

            <button type="button" onclick="recargarConversaciones()" class="chat-btn chat-btn-refresh" id="btn-recargar">
                <i class="fa-solid fa-rotate-right"></i> Recargar
            </button>
        </div>
    </div>

    {{-- Barra de soporte: correo y WhatsApp configurados --}}
    <div class="chat-support-bar">
        <span class="chat-support-item">
            <i class="fa-regular fa-envelope"></i>
            <strong>Avisos a:</strong> {{ $supportEmail ?: 'sin configurar' }}
        </span>

        @if ($supportWhatsapp)
            <span class="chat-support-item">
                <i class="fa-brands fa-whatsapp"></i>
                <strong>WhatsApp:</strong> +{{ $supportWhatsapp }}
            </span>
        @endif

        <a href="{{ route('admin.settings') }}#soporte" class="chat-support-link">
            <i class="fa-solid fa-sliders"></i> Personalizar
        </a>
    </div>

    {{-- ==================================================================
         Conversaciones y chat
         ================================================================== --}}
    <div class="chat-layout">
        {{-- Panel de conversaciones (renderizado en servidor) --}}
        <div class="chat-sidebar">
            <div class="chat-sidebar-header">
                <i class="fa-solid fa-message"></i> Conversaciones
                <span class="chat-badge" id="total-conversaciones">{{ $conversaciones->count() }}</span>
            </div>

            <div class="chat-conversations-list" id="lista-mensajes">
                @forelse ($conversaciones as $conv)
                    <div class="chat-conversation-item {{ $sessionSeleccionada === $conv['session_id'] ? 'active' : '' }}"
                        data-session="{{ $conv['session_id'] }}"
                        data-name="{{ $conv['user_name'] }}"
                        data-email="{{ $conv['user_email'] }}"
                        onclick="cargarConversacion('{{ $conv['session_id'] }}', this)">

                        <div class="chat-conversation-avatar">
                            <i class="fa-solid fa-user-circle"></i>
                        </div>

                        <div class="chat-conversation-info">
                            <div class="chat-conversation-name">
                                {{ $conv['user_name'] }}
                                @if ($conv['pending'] > 0)
                                    <span class="chat-pending-badge">{{ $conv['pending'] }}</span>
                                @endif
                            </div>
                            <div class="chat-conversation-preview">
                                {{ \Illuminate\Support\Str::limit($conv['message'], 60) }}
                            </div>
                            <div class="chat-conversation-meta">
                                <span><i class="fa-regular fa-envelope"></i> {{ $conv['user_email'] ?? 'invitado' }}</span>
                                <span><i class="fa-regular fa-comment"></i> {{ $conv['total_messages'] }} msgs</span>
                            </div>
                        </div>

                        <div class="chat-conversation-time">{{ $conv['time_ago'] }}</div>
                    </div>
                @empty
                    <div class="chat-empty-list">
                        <i class="fa-regular fa-inbox"></i>
                        <p>No hay conversaciones</p>
                        <small>Los mensajes de los usuarios aparecerán aquí</small>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Conversación activa --}}
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
                    <a href="#" id="btn-whatsapp" class="chat-icon-btn" target="_blank" rel="noopener"
                        title="Abrir en WhatsApp" style="display: none;">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    <button type="button" class="chat-icon-btn" onclick="marcarConversacionLeida()"
                        id="btn-marcar-leido" title="Marcar como leído" style="display: none;">
                        <i class="fa-regular fa-circle-check"></i>
                    </button>
                </div>
            </div>

            <div class="chat-messages-area" id="conversacion">
                <div class="chat-empty-state">
                    <i class="fa-regular fa-comment-dots"></i>
                    <h4>Ninguna conversación seleccionada</h4>
                    <p>Selecciona una conversación del panel izquierdo para responder</p>
                </div>
            </div>

            <div class="chat-input-area">
                <div class="chat-input-wrapper">
                    <textarea id="respuesta" class="chat-input" rows="2"
                        placeholder="Escribe tu respuesta… (Ctrl+Enter para enviar)" disabled></textarea>
                    <button type="button" class="chat-send-btn" onclick="enviarRespuesta()" id="btn-enviar" disabled>
                        <i class="fa-solid fa-paper-plane"></i> Enviar
                    </button>
                </div>
                <div class="chat-input-hint">
                    <i class="fa-regular fa-clock"></i> El usuario recibe tu respuesta en su chat (se actualiza cada 5 s)
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let sessionIdActual = @json($sessionSeleccionada);
let intervaloConversacion = null;

function getToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function recargarConversaciones() {
    const btn = document.getElementById('btn-recargar');
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Recargando…';

    fetch('{{ route('admin.chat') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(() => { window.location.reload(); })
        .catch(() => { window.location.reload(); });

    setTimeout(() => { btn.innerHTML = original; }, 1200);
}

function cargarConversacion(sessionId, item) {
    sessionIdActual = sessionId;

    document.getElementById('btn-enviar').disabled = false;
    document.getElementById('respuesta').disabled = false;
    document.getElementById('btn-marcar-leido').style.display = 'flex';
    document.getElementById('chat-estado').innerHTML = '<i class="fa-solid fa-circle" style="color:#22c55e"></i> Activo';

    if (item) {
        const name = item.getAttribute('data-name') || 'Usuario';
        document.getElementById('chat-usuario-nombre').textContent = name;

        const email = item.getAttribute('data-email');
        const wa = document.getElementById('btn-whatsapp');
        if (email || true) {
            wa.style.display = 'flex';
            wa.href = 'https://wa.me/?text=' + encodeURIComponent('Hola ' + name + ', te escribo del soporte de Rumbero Extremo.');
        }
    }

    document.querySelectorAll('.chat-conversation-item').forEach(el => {
        el.classList.toggle('active', el.getAttribute('data-session') === sessionId);
    });

    fetch('{{ url('/api/rumbero-ai/conversacion') }}?session_id=' + encodeURIComponent(sessionId), {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getToken() }
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.data) return;

            const html = data.data.map(msg => {
                const who = msg.is_admin ? 'Administrador' : (msg.is_ai ? 'Rumbero AI' : (msg.user_name || 'Usuario'));
                const icon = msg.is_admin ? 'fa-user-tie' : (msg.is_ai ? 'fa-robot' : 'fa-user');
                const cls = msg.is_admin ? 'message-admin' : (msg.is_ai ? 'message-ai' : 'message-user');

                return `
                    <div class="chat-message ${cls}">
                        <div class="chat-message-bubble">
                            <div class="chat-message-header">
                                <strong><i class="fa-solid ${icon}"></i> ${escapeHtml(who)}</strong>
                            </div>
                            <div class="chat-message-text">${escapeHtml(msg.message)}</div>
                            <div class="chat-message-time">${escapeHtml(msg.created_at)}</div>
                        </div>
                    </div>`;
            }).join('');

            const area = document.getElementById('conversacion');
            area.innerHTML = html || '<div class="chat-empty-state"><p>Sin mensajes</p></div>';
            area.scrollTop = area.scrollHeight;

            document.getElementById('respuesta').focus();
        })
        .catch(() => {
            document.getElementById('conversacion').innerHTML =
                '<div class="chat-error-state"><i class="fa-solid fa-circle-exclamation"></i><p>Error al cargar la conversación</p></div>';
        });
}

function enviarRespuesta() {
    const input = document.getElementById('respuesta');
    const respuesta = input.value.trim();
    if (!respuesta || !sessionIdActual) return;

    const btn = document.getElementById('btn-enviar');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enviando…';

    fetch('{{ url('/api/rumbero-ai/admin/responder') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getToken()
        },
        body: JSON.stringify({ session_id: sessionIdActual, respuesta: respuesta })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                cargarConversacion(sessionIdActual);
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Enviado';
            } else {
                alert(data.message || 'No se pudo enviar la respuesta');
            }
        })
        .catch(() => alert('Error de conexión al enviar la respuesta'))
        .finally(() => {
            setTimeout(() => { btn.innerHTML = original; btn.disabled = false; }, 1500);
        });
}

function marcarConversacionLeida() {
    if (!sessionIdActual) return;

    fetch('{{ url('/api/rumbero-ai/admin/marcar-conversacion-leida') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getToken()
        },
        body: JSON.stringify({ session_id: sessionIdActual })
    }).catch(() => {});
}

document.getElementById('respuesta')?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        enviarRespuesta();
    }
});

// Refresco automático de la conversación abierta
if (sessionIdActual) {
    cargarConversacion(sessionIdActual);
    intervaloConversacion = setInterval(() => {
        if (sessionIdActual) cargarConversacion(sessionIdActual);
    }, 8000);
}

window.addEventListener('beforeunload', () => clearInterval(intervaloConversacion));
</script>
@endsection
