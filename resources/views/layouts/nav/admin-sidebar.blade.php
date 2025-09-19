<button class="menu-toggle">
    <i class="fa-solid fa-bars"></i>
</button>

<!-- Overlay para cerrar el menú -->
<div class="sidebar-overlay"></div>

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
                            <i class="fa-solid fa-handshake"></i><span class="sidebar-link-text">Aliados</span>
                        </a>
                    </li>
                @endif

                {{-- Contenedor de Gestión de Contenido --}}
                @if (Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item sidebar-parent-item">
                        <ul class="sidebar-nav-submenu">
                            <li class="sidebar-nav-subitem">
                                <a href="{{ route('admin.banners.index') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('banners.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-images"></i> Banners
                                </a>
                            </li>
                            <li class="sidebar-nav-subitem">
                                <a href="{{ route('admin.commercial-allies.index') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('aliados-comerciales.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-store"></i> Aliados Comerciales
                                </a>
                            </li>
                            <li class="sidebar-nav-subitem">
                                <a href="{{ route('admin.promotions.index') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('promociones.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-tags"></i> Promociones
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Reportes --}}
                @if (Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.reports.sales') }}"
                            class="sidebar-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-file-invoice"></i> <span class="sidebar-link-text">Reportes</span>
                        </a>
                    </li>
                @endif

                {{-- Payout --}}
                @if (Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.payouts.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('payouts.*') ? 'active' : '' }}">
                            <img src="{{ asset('assets/img/dashboard/pago_proveedores.png') }}"
                                alt="Pago a Proveedores" class="sidebar-icon">
                            <span class="sidebar-link-text">Pago a Proveedores</span>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const body = document.body;

        function toggleMenu() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            body.classList.toggle('no-scroll');
            menuToggle.classList.toggle('active');
        }

        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleMenu();
        });

        overlay.addEventListener('click', toggleMenu);

        // Cerrar menú al hacer clic en un link
        const sidebarLinks = document.querySelectorAll('.sidebar-nav-link, .sidebar-logout-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    toggleMenu();
                }
            });
        });

        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                toggleMenu();
            }
        });
    });
</script>
