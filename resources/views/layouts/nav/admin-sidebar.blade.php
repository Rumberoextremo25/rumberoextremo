{{-- resources/views/layouts/partials/admin-sidebar.blade.php --}}

<aside class="sidebar">
    <div class="sidebar-header"> {{-- Cambiado de 'logo-container' a 'sidebar-header' --}}
        {{-- Asegúrate de que esta ruta a la imagen sea correcta --}}
        <a href="{{ url('/') }}">
            <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Logo Rumbero Extremo">
        </a>
    </div>
    <nav class="sidebar-nav"> {{-- Envuelto en <nav> con clase 'sidebar-nav' --}}
        <ul>
            @if(Auth::check()) {{-- Asegúrate de que haya un usuario logueado --}}
                {{-- Dashboard (visible para admin y aliado, o para todos si es un dashboard general) --}}
                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                    <li>
                        {{-- Usa request()->routeIs() para una activación precisa de la ruta --}}
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                        </a>
                    </li>
                @endif

                {{-- Perfil (visible para todos los roles) --}}
                <li>
                    <a href="{{ route('profile') }}" class="{{ request()->routeIs('profile') ? 'active' : '' }}">
                        <i class="fas fa-user-circle"></i> <span>Perfil</span>
                    </a>
                </li>

                {{-- Usuarios (solo para Admin) --}}
                @if(Auth::user()->role === 'admin')
                    <li>
                        {{-- Ajusta la ruta si es diferente, e.g., 'users' si no es un recurso --}}
                        <a href="{{ route('users') }}" class="{{ request()->routeIs('users.index') || request()->routeIs('users.create') || request()->routeIs('users.edit') || request()->routeIs('users.show') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> <span>Usuarios</span>
                        </a>
                    </li>
                @endif

                {{-- Aliados (solo para Admin) --}}
                @if(Auth::user()->role === 'admin')
                    <li>
                        <a href="{{ route('aliados.index') }}" class="{{ request()->routeIs('allies.*') ? 'active' : '' }}">
                            <i class="fas fa-handshake"></i> <span>Aliados</span>
                        </a>
                    </li>
                @endif

                {{-- Vistas de Gestión (solo para Admin) --}}
                @if(Auth::user()->role === 'admin')
                    <div class="sidebar-divider"></div> {{-- Divisor para secciones --}}
                    <li class="sidebar-heading">Gestión de Contenido</li>
                    <li>
                        {{-- Usando data-route-pattern para la lógica JS, o puedes usar request()->routeIs('admin.banners.*') directamente aquí --}}
                        <a href="{{ route('admin.banners.index') }}" class="{{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                            <i class="fas fa-images"></i> <span>Banners</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.commercial-allies.index') }}" class="{{ request()->routeIs('admin.commercial-allies.*') ? 'active' : '' }}">
                            <i class="fas fa-store"></i> <span>Aliados Comerciales</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.promotions.index') }}" class="{{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i> <span>Promociones</span>
                        </a>
                    </li>
                    <div class="sidebar-divider"></div> {{-- Otro divisor --}}
                @endif

                {{-- Reportes (para Admin y Aliado) --}}
                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                    <li>
                        {{-- Ajusta la ruta si es diferente --}}
                        <a href="{{ route('reports.sales') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice-dollar"></i> <span>Reportes</span>
                        </a>
                    </li>
                @endif

                {{-- Reportes (para Admin y Aliado) --}}
                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                    <li>
                        {{-- Ajusta la ruta si es diferente --}}
                        <a href="{{ route('Admin.payouts.pending') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice-dollar"></i> <span>Pago A Aliados</span>
                        </a>
                    </li>
                @endif

                {{-- Configuración (solo para Admin) --}}
                @if(Auth::user()->role === 'admin')
                    <li>
                        {{-- Ajusta la ruta si es diferente --}}
                        <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i> <span>Configuración</span>
                        </a>
                    </li>
                @endif

                {{-- Cerrar Sesión (visible para todos los roles logueados) --}}
                <li>
                    <form method="POST" action="{{ route('logout') }}" style="margin: 0; padding: 0;">
                        @csrf
                        {{-- Usa preventDefault para evitar la navegación directa y permitir el envío del formulario --}}
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                        </a>
                    </form>
                </li>
            @endif {{-- Fin de Auth::check() --}}
        </ul>
    </nav>
</aside>