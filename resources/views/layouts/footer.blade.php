<footer>
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <div class="footer-wave"></div> {{-- Mantener la onda si quieres un efecto decorativo similar --}}

    <div class="container footer-grid-container">
        {{-- Sección 1: Rumbero Extremo (Acerca de) --}}
        <div class="footer-section footer-about">
            <a href="{{ url('/') }}" class="footer-logo-link">
                <img src="{{ asset('assets/img/IMG_4253.png') }}" alt="Logo Rumbero Extremo" class="footer-logo">
            </a>
            <h3>Rumbero Extremo</h3>
            <p>Tu guía definitiva para la vida nocturna en Venezuela. Descubre eventos, conecta con amigos y vive experiencias inolvidables en los mejores lugares de rumba.</p>
            {{-- Los enlaces de redes sociales se pueden agregar aquí si deseas mantenerlos --}}
            {{--
            <div class="social-links">
                <a href="https://facebook.com/rumberoextremo" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://instagram.com/rumberoextremo" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://tiktok.com/@rumberoextremo" target="_blank" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
            </div>
            --}}
        </div>

        {{-- Sección 2: Enlaces Rápidos --}}
        <div class="footer-section footer-links">
            <h3>Enlaces Rápidos</h3>
            <ul>
                <li><a href="{{ url('/') }}">Inicio</a></li>
                <li><a href="{{ url('/about') }}">¿Quiénes somos?</a></li>
                <li><a href="{{ url('/terms') }}">Términos & Condiciones</a></li>
                <li><a href="{{ url('/privacy') }}">Políticas de Privacidad</a></li>
                {{-- Elimino FAQ si no está en el nuevo diseño visual, puedes añadirlo si lo necesitas --}}
                {{-- <li><a href="{{ url('/faq') }}">Preguntas Frecuentes</a></li> --}}
            </ul>
        </div>

        {{-- Sección 3: Conéctate (nueva sección visual) --}}
        <div class="footer-section footer-connect">
            <div class="connect-icon-container">
                {{-- Reemplaza con tu SVG o Font Awesome para el ícono de "Conéctate" --}}
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#8A2BE2" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-share-2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
            </div>
            <h3>Conéctate</h3>
            <p>Mantente al tanto de nuestras últimas noticias y avances.</p>
        </div>

        {{-- Sección 4: Suscríbete a nuestro newsletter --}}
        <div class="footer-section footer-newsletter">
            <h3>Suscríbete a nuestro newsletter</h3>
            <p>Recibe las últimas noticias y promociones directamente en tu bandeja de entrada.</p>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form">
                @csrf
                <div class="newsletter-input-group">
                    <input type="email" name="email" placeholder="Tu correo electrónico" required value="{{ old('email') }}">
                    <button type="submit" class="subscribe-button">Suscribir</button>
                </div>
                @error('email')
                    <span class="error-message" style="color: #ffcccc; font-size: 0.85em; display: block; margin-top: 8px;">{{ $message }}</span>
                @enderror
                @if (session('newsletter_success'))
                    <span class="success-message" style="color: #ccffcc; font-size: 0.85em; display: block; margin-top: 8px;">{{ session('newsletter_success') }}</span>
                @endif
            </form>
        </div>
    </div>

    {{-- Sección de copyright --}}
    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} Rumbero Extremo App. Todos los derechos reservados.</p>
        <p>Hecho en Venezuela.</p> {{-- Mantengo el ícono del corazón si quieres estilizarlo con CSS --}}
    </div>
</footer>