{{--
  Navbar pública · fragmento (se incluye dentro de <body> del layout)
  Mantiene las clases que usa el JS del menú móvil: .menu-toggle, .main-nav,
  .nav-links, .auth-only, #auth-btn, #logout-form
--}}
<link rel="stylesheet" href="{{ asset('css/navbar-modern.css') }}?v={{ filemtime(public_path('css/navbar-modern.css')) }}">
<meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

<header class="nv-header main-header" id="nvHeader">
    <div class="nv-inner">
        <a href="{{ url('/') }}" class="nv-logo header-logo" aria-label="Rumbero Extremo — Inicio">
            <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Rumbero Extremo">
        </a>

        <nav class="nv-nav main-nav" aria-label="Navegación principal">
            <ul class="nv-links nav-links">
                <li class="auth-only" style="display:none;">
                    <a href="{{ route('dashboard') }}" class="nv-link nav-item">
                        <i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dashboard
                    </a>
                </li>

                <li>
                    <a href="{{ url('/about') }}"
                        class="nv-link nav-item {{ request()->is('about') ? 'is-active' : '' }}">
                        Sobre nosotros
                    </a>
                </li>

                <li>
                    <a href="{{ url('/demo') }}"
                        class="nv-link nav-item {{ request()->is('demo*') ? 'is-active' : '' }}">
                        Demo
                    </a>
                </li>

                <li>
                    <a href="{{ url('/contact') }}"
                        class="nv-link nav-item {{ request()->is('contact') ? 'is-active' : '' }}">
                        Contacto
                    </a>
                </li>

                <li class="nv-actions">
                    <a href="{{ route('login') }}" class="nv-cta cta-button-nav" id="auth-btn">
                        Acceder <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" id="logout-form" class="nv-logout-form"
                        style="display:none;">
                        @csrf
                        <button type="submit" class="nv-cta nv-cta--ghost cta-button-nav">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        </nav>

        <button type="button" class="nv-burger menu-toggle" aria-label="Abrir menú" aria-expanded="false"
            aria-controls="nvHeader">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('nvHeader');
        const menuToggle = document.querySelector('.menu-toggle');
        const mainNav = document.querySelector('.main-nav');
        const authBtn = document.getElementById('auth-btn');
        const logoutForm = document.getElementById('logout-form');
        const authOnly = document.querySelectorAll('.auth-only');
        const meta = document.querySelector('meta[name="user-authenticated"]');
        const isAuthenticated = meta && meta.content === 'true';

        // ---- Estado de sesión ----
        if (isAuthenticated) {
            authOnly.forEach(el => (el.style.display = ''));
            if (authBtn) authBtn.style.display = 'none';
            if (logoutForm) logoutForm.style.display = 'inline-flex';
        } else {
            authOnly.forEach(el => (el.style.display = 'none'));
            if (authBtn) authBtn.style.display = 'inline-flex';
            if (logoutForm) logoutForm.style.display = 'none';
        }

        // ---- Sombra al hacer scroll ----
        const onScroll = () => {
            if (!header) return;
            header.classList.toggle('is-scrolled', window.scrollY > 12);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        // ---- Menú móvil ----
        if (menuToggle && mainNav) {
            const icon = menuToggle.querySelector('i');

            const close = () => {
                mainNav.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            };

            menuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const open = !mainNav.classList.contains('active');
                mainNav.classList.toggle('active', open);
                menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (icon) {
                    icon.classList.toggle('fa-times', open);
                    icon.classList.toggle('fa-bars', !open);
                }
            });

            document.addEventListener('click', function(event) {
                if (
                    mainNav.classList.contains('active') &&
                    !mainNav.contains(event.target) &&
                    !menuToggle.contains(event.target)
                ) {
                    close();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') close();
            });

            mainNav.addEventListener('click', function(e) {
                if (e.target.closest('a')) close();
                e.stopPropagation();
            });
        }
    });
</script>
