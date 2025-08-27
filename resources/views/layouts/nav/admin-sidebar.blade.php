{{-- resources/views/layouts/partials/admin-sidebar.blade.php --}}

<aside class="admin-sidebar">

    {{-- Contenedor del logo. Ahora está DENTRO del aside. --}}
    <div class="logo-container">
        <a href="{{ url('/') }}" class="logo-link">
            <img src="{{ asset('assets/img/IMG_4254.png') }}" alt="Logo Rumbero Extremo" class="logo-img">
        </a>
    </div>
    <nav class="sidebar-nav">
        <ul>
            @if (Auth::check())
                {{-- Dashboard (visible para admin y aliado) --}}
                @if (Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('dashboard') }}"
                            class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active-link' : '' }}">
                            <i class="fas fa-tachometer-alt"></i> <span class="sidebar-link-text">Dashboard</span>
                        </a>
                    </li>
                @endif
                {{-- Perfil (visible para todos los roles) --}}
                <li class="sidebar-nav-item">
                    <a href="{{ route('profile') }}"
                        class="sidebar-nav-link {{ request()->routeIs('profile') ? 'active-link' : '' }}">
                        <i class="fas fa-user-circle"></i> <span class="sidebar-link-text">Perfil</span>
                    </a>
                </li>
                {{-- Usuarios (solo para Admin) --}}
                @if (Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('users') }}"
                            class="sidebar-nav-link {{ request()->routeIs('users.*') ? 'active-link' : '' }}">
                            <i class="fas fa-users"></i> <span class="sidebar-link-text">Usuarios</span>
                        </a>
                    </li>
                @endif
                {{-- Aliados (solo para Admin) --}}
                @if (Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('aliados.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('allies.*') ? 'active-link' : '' }}">
                            <i class="fas fa-handshake"></i> <span class="sidebar-link-text">Aliados</span>
                        </a>
                    </li>
                @endif
                {{-- Vistas de Gestión (solo para Admin) --}}
                @if (Auth::user()->role === 'admin')
                    <h6 class="sidebar-heading">Gestión de Contenido</h6>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.banners.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.banners.*') ? 'active-link' : '' }}">
                            <i class="fas fa-images"></i> <span class="sidebar-link-text">Banners</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.commercial-allies.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.commercial-allies.*') ? 'active-link' : '' }}">
                            <i class="fas fa-store"></i> <span class="sidebar-link-text">Aliados Comerciales</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.promotions.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.promotions.*') ? 'active-link' : '' }}">
                            <i class="fas fa-tags"></i> <span class="sidebar-link-text">Promociones</span>
                        </a>
                    </li>
                @endif
                {{-- Reportes (para Admin y Aliado) --}}
                @if (Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('reports.sales') }}"
                            class="sidebar-nav-link {{ request()->routeIs('reports.*') ? 'active-link' : '' }}">
                            <i class="fas fa-file-invoice-dollar"></i> <span class="sidebar-link-text">Reportes</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('Admin.payouts.pending') }}"
                            class="sidebar-nav-link {{ request()->routeIs('Admin.payouts.*') ? 'active-link' : '' }}">
                            <i class="fas fa-money-bill-wave"></i> <span class="sidebar-link-text">Pago a Aliados</span>
                        </a>
                    </li>
                @endif
                {{-- Configuración (solo para Admin) --}}
                @if (Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.settings') }}"
                            class="sidebar-nav-link {{ request()->routeIs('settings.*') ? 'active-link' : '' }}">
                            <i class="fas fa-cog"></i> <span class="sidebar-link-text">Configuración</span>
                        </a>
                    </li>
                @endif
            @endif
        </ul>
        <div class="logout-container">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                    class="sidebar-logout-link">
                    <i class="fas fa-sign-out-alt"></i> <span class="sidebar-link-text">Cerrar Sesión</span>
                </a>
            </form>
        </div>
    </nav>

    {{-- Contenedor de cerrar sesión. También debe estar DENTRO del aside. --}}

</aside>
