<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar Responsive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <a href="{{ url('/') }}" class="header-logo">
                <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Logo Rumbero Extremo">
            </a>
            <nav class="main-nav">
                <ul class="nav-links">
                    <!-- Dashboard link - visible solo con sesión -->
                    <li class="auth-only" style="display: none;">
                        <a href="{{ route('dashboard') }}" class="nav-item">Dashboard</a>
                    </li>
                    
                    <!-- Enlaces siempre visibles -->
                    <li><a href="{{ url('/about') }}" class="nav-item">Sobre Nosotros</a></li>
                    <li><a href="{{ url('/demo') }}" class="nav-item">Demo</a></li>
                    <li><a href="{{ url('/contact') }}" class="nav-item">Contacto</a></li>
                    
                    <!-- Botón de autenticación -->
                    <li>
                        <a href="{{ route('login') }}" class="cta-button-nav" id="auth-btn">Acceder</a>
                    </li>
                    
                    <!-- Formulario de logout (oculto por defecto) -->
                    <li class="auth-only" style="display: none;">
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="submit" class="cta-button-nav">Cerrar Sesión</button>
                        </form>
                    </li>
                </ul>
            </nav>
            <button class="menu-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <script>
        // Función para cambiar el estado de autenticación
        function setAuthState(isAuthenticated) {
            const authElements = document.querySelectorAll('.auth-only');
            const authBtn = document.getElementById('auth-btn');
            const logoutForm = document.getElementById('logout-form');
            
            if (isAuthenticated) {
                // Usuario autenticado
                authElements.forEach(el => el.style.display = 'flex');
                
                if (authBtn) {
                    authBtn.style.display = 'none'; // Ocultar botón Acceder
                }
            } else {
                // Usuario no autenticado
                authElements.forEach(el => el.style.display = 'none');
                
                if (authBtn) {
                    authBtn.style.display = 'inline-block'; // Mostrar botón Acceder
                }
            }
        }

        // Verificar autenticación al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Leer el meta tag con el estado de autenticación
            const isAuthenticated = document.querySelector('meta[name="user-authenticated"]').content === 'true';
            setAuthState(isAuthenticated);

            // Configuración del menú móvil (tu código existente)
            const menuToggle = document.querySelector('.menu-toggle');
            const mainNav = document.querySelector('.main-nav');

            if (menuToggle && mainNav) {
                menuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mainNav.classList.toggle('active');

                    const icon = menuToggle.querySelector('i');
                    if (mainNav.classList.contains('active')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    } else {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });

                document.addEventListener('click', function(event) {
                    if (mainNav.classList.contains('active') &&
                        !mainNav.contains(event.target) &&
                        !menuToggle.contains(event.target)) {
                        mainNav.classList.remove('active');

                        const icon = menuToggle.querySelector('i');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });

                mainNav.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });
    </script>
</body>
</html>