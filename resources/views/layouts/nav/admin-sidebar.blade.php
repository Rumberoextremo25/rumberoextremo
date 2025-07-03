<div class="sidebar">
    <div class="logo-container">
        {{-- Asegúrate de que esta ruta a la imagen sea correcta --}}
        <a href="{{ url('/') }}">
            <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Logo Rumbero Extremo">
        </a>
    </div>
    <ul>
        {{-- Enlaces para todos los usuarios (siempre visibles, o ajusta según necesites) --}}
        {{-- El Dashboard podría ser visible para todos, o solo para admins/aliados si es un dashboard de gestión --}}
        @if(Auth::check()) {{-- Asegúrate de que haya un usuario logueado --}}
            {{-- Dashboard (visible para admin y aliado, o para todos si es un dashboard general) --}}
            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i
                                class="fas fa-chart-line"></i> Dashboard</a></li>
            @endif

            {{-- Perfil (visible para todos los roles) --}}
            <li><a href="{{ route('profile') }}" class="{{ request()->routeIs('profile') ? 'active' : '' }}"><i
                            class="fas fa-user-circle"></i> Perfil</a></li>

            {{-- Usuarios (solo para Admin) --}}
            @if(Auth::user()->role === 'admin')
                <li><a href="{{ route('users') }}" class="{{ request()->routeIs('users') ? 'active' : '' }}"><i
                                class="fas fa-users"></i> Usuarios</a></li>
            @endif

            {{-- Aliados (solo para Admin) --}}
            @if(Auth::user()->role === 'admin')
                <li><a href="{{ route('aliado') }}" class="{{ request()->routeIs('aliado') ? 'active' : '' }}"><i
                                class="fas fa-handshake"></i> Aliados</a></li>
            @endif

            {{-- Productos (solo para Admin) --}}
            @if(Auth::user()->role === 'admin')
                <li><a href="{{ route('products') }}" class="{{ request()->routeIs('product') ? 'active' : '' }}"><i
                                class="fas fa-box"></i> Productos</a></li>
            @endif

            {{-- Reportes (para Admin y Aliado) --}}
            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                <li><a href="{{ route('reportes') }}"
                        class="{{ request()->routeIs('admin.reportes') ? 'active' : '' }}"><i
                                class="fas fa-file-invoice-dollar"></i> Reportes</a></li>
            @endif

            {{-- Configuración (solo para Admin) --}}
            @if(Auth::user()->role === 'admin')
                <li><a href="{{ route('settings') }}"
                        class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}"><i class="fas fa-cog"></i>
                        Configuración</a></li>
            @endif

            {{-- Cerrar Sesión (visible para todos los roles logueados) --}}
            <li>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0; padding: 0;">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </form>
            </li>
        @endif {{-- Fin de Auth::check() --}}
    </ul>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebarLinks = document.querySelectorAll('.sidebar ul li a');

        sidebarLinks.forEach(link => {
            // Remove 'active' class from all links first
            link.classList.remove('active');

            // Logic to activate the sidebar link based on the current route
            // This is a simplification and might need adjustments if your routes are more complex
            // For example, if 'aliado' route has nested routes like 'aliado/create', you might need more specific logic.
            if (window.location.href.includes(link.getAttribute('href')) && link.getAttribute('href') !== '{{ route('logout') }}') {
                link.classList.add('active');
            }
        });
    });
</script>