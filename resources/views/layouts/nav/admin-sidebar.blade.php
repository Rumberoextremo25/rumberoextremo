<button class="menu-toggle">
    <i class="fa-solid fa-bars"></i>
</button>

<!-- Overlay para cerrar el menú -->
<div class="sidebar-overlay"></div>

<aside class="admin-sidebar">
    {{-- Navegación del Sidebar --}}
    <nav class="sidebar-nav">
        <ul>
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

                {{-- TRANSACCIONES - PARA ALIADOS Y ADMIN --}}
                @if (Auth::user()->role === 'admin' || Auth::user()->role === 'aliado')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('transacciones.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('transacciones.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-right-left"></i>  <span class="sidebar-link-text">Mis Transacciones</span>
                        </a>
                    </li>
                @endif

                {{-- Usuarios --}}
                @if (Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.users.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users"></i> <span class="sidebar-link-text">Usuarios</span>
                        </a>
                    </li>
                @endif

                {{-- Aliados --}}
                @if (Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.aliados.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.aliados.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-handshake"></i><span class="sidebar-link-text">Aliados</span>
                        </a>
                    </li>
                @endif

                {{-- Contenedor de Gestión de Contenido --}}
                @if (Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item sidebar-parent-item">
                        <div class="sidebar-nav-header">
                            <i class="fa-solid fa-newspaper"></i> Gestión de Contenido
                        </div>
                        <ul class="sidebar-nav-submenu">
                            <li class="sidebar-nav-subitem">
                                <a href="{{ route('admin.banners.index') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-images"></i> Banners
                                </a>
                            </li>
                            <li class="sidebar-nav-subitem">
                                <a href="{{ route('admin.commercial-allies.index') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('admin.commercial-allies.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-store"></i> Aliados Comerciales
                                </a>
                            </li>
                            <li class="sidebar-nav-subitem">
                                <a href="{{ route('admin.promotions.index') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
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
                            class="sidebar-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-file-invoice"></i> <span class="sidebar-link-text">Reportes</span>
                        </a>
                    </li>
                @endif

                {{-- Payout --}}
                @if (Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.payouts.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.payouts.*') ? 'active' : '' }}">
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
                            class="sidebar-nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
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
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> <span class="sidebar-link-text">Cerrar Sesión</span>
                </a>
            </form>
        </div>
    </nav>
</aside>

<style>
    /* Estilos para el menú activo */
    .sidebar-nav-link.active {
        background: linear-gradient(135deg, #A601B3 0%, #3004E1 100%);
        color: #ffffff !important;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(166, 1, 179, 0.2);
    }
    
    .sidebar-nav-link.active i {
        color: #ffffff !important;
    }
    
    .sidebar-nav-link.active:hover {
        background: linear-gradient(135deg, #3004E1 0%, #A601B3 100%);
        box-shadow: 0 6px 15px rgba(166, 1, 179, 0.3);
    }
    
    /* Estilo para el header de la sección */
    .sidebar-nav-header {
        padding: 0.8rem 1rem;
        color: #94a3b8;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .sidebar-nav-header i {
        color: #A601B3;
        font-size: 0.9rem;
    }
    
    /* Submenú */
    .sidebar-nav-submenu {
        list-style: none;
        padding-left: 0.5rem !important;
        margin-top: 0.25rem;
    }
    
    .sidebar-nav-subitem {
        margin: 0.25rem 0;
    }
    
    .sidebar-nav-subitem .sidebar-nav-link {
        padding: 0.6rem 1rem 0.6rem 2rem !important;
        font-size: 0.9rem;
    }
    
    /* Asegurar que el icono de transacciones tenga el mismo estilo que los demás */
    .sidebar-nav-link i.fa-money-bill-transfer {
        font-size: 1.2rem;
        width: 20px;
        text-align: center;
    }
    
    /* Color cuando está activo */
    .sidebar-nav-link.active i.fa-money-bill-transfer {
        color: #ffffff !important;
    }
    
    /* Efecto hover */
    .sidebar-nav-link:hover i.fa-money-bill-transfer {
        color: #ffffff;
    }
</style>

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

        if (menuToggle) {
            menuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleMenu();
            });
        }

        if (overlay) {
            overlay.addEventListener('click', toggleMenu);
        }

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
            if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
                toggleMenu();
            }
        });
        
        // Debug: Mostrar la ruta actual en consola (opcional, quitar en producción)
        console.log('Ruta actual:', window.location.pathname);
    });
</script>
