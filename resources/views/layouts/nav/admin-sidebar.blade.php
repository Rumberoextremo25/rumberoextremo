<button class="menu-toggle">
    <i class="fa-solid fa-bars"></i>
</button>

<aside class="admin-sidebar">
    {{-- Navegación del Sidebar --}}
    <nav class="sidebar-nav">
        <ul>
            {{-- Sección de Navegación --}}
            @if (Auth::check())
                {{-- Dashboard --}}
                @if (Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('dashboard') }}"
                            class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i> <span class="sidebar-link-text">Dashboard</span>
                        </a>
                    </li>
                @endif
                {{-- Perfil --}}
                <li class="sidebar-nav-item">
                    <a href="{{ route('profile') }}"
                        class="sidebar-nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">
                        <i class="fa-solid fa-user"></i> <span class="sidebar-link-text">Perfil</span>
                    </a>
                </li>
                {{-- Usuarios --}}
                @if (Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('users') }}"
                            class="sidebar-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users"></i> <span class="sidebar-link-text">Usuarios</span>
                        </a>
                    </li>
                @endif
                {{-- Aliados --}}
                @if (Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('aliados.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('aliados.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-gem"></i> <span class="sidebar-link-text">Aliados</span>
                        </a>
                    </li>
                @endif
                {{-- Reportes --}}
                @if (Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('reports.sales') }}"
                            class="sidebar-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-file-invoice"></i> <span class="sidebar-link-text">Reportes</span>
                        </a>
                    </li>
                @endif
                {{-- Configuración --}}
                @if (Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.settings') }}"
                            class="sidebar-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-gear"></i> <span class="sidebar-link-text">Configuración</span>
                        </a>
                    </li>
                @endif
            @endif
        </ul>
        {{-- Sección de Cerrar Sesión --}}
        <div class="logout-container">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                    class="sidebar-logout-link">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> <span class="sidebar-link-text">Cerrar
                        Sesión</span>
                </a>
            </form>
        </div>
    </nav>
</aside>

{{-- El script para el menú móvil va aquí --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.admin-sidebar');

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    });
</script>
