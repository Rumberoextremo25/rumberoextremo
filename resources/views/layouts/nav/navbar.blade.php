<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar Responsive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/navbar.css">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <a href="/" class="header-logo">
                <img src="assets/img/IMG_4254.png" alt="Logo Rumbero Extremo">
            </a>
            <nav class="main-nav">
                <ul class="nav-links">
                    <!-- Estos elementos se mostrarán solo si el usuario ha iniciado sesión -->
                    <li class="auth-only" style="display: none;"><a href="/dashboard" class="nav-item">Dashboard</a></li>
                    <li><a href="/about" class="nav-item">Sobre Nosotros</a></li>
                    <li><a href="/demo" class="nav-item">Demo</a></li>
                    <li><a href="/contact" class="nav-item">Contacto</a></li>
                    <!-- Botón de Logout (solo para usuarios autenticados) -->
                    <li class="auth-only" style="display: none;">
                        <form action="/logout" method="POST" class="logout-form">
                            <button type="submit" class="cta-button-nav">Logout</button>
                        </form>
                    </li>
                    <!-- Botón de Acceder (solo para usuarios no autenticados) -->
                    <li class="no-auth-only">
                        <a href="/login" class="cta-button-nav">Acceder</a>
                    </li>
                </ul>
            </nav>
            <button class="menu-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>
    <script>
        // Función para simular el estado de autenticación
        function toggleAuth(isAuthenticated) {
            const authElements = document.querySelectorAll('.auth-only');
            const noAuthElements = document.querySelectorAll('.no-auth-only');
            const loginBtn = document.getElementById('login-btn');
            const logoutBtn = document.getElementById('logout-btn');
            
            if (isAuthenticated) {
                // Mostrar elementos para usuarios autenticados
                authElements.forEach(el => el.style.display = 'flex');
                noAuthElements.forEach(el => el.style.display = 'none');
                loginBtn.style.display = 'none';
                logoutBtn.style.display = 'inline-block';
            } else {
                // Mostrar elementos para usuarios no autenticados
                authElements.forEach(el => el.style.display = 'none');
                noAuthElements.forEach(el => el.style.display = 'flex');
                loginBtn.style.display = 'inline-block';
                logoutBtn.style.display = 'none';
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const mainNav = document.querySelector('.main-nav');

            if (menuToggle && mainNav) {
                menuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mainNav.classList.toggle('active');

                    // Cambiar icono al abrir/cerrar
                    const icon = menuToggle.querySelector('i');
                    if (mainNav.classList.contains('active')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    } else {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });

                // Cerrar el menú al hacer clic fuera de él
                document.addEventListener('click', function(event) {
                    if (mainNav.classList.contains('active') &&
                        !mainNav.contains(event.target) &&
                        !menuToggle.contains(event.target)) {
                        mainNav.classList.remove('active');

                        // Restaurar icono de hamburguesa
                        const icon = menuToggle.querySelector('i');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });

                // Prevenir que los clics dentro del menú cierren el menú
                mainNav.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });
    </script>
</body>
</html>