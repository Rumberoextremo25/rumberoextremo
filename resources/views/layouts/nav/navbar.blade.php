<header class="main-header">
    {{-- Asegúrate de que tu CSS para el navbar esté correctamente vinculado. --}}
    {{-- El archivo 'navbar.css' debe contener todo el CSS que te he proporcionado, incluidas las media queries. --}}
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">

    <div class="header-container">
        <a href="{{ url('/') }}" class="header-logo">
            <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Logo Rumbero Extremo">
        </a>

        {{-- Menú de navegación principal --}}
        <nav class="main-nav">
            <ul class="nav-links">
                {{-- Solo muestra el Dashboard si el usuario está autenticado --}}
                @auth
                    <li><a href="{{ url('/dashboard') }}" class="nav-item">Dashboard</a></li>
                @endauth
                <li><a href="{{ url('/about') }}" class="nav-item">Sobre Nosotros</a></li>
                <li><a href="{{ url('/demo') }}" class="nav-item">Demo</a></li>
                <li><a href="{{ url('/contact') }}" class="nav-item">Contacto</a></li>

                {{-- Lógica para cambiar el botón de Acceder/Logout --}}
                @auth
                    {{-- Si el usuario está autenticado, muestra el botón de Logout --}}
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="logout-form">
                            @csrf
                            <button type="submit" class="cta-button-nav">Logout</button>
                        </form>
                    </li>
                @else
                    {{-- Si el usuario no está autenticado, muestra el botón de Acceder --}}
                    <li><a href="{{ route('login') }}" class="cta-button-nav">Acceder</a></li>
                @endauth
            </ul>
        </nav>

        {{-- BOTÓN DEL MENÚ DE HAMBURGUESA --}}
        {{-- Este botón es crucial para activar el menú desplegable en pantallas pequeñas. --}}
        {{-- Su visibilidad se controla completamente mediante las media queries en navbar.css. --}}
        <button class="menu-toggle" aria-label="Toggle navigation">
            &#9776; {{-- Carácter Unicode para el ícono de hamburguesa (tres líneas horizontales) --}}
            {{-- Alternativa: Si usas Font Awesome, podrías poner: <i class="fas fa-bars"></i> --}}
        </button>
    </div>
</header>

{{-- Script JavaScript para la funcionalidad del menú de hamburguesa --}}
{{-- Coloca este script idealmente justo antes de la etiqueta de cierre </body> para asegurar que el DOM esté cargado. --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Selecciona el botón de hamburguesa y el menú de navegación
        const menuToggle = document.querySelector('.menu-toggle');
        const mainNav = document.querySelector('.main-nav');

        // Solo si ambos elementos existen en la página
        if (menuToggle && mainNav) {
            // Agrega un "escuchador de eventos" para el clic en el botón de hamburguesa
            menuToggle.addEventListener('click', function() {
                // Alterna (añade/quita) la clase 'active' en el menú de navegación.
                // Esta clase 'active' es la que tu CSS utiliza para mostrar u ocultar el menú.
                mainNav.classList.toggle('active');
            });

            // Opcional pero recomendado: Cierra el menú si el usuario hace clic fuera de él.
            document.addEventListener('click', function(event) {
                // Verifica si el clic no fue dentro del menú (mainNav) y tampoco fue en el botón de hamburguesa (menuToggle)
                if (!mainNav.contains(event.target) && !menuToggle.contains(event.target)) {
                    // Si se cumplen las condiciones, remueve la clase 'active' para cerrar el menú.
                    mainNav.classList.remove('active');
                }
            });
        }
    });
</script>