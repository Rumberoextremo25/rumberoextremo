<footer>
    {{-- Se asume que el archivo CSS 'footer.css' está correctamente enlazado --}}
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">

    {{-- Este enlace es necesario para los íconos de Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <div class="container footer-grid-container">
        {{-- Sección 1: Rumbero Extremo (Acerca de) --}}
        <div class="footer-section footer-about">
            <a href="{{ url('/') }}" class="footer-logo-link">
                <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Logo Rumbero Extremo" class="footer-logo">
            </a>
            <h3>Rumbero Extremo</h3>
            <p>Somos la plataforma que te acompaña para tu Recreación y Entretenimiento, descubre los sitios en
                tendencia y con las mejores promociones.

                Descubre los mejores eventos y lugares, conecta con amigos y vive la experiencia de ser un Rumbero
                Extremo</p>

            {{-- BOTONES DE TIENDAS OFICIALES --}}
            <div class="store-badges-container">
                <!-- Google Play Button -->
                <a href="https://play.google.com/store/apps/details?id=com.sistema.rumberoextremooficial" class="store-badge google-play-badge" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-google-play"></i>
                    <span class="badge-text">Google Play</span>
                </a>

                <!-- App Store Button - Coming Soon -->
                <a href="#" class="store-badge app-store-badge disabled">
                    <i class="fab fa-apple"></i>
                    <span class="badge-text">Próximamente</span>
                </a>
            </div>
        </div>

        {{-- Sección 2: Enlaces Rápidos --}}
        <div class="footer-section footer-links">
            <h3>Enlaces Rápidos</h3>
            <ul>
                <li><a href="{{ url('/faq') }}">Preguntas Frecuentes</a></li>
                <li><a href="{{ url('/about') }}">¿Quiénes somos?</a></li>
                <li><a href="{{ url('/terms') }}">Términos & Condiciones</a></li>
                <li><a href="{{ url('/privacy') }}">Políticas de Privacidad</a></li>
            </ul>
        </div>

        {{-- Sección 3: Conéctate (con el botón de RumberoAI MEJORADO) --}}
        <div class="footer-section footer-connect">
            <div class="connect-icon-container">
                {{-- Ícono SVG para la sección "Conéctate" --}}
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke-width="1"
                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-share-2">
                    <circle cx="18" cy="5" r="3"></circle>
                    <circle cx="6" cy="12" r="3"></circle>
                    <circle cx="18" cy="19" r="3"></circle>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                </svg>
            </div>
            <h3>Conécta con Rumbero Extremo</h3>
            <p>Mantente al tanto de nuestras últimas noticias y avances.</p>

            {{-- Íconos de redes sociales para la sección de "Conéctate" --}}
            <div class="social-icons-connect">
                <a href="https://facebook.com/rumberoextremo" target="_blank" aria-label="Facebook" class="facebook">
                    <img src="assets/img/home/logo_facebook.png" alt="Logo de Facebook">
                </a>
                <a href="https://instagram.com/rumberoextremo" target="_blank" aria-label="Instagram" class="instagram">
                    <img src="assets/img/home/logo_instagram.png" alt="Logo de Instagram">
                </a>
            </div>
        </div>

        {{-- Sección 4: Suscríbete a nuestro newsletter --}}
        <div class="footer-section footer-newsletter">
            <h3>Suscríbete a Nuestro Newsletter</h3>
            <p>En Rumbero Extremo las Noticias Vuelan.</p>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form">
                @csrf

                {{-- Campo HONEYPOT - Oculto para humanos, visible para bots --}}
                <div
                    style="position: absolute; left: -9999px; top: -9999px; opacity: 0; height: 0; width: 0; overflow: hidden;">
                    <input type="text" name="honeypot" id="honeypot" tabindex="-1" autocomplete="off">
                </div>

                {{-- Campo timestamp para prevenir envíos rápidos --}}
                <input type="hidden" name="timestamp" value="{{ time() }}">

                <div class="newsletter-input-group">
                    <input type="email" name="email" placeholder="Tu correo electrónico" required
                        value="{{ old('email') }}">
                    <button type="submit" class="subscribe-button">Suscribir</button>
                </div>

                @error('email')
                    <span class="error-message"
                        style="color: #ffcccc; font-size: 0.85em; display: block; margin-top: 8px;">{{ $message }}</span>
                @enderror
                @error('honeypot')
                    <span class="error-message"
                        style="color: #ffcccc; font-size: 0.85em; display: block; margin-top: 8px;">Solicitud inválida. Por
                        favor, intenta de nuevo.</span>
                @enderror
                @error('timestamp')
                    <span class="error-message"
                        style="color: #ffcccc; font-size: 0.85em; display: block; margin-top: 8px;">Por favor, espera unos
                        segundos antes de enviar.</span>
                @enderror
                @if (session('newsletter_success'))
                    <span class="success-message"
                        style="color: #ccffcc; font-size: 0.85em; display: block; margin-top: 8px;">{{ session('newsletter_success') }}</span>
                @endif
                @if (session('newsletter_error'))
                    <span class="error-message"
                        style="color: #ffcccc; font-size: 0.85em; display: block; margin-top: 8px;">{{ session('newsletter_error') }}</span>
                @endif
            </form>
        </div>
    </div>

    {{-- Sección de copyright --}}
    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} Rumbero Extremo. Todos los derechos reservados.</p>
        <p>Hecho con ❤️ en Venezuela.</p>
    </div>

    <script>
        // Función para mostrar notificaciones modernas
        function showToast(message, type = 'success') {
            // Remover toasts existentes
            const existingToasts = document.querySelectorAll('.toast-notification');
            existingToasts.forEach(toast => toast.remove());

            // Crear el elemento toast
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;

            // Configurar iconos
            const icons = {
                success: '✓',
                error: '✗',
                info: 'ℹ'
            };

            const icon = icons[type] || '✓';

            // Estructura del toast
            toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">${message}</div>
            <div class="toast-close">×</div>
        `;

            // Agregar al body
            document.body.appendChild(toast);

            // Función para cerrar
            const closeToast = () => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 300);
            };

            // Evento para cerrar con el botón
            const closeBtn = toast.querySelector('.toast-close');
            closeBtn.addEventListener('click', closeToast);

            // Auto cerrar después de 5 segundos
            setTimeout(closeToast, 5000);

            // Cerrar al hacer clic en el toast (opcional)
            toast.addEventListener('click', (e) => {
                if (e.target !== closeBtn) {
                    closeToast();
                }
            });
        }

        // Cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Manejar el envío del formulario de newsletter
            const newsletterForm = document.querySelector('.newsletter-form');

            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    console.log('Formulario enviado');
                });
            }

            // Mostrar notificaciones de sesión con el estilo moderno
            @if (session('newsletter_success'))
                showToast('{{ session('newsletter_success') }}', 'success');
            @endif

            @if (session('newsletter_error'))
                showToast('{{ session('newsletter_error') }}', 'error');
            @endif

            @if ($errors->any())
                @php
                    $errorMessages = implode('\n', $errors->all());
                @endphp
                showToast('{{ $errorMessages }}', 'error');
            @endif

            // También mostrar notificaciones flash normales (por si hay otros mensajes)
            @if (session('success'))
                showToast('{{ session('success') }}', 'success');
            @endif

            @if (session('error'))
                showToast('{{ session('error') }}', 'error');
            @endif
        });
    </script>
</footer>
