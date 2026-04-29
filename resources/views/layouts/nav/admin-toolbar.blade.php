{{-- Admin Toolbar Moderno --}}
<header class="admin-toolbar">
    <div class="toolbar-left">
        <button class="toolbar-toggle" id="toolbarSidebarToggle">
            <i class="fas fa-bars-staggered"></i>
        </button>
        <div class="page-info">
            <div class="page-breadcrumb">
                <span class="breadcrumb-home">
                    <i class="fas fa-house-chimney"></i>
                </span>
                <span class="breadcrumb-separator">
                    <i class="fas fa-chevron-right"></i>
                </span>
                <span class="breadcrumb-current">
                    @yield('page_title_toolbar', 'Dashboard')
                </span>
            </div>
            <h1 class="page-title">
                @yield('page_title_toolbar', 'Panel Administrativo')
            </h1>
        </div>
    </div>

    <div class="toolbar-right">
        <!-- Búsqueda Moderna -->
        <div class="search-modern">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" 
                       id="globalSearchInput" 
                       class="search-input" 
                       placeholder="Buscar en el panel..."
                       autocomplete="off">
                <div class="search-shortcut">
                    <span>⌘</span>
                    <span>K</span>
                </div>
                <div class="search-dropdown" id="searchDropdown">
                    <div class="search-suggestions">
                        <div class="search-suggestion-group">
                            <div class="group-title">
                                <i class="fas fa-chart-line"></i>
                                <span>Secciones</span>
                            </div>
                            <a href="{{ route('dashboard') }}" class="suggestion-item">
                                <i class="fas fa-chart-pie"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="suggestion-item">
                                <i class="fas fa-users"></i>
                                <span>Usuarios</span>
                            </a>
                            <a href="{{ route('admin.aliados.index') }}" class="suggestion-item">
                                <i class="fas fa-handshake"></i>
                                <span>Aliados</span>
                            </a>
                            <a href="{{ route('admin.banners.index') }}" class="suggestion-item">
                                <i class="fas fa-images"></i>
                                <span>Banners</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notificaciones -->
        <div class="notifications-modern">
            <button class="notifications-btn" id="notificationsBtn">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
            </button>
            <div class="notifications-dropdown" id="notificationsDropdown">
                <div class="dropdown-header">
                    <h4>Notificaciones</h4>
                    <a href="#">Ver todas</a>
                </div>
                <div class="notifications-list">
                    <div class="notification-item unread">
                        <div class="notification-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="notification-content">
                            <p>Pago pendiente de aprobación</p>
                            <span class="notification-time">Hace 5 min</span>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="notification-content">
                            <p>Venta completada #1245</p>
                            <span class="notification-time">Hace 1 hora</span>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon info">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="notification-content">
                            <p>Nuevo aliado registrado</p>
                            <span class="notification-time">Hace 3 horas</span>
                        </div>
                    </div>
                </div>
                <div class="dropdown-footer">
                    <a href="#" class="view-all-link">
                        <span>Ver todas las notificaciones</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Perfil de Usuario -->
        <div class="user-profile-modern">
            <button class="user-trigger" id="userTrigger">
                <div class="user-avatar">
                    @if(Auth::user()->profile_photo_path)
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Avatar">
                    @else
                        <span class="avatar-initials">{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</span>
                    @endif
                </div>
                <div class="user-info">
                    <span class="user-name">{{ Auth::user()->name ?? 'Administrador' }}</span>
                    <span class="user-role">{{ Auth::user()->role === 'admin' ? 'Administrador' : 'Aliado' }}</span>
                </div>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="user-dropdown" id="userDropdown">
                <div class="dropdown-header">
                    <div class="user-avatar-large">
                        @if(Auth::user()->profile_photo_path)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Avatar">
                        @else
                            <span>{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="user-details">
                        <h4>{{ Auth::user()->name ?? 'Administrador' }}</h4>
                        <p>{{ Auth::user()->email ?? 'admin@rumbero.com' }}</p>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="{{ route('profile') }}" class="dropdown-item">
                    <i class="fas fa-user-circle"></i>
                    <span>Mi Perfil</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="dropdown-item">
                    <i class="fas fa-sliders-h"></i>
                    <span>Configuración</span>
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit" class="dropdown-item logout-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const sidebarToggle = document.getElementById('toolbarSidebarToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
        
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }
    }
    
    // Búsqueda
    const searchInput = document.getElementById('globalSearchInput');
    const searchDropdown = document.getElementById('searchDropdown');
    
    if (searchInput && searchDropdown) {
        searchInput.addEventListener('focus', function() {
            searchDropdown.classList.add('show');
        });
        
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.classList.remove('show');
            }
        });
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchDropdown.classList.remove('show');
                searchInput.blur();
            }
        });
    }
    
    // Notificaciones
    const notificationsBtn = document.getElementById('notificationsBtn');
    const notificationsDropdown = document.getElementById('notificationsDropdown');
    
    if (notificationsBtn && notificationsDropdown) {
        notificationsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationsDropdown.classList.toggle('show');
        });
        
        document.addEventListener('click', function() {
            notificationsDropdown.classList.remove('show');
        });
    }
    
    // Perfil de usuario
    const userTrigger = document.getElementById('userTrigger');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userTrigger && userDropdown) {
        userTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        
        document.addEventListener('click', function() {
            userDropdown.classList.remove('show');
        });
    }
    
    // Ctrl + K para búsqueda
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchInput?.focus();
        }
    });
});
</script>