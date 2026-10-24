{{-- Barra superior del panel: breadcrumb, búsqueda global, notificaciones y usuario --}}
<header class="admin-toolbar">

    {{-- ==================================================================
         Izquierda: toggle + título de la página
         ================================================================== --}}
    <div class="toolbar-left">
        <button type="button" class="toolbar-toggle" data-rx-toggle="sidebar" aria-controls="adminSidebar"
            aria-expanded="false" aria-label="Mostrar u ocultar el menú">
            <i class="fa-solid fa-bars-staggered" aria-hidden="true"></i>
        </button>

        <div class="page-info">
            <nav class="page-breadcrumb" aria-label="Ubicación">
                <span class="breadcrumb-home" aria-hidden="true">
                    <i class="fa-solid fa-house-chimney"></i>
                </span>
                <span class="breadcrumb-separator" aria-hidden="true">
                    <i class="fa-solid fa-chevron-right"></i>
                </span>
                <span class="breadcrumb-current">@yield('page_title_toolbar', 'Panel')</span>
            </nav>
            <p class="page-title">@yield('page_title_toolbar', 'Panel Administrativo')</p>
        </div>
    </div>

    {{-- ==================================================================
         Derecha: búsqueda, notificaciones y perfil
         ================================================================== --}}
    <div class="toolbar-right">

        {{-- Búsqueda global (en móvil se despliega bajo la barra) --}}
        <div class="search-modern">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon" aria-hidden="true"></i>
                <label for="globalSearchInput" class="rx-sr-only">Buscar en el panel</label>
                <input type="search" id="globalSearchInput" class="search-input" placeholder="Buscar en el panel⬦"
                    autocomplete="off" enterkeyhint="search" spellcheck="false">

                <div class="search-shortcut" aria-hidden="true">
                    <span>&#8984;</span><span>K</span>
                </div>

                <div class="search-dropdown" id="searchDropdown" role="menu" aria-label="Sugerencias de búsqueda">
                    <div class="search-suggestions">
                        <div class="search-suggestion-group">
                            <div class="group-title">
                                <i class="fas fa-compass" aria-hidden="true"></i>
                                <span>Ir a</span>
                            </div>
                            <a href="{{ route('dashboard') }}" class="suggestion-item">
                                <i class="fas fa-chart-pie" aria-hidden="true"></i>
                                <span>Dashboard</span>
                            </a>
                            @if (auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->user_type === 'admin'))
                                <a href="{{ route('admin.users.index') }}" class="suggestion-item">
                                    <i class="fas fa-users" aria-hidden="true"></i>
                                    <span>Usuarios</span>
                                </a>
                                <a href="{{ route('admin.aliados.index') }}" class="suggestion-item">
                                    <i class="fas fa-handshake" aria-hidden="true"></i>
                                    <span>Aliados</span>
                                </a>
                                <a href="{{ route('admin.banners.index') }}" class="suggestion-item">
                                    <i class="fas fa-images" aria-hidden="true"></i>
                                    <span>Banners</span>
                                </a>
                                <a href="{{ route('admin.payouts.index') }}" class="suggestion-item">
                                    <i class="fas fa-money-bill-transfer" aria-hidden="true"></i>
                                    <span>Pago a Proveedores</span>
                                </a>
                                <a href="{{ route('admin.settings') }}" class="suggestion-item">
                                    <i class="fas fa-gear" aria-hidden="true"></i>
                                    <span>Configuración</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botón que despliega la búsqueda en móvil --}}
        <button type="button" class="toolbar-search-toggle" data-rx-search-toggle aria-expanded="false"
            aria-controls="globalSearchInput" aria-label="Buscar">
            <i class="fas fa-search" aria-hidden="true"></i>
        </button>

        {{-- Notificaciones (datos reales de activity_logs + notifications) --}}
        <div class="notifications-modern"
            data-rx-notifications
            data-feed-url="{{ route('admin.notifications.feed') }}"
            data-read-url="{{ route('admin.notifications.read', ['id' => '__ID__']) }}"
            data-read-all-url="{{ route('admin.notifications.read-all') }}">
            <button type="button" class="notifications-btn" data-rx-dropdown-toggle="notifications"
                aria-expanded="false" aria-controls="notificationsDropdown" aria-label="Notificaciones">
                <i class="far fa-bell" aria-hidden="true"></i>
                <span class="notification-badge" data-rx-notification-badge
                    @if ($toolbarUnread === 0) hidden @endif>{{ $toolbarUnread }}</span>
            </button>

            <div class="notifications-dropdown" id="notificationsDropdown" data-rx-dropdown="notifications">
                <div class="dropdown-header">
                    <h4>Notificaciones</h4>
                    <a href="{{ route('admin.notifications.index') }}">Ver todas</a>
                </div>

                <div class="notifications-list" data-rx-notification-list>
                    @forelse ($toolbarNotifications as $notification)
                        @php $data = $notification->data; @endphp
                        <a href="{{ $data['url'] ?? '#' }}"
                            class="notification-item {{ $notification->read_at ? '' : 'unread' }}"
                            data-rx-notification-id="{{ $notification->id }}">
                            <span class="notification-icon {{ $data['color'] ?? 'info' }}">
                                <i class="{{ $data['icon'] ?? 'fa-solid fa-bell' }}" aria-hidden="true"></i>
                            </span>
                            <div class="notification-content">
                                <p>{{ $data['title'] ?? 'Actividad' }}</p>
                                <span class="notification-message">{{ $data['message'] ?? '' }}</span>
                                <span class="notification-time">
                                    <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                    {{ $notification->created_at?->diffForHumans() }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="notification-empty" data-rx-notification-empty>
                            <i class="fa-regular fa-bell-slash" aria-hidden="true"></i>
                            <p>Sin actividad todavía</p>
                            <small>Aquí verás cada usuario, aliado, promoción o banner que se cree, edite o elimine.</small>
                        </div>
                    @endforelse
                </div>

                <div class="dropdown-footer">
                    <button type="button" class="view-all-link view-all-link--button" data-rx-notification-read-all>
                        <i class="fa-solid fa-check-double" aria-hidden="true"></i>
                        <span>Marcar todas como leídas</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Perfil de usuario --}}
        @auth
            <div class="user-profile-modern">
                <button type="button" class="user-trigger" data-rx-dropdown-toggle="user" aria-expanded="false"
                    aria-controls="userDropdown" aria-label="Menú de usuario">
                    <span class="user-avatar">
                        @if (auth()->user()->profile_photo_path)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}"
                                alt="{{ auth()->user()->name }}" width="34" height="34" loading="lazy">
                        @else
                            <span class="avatar-initials"
                                aria-hidden="true">{{ mb_substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                        @endif
                    </span>
                    <span class="user-info">
                        <span class="user-name">{{ auth()->user()->name ?? 'Administrador' }}</span>
                        <span class="user-role">{{ auth()->user()->role === 'admin' ? 'Administrador' : 'Aliado' }}</span>
                    </span>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </button>

                <div class="user-dropdown" id="userDropdown" data-rx-dropdown="user">
                    <div class="dropdown-header">
                        <div class="user-avatar-large">
                            @if (auth()->user()->profile_photo_path)
                                <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}"
                                    alt="{{ auth()->user()->name }}" width="46" height="46" loading="lazy">
                            @else
                                <span aria-hidden="true">{{ mb_substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="user-details">
                            <h4>{{ auth()->user()->name ?? 'Administrador' }}</h4>
                            <p>{{ auth()->user()->email ?? 'admin@rumbero.com' }}</p>
                        </div>
                    </div>

                    <div class="dropdown-divider"></div>

                    <a href="{{ route('profile') }}" class="dropdown-item">
                        <i class="fas fa-user-circle" aria-hidden="true"></i>
                        <span>Mi Perfil</span>
                    </a>

                    @if (auth()->user()->role === 'admin' || auth()->user()->user_type === 'admin')
                        <a href="{{ route('admin.settings') }}" class="dropdown-item">
                            <i class="fas fa-sliders-h" aria-hidden="true"></i>
                            <span>Configuración</span>
                        </a>
                    @endif

                    {{-- Interruptor rápido de modo oscuro (preferencia por usuario) --}}
                    <div class="dropdown-item dropdown-item--switch">
                        <i class="fas fa-moon" aria-hidden="true"></i>
                        <span>Modo oscuro</span>
                        <label class="toolbar-switch" aria-label="Activar modo oscuro">
                            <input type="checkbox" data-rx-dark-toggle
                                data-url="{{ route('admin.settings.update-dark-mode') }}"
                                {{ ($darkMode ?? false) ? 'checked' : '' }}>
                            <span class="toolbar-switch-slider"></span>
                        </label>
                    </div>

                    <div class="dropdown-divider"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item logout-item">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                            <span>Cerrar Sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</header>
