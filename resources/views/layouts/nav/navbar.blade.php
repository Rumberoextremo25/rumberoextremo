<header class="main-header">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
    <div class="header-container">
        <a href="{{ url('/') }}" class="header-logo">
            <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Logo Rumbero Extremo">
        </a>
        <nav class="main-nav">
            <ul class="nav-links">
                @auth
                    <li><a href="{{ url('/dashboard') }}" class="nav-item">Dashboard</a></li>
                @endauth
                <li><a href="{{ url('/about') }}" class="nav-item">Sobre Nosotros</a></li>
                <li><a href="{{ url('/demo') }}" class="nav-item">Demo</a></li>
                <li><a href="{{ url('/contact') }}" class="nav-item">Contacto</a></li>
                @auth
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="logout-form">
                            @csrf
                            <button type="submit" class="cta-button-nav">Logout</button>
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}" class="cta-button-nav">Acceder</a></li>
                @endauth
            </ul>
        </nav>
        {{-- BOTÓN DEL MENÚ DE HAMBURGUESA --}}
        <button class="menu-toggle" aria-label="Toggle navigation">
            &#9776;
        </button>
    </div>
</header>
{{-- Script JavaScript para la funcionalidad del menú de hamburguesa --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const mainNav = document.querySelector('.main-nav');
        if (menuToggle && mainNav) {
            menuToggle.addEventListener('click', function() {
                mainNav.classList.toggle('active');
            });
            document.addEventListener('click', function(event) {
                if (!mainNav.contains(event.target) && !menuToggle.contains(event.target)) {
                    mainNav.classList.remove('active');
                }
            });
        }
    });
</script>