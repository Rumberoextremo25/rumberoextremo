@php
    $user = \Illuminate\Support\Facades\Auth::user();
    $isAdmin = $user && ($user->role === 'admin' || $user->user_type === 'admin');
    $isAlly = $user && $user->role === 'aliado';
    $contentOpen = request()->routeIs('admin.banners.*') || request()->routeIs('admin.commercial-allies.*') || request()->routeIs('admin.promotions.*') || request()->routeIs('admin.demo-videos.*') || request()->routeIs('admin.home-slides.*');
    $auditEmails = $isAdmin ? app(\App\Services\SystemAuditService::class)->allowedEmails() : [];
    $canViewAudit = $isAdmin && ($auditEmails === [] || in_array(strtolower((string) $user->email), $auditEmails, true));
@endphp

{{-- Capa oscura del drawer (móvil / tablet) --}}
<div class="sidebar-overlay" aria-hidden="true"></div>

<aside id="adminSidebar" class="admin-sidebar" aria-label="Navegación del panel">

    {{-- ==================================================================
         Cabecera: logo + cerrar (móvil)
         ================================================================== --}}
    <div class="sidebar-header">
        <a href="{{ route('welcome') }}" class="sidebar-logo-link" aria-label="Rumbero Extremo — Ir al sitio">
            <img src="{{ asset('assets/img/logo_sidebar.png') }}" alt="Rumbero Extremo" class="logo-image sidebar-logo"
                width="174" height="34" decoding="async">
        </a>

        <button type="button" class="sidebar-close" data-rx-toggle="sidebar" aria-label="Cerrar menú"
            aria-controls="adminSidebar">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    {{-- ==================================================================
         Navegación
         ================================================================== --}}
    <nav class="sidebar-nav">
        <ul>

            @auth
                {{-- ------------------------------------------------------------
                     GENERAL
                     ------------------------------------------------------------ --}}
                <li class="sidebar-section-title"><span>General</span></li>

                @if ($isAdmin || $isAlly)
                    <li class="sidebar-nav-item">
                        <a href="{{ route('dashboard') }}"
                            class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            data-tooltip="Dashboard" @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                            <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                            <span class="sidebar-link-text">Dashboard</span>
                        </a>
                    </li>
                @endif

                <li class="sidebar-nav-item">
                    <a href="{{ route('profile') }}"
                        class="sidebar-nav-link {{ request()->routeIs('profile*') ? 'active' : '' }}"
                        data-tooltip="Perfil" @if (request()->routeIs('profile*')) aria-current="page" @endif>
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                        <span class="sidebar-link-text">Perfil</span>
                    </a>
                </li>

                {{-- ------------------------------------------------------------
                     OPERACIÓN
                     ------------------------------------------------------------ --}}
                @if ($isAdmin || $isAlly)
                    <li class="sidebar-section-title"><span>Operación</span></li>
                @endif

                @if ($isAdmin || $isAlly)
                    <li class="sidebar-nav-item">
                        <a href="{{ route('transacciones.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('transacciones.*') ? 'active' : '' }}"
                            data-tooltip="Transacciones" @if (request()->routeIs('transacciones.*')) aria-current="page" @endif>
                            <i class="fa-solid fa-right-left" aria-hidden="true"></i>
                            <span class="sidebar-link-text">Mis Transacciones</span>
                        </a>
                    </li>
                @endif

                @if ($isAdmin)
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.users.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                            data-tooltip="Usuarios" @if (request()->routeIs('admin.users.*')) aria-current="page" @endif>
                            <i class="fa-solid fa-users" aria-hidden="true"></i>
                            <span class="sidebar-link-text">Usuarios</span>
                        </a>
                    </li>

                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.aliados.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.aliados.*') ? 'active' : '' }}"
                            data-tooltip="Aliados" @if (request()->routeIs('admin.aliados.*')) aria-current="page" @endif>
                            <i class="fa-solid fa-handshake" aria-hidden="true"></i>
                            <span class="sidebar-link-text">Aliados</span>
                        </a>
                    </li>
                @endif

                @if ($isAdmin || $isAlly)
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.reports.sales') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                            data-tooltip="Reportes" @if (request()->routeIs('admin.reports.*')) aria-current="page" @endif>
                            <i class="fa-solid fa-file-invoice" aria-hidden="true"></i>
                            <span class="sidebar-link-text">Reportes</span>
                        </a>
                    </li>
                @endif

                @if ($isAdmin)
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.requests.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}"
                            data-tooltip="Solicitudes" @if (request()->routeIs('admin.requests.*')) aria-current="page" @endif>
                            <i class="fa-solid fa-file-signature" aria-hidden="true"></i>
                            <span class="sidebar-link-text">Solicitudes</span>
                        </a>
                    </li>
                @endif

                @if ($isAdmin)
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.payouts.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.payouts.*') ? 'active' : '' }}"
                            data-tooltip="Pago a Proveedores" @if (request()->routeIs('admin.payouts.*')) aria-current="page" @endif>
                            <i class="fa-solid fa-money-bill-transfer" aria-hidden="true"></i>
                            <span class="sidebar-link-text">Pago a Proveedores</span>
                        </a>
                    </li>
                @endif

                {{-- ------------------------------------------------------------
                     HERRAMIENTAS (solo admin)
                     ------------------------------------------------------------ --}}
                @if ($isAdmin)
                    <li class="sidebar-section-title"><span>Herramientas</span></li>

                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.chat') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.chat') ? 'active' : '' }}"
                            data-tooltip="Chat de Soporte" @if (request()->routeIs('admin.chat')) aria-current="page" @endif>
                            <i class="fa-solid fa-headset" aria-hidden="true"></i>
                            <span class="sidebar-link-text">Chat de Soporte</span>
                        </a>
                    </li>

                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.sales.stats') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.sales.stats') ? 'active' : '' }}"
                            data-tooltip="Estadísticas de Ventas" @if (request()->routeIs('admin.sales.stats')) aria-current="page" @endif>
                            <i class="fa-solid fa-chart-simple" aria-hidden="true"></i>
                            <span class="sidebar-link-text">Estadísticas de Ventas</span>
                        </a>
                    </li>

                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.qr.index') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.qr.*') ? 'active' : '' }}"
                            data-tooltip="Generar QR" @if (request()->routeIs('admin.qr.*')) aria-current="page" @endif>
                            <i class="fa-solid fa-qrcode" aria-hidden="true"></i>
                            <span class="sidebar-link-text">Generar QR</span>
                        </a>
                    </li>

                    {{-- --------------------------------------------------------
                         CONTENIDO (submenú plegable)
                         -------------------------------------------------------- --}}
                    <li class="sidebar-section-title"><span>Contenido</span></li>

                    <li class="sidebar-nav-item sidebar-parent-item {{ $contentOpen ? 'is-open' : '' }}">
                        <button type="button" class="sidebar-nav-header" data-tooltip="Gestión de Contenido"
                            aria-controls="sidebarSubmenuContenido" aria-expanded="{{ $contentOpen ? 'true' : 'false' }}">
                            <i class="fa-solid fa-newspaper" aria-hidden="true"></i>
                            <span class="sidebar-link-text">Gestión de Contenido</span>
                            <i class="fa-solid fa-chevron-down sidebar-caret" aria-hidden="true"></i>
                        </button>

                        <ul class="sidebar-nav-submenu" id="sidebarSubmenuContenido">
                            <li class="sidebar-nav-subitem">
                                <a href="{{ route('admin.banners.index') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}"
                                    @if (request()->routeIs('admin.banners.*')) aria-current="page" @endif>
                                    <i class="fa-solid fa-images" aria-hidden="true"></i>
                                    <span class="sidebar-link-text">Banners</span>
                                </a>
                            </li>
                            <li class="sidebar-nav-subitem">
                                <a href="{{ route('admin.commercial-allies.index') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('admin.commercial-allies.*') ? 'active' : '' }}"
                                    @if (request()->routeIs('admin.commercial-allies.*')) aria-current="page" @endif>
                                    <i class="fa-solid fa-store" aria-hidden="true"></i>
                                    <span class="sidebar-link-text">Aliados Comerciales</span>
                                </a>
                            </li>
                            <li class="sidebar-nav-subitem">
                                <a href="{{ route('admin.promotions.index') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}"
                                    @if (request()->routeIs('admin.promotions.*')) aria-current="page" @endif>
                                    <i class="fa-solid fa-tags" aria-hidden="true"></i>
                                    <span class="sidebar-link-text">Promociones</span>
                                </a>
                            </li>
                            <li class="sidebar-nav-subitem">
                                <a href="{{ route('admin.demo-videos.index') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('admin.demo-videos.*') ? 'active' : '' }}"
                                    @if (request()->routeIs('admin.demo-videos.*')) aria-current="page" @endif>
                                    <i class="fa-solid fa-clapperboard" aria-hidden="true"></i>
                                    <span class="sidebar-link-text">Videos de demo</span>
                                </a>
                            </li>
                            <li class="sidebar-nav-subitem">
                                <a href="{{ route('admin.home-slides.index') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('admin.home-slides.*') ? 'active' : '' }}"
                                    @if (request()->routeIs('admin.home-slides.*')) aria-current="page" @endif>
                                    <i class="fa-solid fa-images" aria-hidden="true"></i>
                                    <span class="sidebar-link-text">Carrusel del Home</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- --------------------------------------------------------
                         SISTEMA
                         -------------------------------------------------------- --}}
                    <li class="sidebar-section-title"><span>Sistema</span></li>

                    @if ($canViewAudit)
                        <li class="sidebar-nav-item">
                            <a href="{{ route('admin.system-audit.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.system-audit.*') ? 'active' : '' }}"
                                data-tooltip="Auditoría del sistema" @if (request()->routeIs('admin.system-audit.*')) aria-current="page" @endif>
                                <i class="fa-solid fa-heart-pulse" aria-hidden="true"></i>
                                <span class="sidebar-link-text">Auditoría del sistema</span>
                            </a>
                        </li>
                    @endif

                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.settings') }}"
                            class="sidebar-nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}"
                            data-tooltip="Configuración" @if (request()->routeIs('admin.settings')) aria-current="page" @endif>
                            <i class="fa-solid fa-gear" aria-hidden="true"></i>
                            <span class="sidebar-link-text">Configuración</span>
                        </a>
                    </li>
                @endif
            @endauth
        </ul>

        {{-- ==================================================================
             Cerrar sesión
             ================================================================== --}}
        <div class="logout-container">
            <form method="POST" action="{{ route('logout') }}" data-no-guard>
                @csrf
                <button type="submit" class="sidebar-logout-link" data-tooltip="Cerrar Sesión">
                    <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                    <span class="sidebar-link-text">Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </nav>
</aside>
