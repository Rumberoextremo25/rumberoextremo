{{-- resources/views/layouts/partials/admin-sidebar.blade.php --}}

<!-- El `aside` principal con el nuevo diseño -->
<aside class="admin-sidebar">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <div class="sidebar-header">
        <a href="{{ url('/') }}" class="logo-link">
            <!-- Asegúrate de que esta ruta a la imagen sea correcta -->
            <img src="{{ asset('assets/img/IMG_4253.png') }}" alt="Logo Rumbero Extremo" class="logo-img">
        </a>
    </div>

    <!-- Contenedor del perfil del usuario, para un diseño más completo -->
    @if(Auth::check())
    <div class="sidebar-profile">
        <div class="profile-avatar">
            <!-- Usa una imagen de perfil o un icono de usuario -->
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="profile-info">
            <span class="profile-name">{{ Auth::user()->name }}</span>
            <span class="profile-role">{{ Str::ucfirst(Auth::user()->role) }}</span>
        </div>
    </div>
    @endif

    <!-- Navegación principal -->
    <nav class="sidebar-nav">
        <ul>
            @if(Auth::check())
                {{-- Dashboard (visible para admin y aliado) --}}
                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active-link' : '' }}">
                            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                        </a>
                    </li>
                @endif

                {{-- Perfil (visible para todos los roles) --}}
                <li class="sidebar-nav-item">
                    <a href="{{ route('profile') }}" class="sidebar-nav-link {{ request()->routeIs('profile') ? 'active-link' : '' }}">
                        <i class="fas fa-user-circle"></i> <span>Perfil</span>
                    </a>
                </li>

                {{-- Usuarios (solo para Admin) --}}
                @if(Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('users') }}" class="sidebar-nav-link {{ request()->routeIs('users.*') ? 'active-link' : '' }}">
                            <i class="fas fa-users"></i> <span>Usuarios</span>
                        </a>
                    </li>
                @endif

                {{-- Aliados (solo para Admin) --}}
                @if(Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('aliados.index') }}" class="sidebar-nav-link {{ request()->routeIs('allies.*') ? 'active-link' : '' }}">
                            <i class="fas fa-handshake"></i> <span>Aliados</span>
                        </a>
                    </li>
                @endif
                
                {{-- Vistas de Gestión (solo para Admin) --}}
                @if(Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="#" class="sidebar-nav-link {{ request()->routeIs('products.*') ? 'active-link' : '' }}">
                            <i class="fas fa-box-open"></i> <span>Productos</span>
                        </a>
                    </li>
                    <li class="sidebar-heading">Gestión de Contenido</li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.banners.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.banners.*') ? 'active-link' : '' }}">
                            <i class="fas fa-images"></i> <span>Banners</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.commercial-allies.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.commercial-allies.*') ? 'active-link' : '' }}">
                            <i class="fas fa-store"></i> <span>Aliados Comerciales</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.promotions.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.promotions.*') ? 'active-link' : '' }}">
                            <i class="fas fa-tags"></i> <span>Promociones</span>
                        </a>
                    </li>
                @endif

                {{-- Reportes (para Admin y Aliado) --}}
                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('reports.sales') }}" class="sidebar-nav-link {{ request()->routeIs('reports.*') ? 'active-link' : '' }}">
                            <i class="fas fa-file-invoice-dollar"></i> <span>Reportes</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('Admin.payouts.pending') }}" class="sidebar-nav-link {{ request()->routeIs('Admin.payouts.*') ? 'active-link' : '' }}">
                            <i class="fas fa-money-bill-wave"></i> <span>Pago a Aliados</span>
                        </a>
                    </li>
                @endif

                {{-- Configuración (solo para Admin) --}}
                @if(Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.settings') }}" class="sidebar-nav-link {{ request()->routeIs('settings.*') ? 'active-link' : '' }}">
                            <i class="fas fa-cog"></i> <span>Configuración</span>
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </nav>

    <!-- Enlace de cerrar sesión separado en la parte inferior -->
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="sidebar-logout-link">
                <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
            </a>
        </form>
    </div>
</aside>