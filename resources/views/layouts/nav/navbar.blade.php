<header class="main-header">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
    <div class="header-container">
        <a href="{{ url('/') }}" class="header-logo">
            <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Logo Rumbero Extremo">
        </a>
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
                    {{-- Si el usuario está autenticado --}}
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="logout-form">
                            @csrf
                            <button type="submit" class="cta-button">Logout</button>
                        </form>
                    </li>
                @else
                    {{-- Si el usuario no está autenticado --}}
                    <li><a href="{{ route('login') }}" class="cta-button">Acceder</a></li>
                @endauth
            </ul>
        </nav>
    </div>
</header>