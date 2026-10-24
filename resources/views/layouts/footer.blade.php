<footer class="px-footer">
    {{-- Estilos de la capa pública (incluye el footer) --}}
    <link rel="stylesheet"
        href="{{ asset('css/public-modern.css') }}?v={{ filemtime(public_path('css/public-modern.css')) }}">

    <div class="px-footer__inner">

        {{-- ============ Marca ============ --}}
        <div>
            <a href="{{ url('/') }}" class="px-footer__logo" aria-label="Rumbero Extremo — Inicio">
                <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Rumbero Extremo" loading="lazy">
            </a>

            <p>
                Somos la plataforma que te acompaña en tu recreación y entretenimiento: descubre los sitios en
                tendencia, conecta con amigos y aprovecha las mejores promociones.
            </p>

            <div class="px-footer__stores">
                <a href="https://play.google.com/store/apps/details?id=com.sistema.rumberoextremooficial"
                    class="px-footer__store" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-google-play"></i> Google Play
                </a>

                <span class="px-footer__store is-disabled" aria-disabled="true">
                    <i class="fab fa-apple"></i> App Store · Pronto
                </span>
            </div>
        </div>

        {{-- ============ Enlaces ============ --}}
        <div>
            <h3>Enlaces rápidos</h3>
            <ul class="px-footer__links">
                <li><a href="{{ url('/faq') }}">Preguntas frecuentes</a></li>
                <li><a href="{{ url('/about') }}">¿Quiénes somos?</a></li>
                <li><a href="{{ url('/demo') }}">Demo</a></li>
                <li><a href="{{ url('/contact') }}">Contacto</a></li>
                <li><a href="{{ url('/terms') }}">Términos y condiciones</a></li>
                <li><a href="{{ url('/privacy') }}">Política de privacidad</a></li>
                <li><a href="{{ url('/cookies') }}">Política de cookies</a></li>
            </ul>
        </div>

        {{-- ============ Conéctate ============ --}}
        <div>
            <h3>Conéctate</h3>
            <p>Mantente al tanto de nuestras últimas noticias, eventos y avances.</p>

            <div class="px-footer__social">
                <a href="https://facebook.com/rumberoextremo" target="_blank" rel="noopener"
                    aria-label="Facebook de Rumbero Extremo">
                    <img src="{{ asset('assets/img/home/logo_facebook.png') }}" alt="Facebook" loading="lazy">
                </a>
                <a href="https://instagram.com/rumberoextremo" target="_blank" rel="noopener"
                    aria-label="Instagram de Rumbero Extremo">
                    <img src="{{ asset('assets/img/home/logo_instagram.png') }}" alt="Instagram" loading="lazy">
                </a>
            </div>
        </div>

        {{-- ============ Newsletter ============ --}}
        <div>
            <h3>Newsletter</h3>
            <p>En Rumbero Extremo las noticias vuelan. Suscríbete y recibe lo nuevo.</p>

            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form">
                @csrf

                {{-- Honeypot: invisible para personas, visible para bots --}}
                <div style="position:absolute; left:-9999px; top:-9999px; opacity:0; height:0; width:0; overflow:hidden;"
                    aria-hidden="true">
                    <input type="text" name="honeypot" id="honeypot" tabindex="-1" autocomplete="off">
                </div>

                <input type="hidden" name="timestamp" value="{{ time() }}">

                <div class="px-newsletter">
                    <input type="email" name="email" placeholder="Tu correo" required
                        value="{{ old('email') }}" aria-label="Tu correo electrónico">
                    <button type="submit">Suscribir</button>
                </div>

                @error('email')
                    <span class="px-newsletter__msg is-error">{{ $message }}</span>
                @enderror
                @error('honeypot')
                    <span class="px-newsletter__msg is-error">Solicitud inválida. Intenta de nuevo.</span>
                @enderror
                @error('timestamp')
                    <span class="px-newsletter__msg is-error">Espera unos segundos antes de enviar.</span>
                @enderror
                @if (session('newsletter_success'))
                    <span class="px-newsletter__msg is-ok">{{ session('newsletter_success') }}</span>
                @endif
                @if (session('newsletter_error'))
                    <span class="px-newsletter__msg is-error">{{ session('newsletter_error') }}</span>
                @endif
            </form>
        </div>
    </div>

    {{-- ============ Barra inferior ============ --}}
    <div class="px-footer__bottom">
        <div class="px-footer__bottom-inner">
            <p>&copy; {{ date('Y') }} Rumbero Extremo. Todos los derechos reservados.</p>
            <p>
                Hecho con <span aria-hidden="true">❤️</span> en Venezuela ·
                <a href="{{ url('/terms') }}">Términos</a> ·
                <a href="{{ url('/privacy') }}">Privacidad</a> ·
                <a href="{{ url('/cookies') }}">Cookies</a>
            </p>
        </div>
    </div>

    <script>
        // Notificaciones flotantes (las usan los mensajes flash del layout)
        function showToast(message, type = 'success') {
            document.querySelectorAll('.toast-notification').forEach(toast => toast.remove());

            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;

            const icons = { success: '✓', error: '✗', info: 'ℹ' };
            const icon = icons[type] || '✓';

            toast.innerHTML = `
                <div class="toast-icon">${icon}</div>
                <div class="toast-content">${message}</div>
                <div class="toast-close">×</div>
            `;

            document.body.appendChild(toast);

            const closeToast = () => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 300);
            };

            const closeBtn = toast.querySelector('.toast-close');
            closeBtn.addEventListener('click', closeToast);
            setTimeout(closeToast, 5000);
            toast.addEventListener('click', (e) => {
                if (e.target !== closeBtn) closeToast();
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            @if (session('newsletter_success'))
                showToast('{{ session('newsletter_success') }}', 'success');
            @endif

            @if (session('newsletter_error'))
                showToast('{{ session('newsletter_error') }}', 'error');
            @endif

            @if ($errors->any())
                @php $errorMessages = implode('\n', $errors->all()); @endphp
                showToast('{{ $errorMessages }}', 'error');
            @endif

            @if (session('success'))
                showToast('{{ session('success') }}', 'success');
            @endif

            @if (session('error'))
                showToast('{{ session('error') }}', 'error');
            @endif
        });
    </script>
</footer>
