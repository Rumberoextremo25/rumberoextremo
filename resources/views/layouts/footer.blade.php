<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section about-us">
                <a href="{{ url('/') }}" class="logo">
                    <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Logo Rumbero Extremo">
                </a>
                <h3>Rumbero Extremo</h3>
                <p>Tu guía definitiva para la vida nocturna en Venezuela. Descubre eventos, conecta con amigos y
                    vive experiencias inolvidables en los mejores lugares de rumba.</p>
            </div>
            <div class="footer-section quick-links">
                <h3>Enlaces Rápidos</h3>
                <ul>
                    <li><a href="{{ url('/') }}">Inicio</a></li>
                    <li><a href="{{ url('/about') }}">Sobre Nosotros</a></li>
                    <li><a href="{{ url('/terms') }}">Terminos & Condiciones</a></li>
                    <li><a href="{{ url('/privacy') }}">Politicas de Privacidad</a></li>
                    <li><a href="{{ url('/faq') }}">Preguntas Frecuentes</a></li>
                </ul>
            </div>
            <div class="footer-section connect">
                <h3>Conéctate</h3>
                <p>Mantente al tanto de nuestras últimas noticias y eventos.</p>
                <div class="social-links">
                    <a href="https://facebook.com/rumberoextremo" target="_blank" aria-label="Facebook"><i
                                class="fab fa-facebook-f"></i></a>
                    <a href="https://instagram.com/rumberoextremo" target="_blank" aria-label="Instagram"><i
                                class="fab fa-instagram"></i></a>
                    <a href="https://tiktok.com/@rumberoextremo" target="_blank" aria-label="TikTok"><i
                                class="fab fa-tiktok"></i></a>
                </div>
            </div>

            <div class="footer-section newsletter">
                <h3>Suscríbete a Nuestro Newsletter</h3>
                <p>Recibe las últimas noticias y promociones directamente en tu bandeja de entrada.</p>
                <form action="{{ route('newsletter.subscribe') }}" method="POST">
                    @csrf
                    <div class="newsletter-input-group">
                        <input type="email" name="email" placeholder="Tu correo electrónico" required value="{{ old('email') }}">
                        <button type="submit">Suscribir</button>
                    </div>
                    @error('email')
                        <span class="error-message" style="color: red; font-size: 0.8em; display: block; margin-top: 5px;">{{ $message }}</span>
                    @enderror
                    @if (session('newsletter_success'))
                        <span class="success-message" style="color: green; font-size: 0.8em; display: block; margin-top: 5px;">{{ session('newsletter_success') }}</span>
                    @endif
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Rumbero Extremo App. Todos los derechos reservados.</p>
            <p>Hecho con <i class="fas fa-heart" style="color: var(--primary-color);"></i> en Venezuela.</p>
        </div>
    </div>
</footer>