@extends('layouts.admin')

@section('page_title_toolbar', 'Gestión de Usuarios')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
@endpush

@section('content')
    <div class="users-wrapper">
        {{-- Header con bienvenida --}}
        <div class="users-header-bar">
            <div class="header-content">
                <h1 class="page-title">
                    <span class="title-main">Gestión de</span>
                    <span class="title-accent">Usuarios</span>
                </h1>
                <p class="page-subtitle">
                    <i class="fas fa-users-cog"></i>
                    Administra todos los usuarios de la plataforma
                </p>
            </div>
            <div class="header-actions">
                <div class="user-greeting">
                    <span>Bienvenido,</span>
                    <strong>{{ Auth::user()->name }}</strong>
                </div>
                <div class="avatar-circle">
                    <span>{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
            </div>
        </div>

        {{-- Mensajes de alerta --}}
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        @endif

        {{-- Tarjetas de estadísticas --}}
        <div class="stats-grid">
            <div class="stat-card" data-color="purple">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ number_format($totalUsersCount ?? $users->total()) }}</span>
                    <span class="stat-label">Total Usuarios</span>
                </div>
            </div>

            <div class="stat-card" data-color="green">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ number_format($activeUsersCount ?? $users->where('status', 'activo')->count()) }}</span>
                    <span class="stat-label">Activos</span>
                </div>
            </div>

            <div class="stat-card" data-color="orange">
                <div class="stat-icon">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ number_format($pendingUsersCount ?? $users->where('status', 'pendiente')->count()) }}</span>
                    <span class="stat-label">Pendientes</span>
                </div>
            </div>

            <div class="stat-card" data-color="red">
                <div class="stat-icon">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ number_format($inactiveUsersCount ?? $users->where('status', 'inactivo')->count()) }}</span>
                    <span class="stat-label">Inactivos</span>
                </div>
            </div>
        </div>

        {{-- Barra de acciones --}}
        <div class="actions-bar">
            <div class="actions-left">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchUsers" placeholder="Buscar usuarios...">
                </div>
                
                <div class="filter-dropdown">
                    <select id="filterRole" class="filter-select">
                        <option value="">Todos los tipos</option>
                        <option value="admin">Administradores</option>
                        <option value="aliado">Aliados</option>
                        <option value="afiliado">Afiliados</option>
                        <option value="comun">Comunes</option>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>

                <div class="filter-dropdown">
                    <select id="filterStatus" class="filter-select">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                        <option value="pendiente">Pendientes</option>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>

            <div class="actions-right">
                <a href="{{ route('admin.users.create') }}" class="btn-add">
                    <i class="fas fa-plus-circle"></i>
                    Añadir Usuario
                </a>
            </div>
        </div>

        {{-- Tabla de usuarios --}}
        @if ($users->isEmpty())
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <h3>No hay usuarios registrados</h3>
                <p>Comienza añadiendo tu primer usuario a la plataforma.</p>
                <a href="{{ route('admin.users.create') }}" class="btn-add">
                    <i class="fas fa-plus-circle"></i>
                    Añadir Usuario
                </a>
            </div>
        @else
            <div class="table-container">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Contacto</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            @foreach ($users as $user)
                                <tr class="user-row" data-role="{{ strtolower($user->role ?? $user->user_type) }}" data-status="{{ strtolower($user->status) }}">
                                    <td>
                                        <span class="user-id">#{{ $user->id }}</span>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                {{ substr($user->firstname ?? $user->name, 0, 1) }}
                                            </div>
                                            <div class="user-details">
                                                <span class="user-name">{{ $user->firstname ?? $user->name }} {{ $user->lastname ?? '' }}</span>
                                                <span class="user-email">{{ $user->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <span><i class="fas fa-phone"></i> {{ $user->phone ?? $user->phone1 ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $role = strtolower($user->role ?? $user->user_type);
                                            $roleText = match($role) {
                                                'admin' => 'Administrador',
                                                'aliado' => 'Aliado',
                                                'afiliado' => 'Afiliado',
                                                'user', 'comun' => 'Común',
                                                default => $role
                                            };
                                        @endphp
                                        <span class="badge badge-role-{{ $role }}">
                                            {{ $roleText }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $status = strtolower($user->status);
                                            $statusText = ucfirst($status);
                                        @endphp
                                        <span class="badge badge-status-{{ $status }}">
                                            <i class="fas fa-circle"></i>
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            @if($user->created_at)
                                                <span>{{ $user->created_at->format('d/m/Y') }}</span>
                                                <small>{{ $user->created_at->format('H:i') }}</small>
                                            @elseif($user->registration_date)
                                                <span>{{ \Carbon\Carbon::parse($user->registration_date)->format('d/m/Y') }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.users.show', $user->id) }}" class="action-btn view" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn edit" title="Editar usuario">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="delete-form" 
                                                  onsubmit="return confirm('¿Estás seguro de eliminar a {{ $user->firstname ?? $user->name }} {{ $user->lastname ?? '' }}? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn delete" title="Eliminar usuario">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            @if ($users->hasPages())
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        <i class="fas fa-list-ul"></i>
                        Mostrando {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} de {{ $users->total() }} usuarios
                    </div>

                    <div class="pagination">
                        @if ($users->onFirstPage())
                            <span class="page-link disabled">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                        @else
                            <a href="{{ $users->previousPageUrl() }}" class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        @endif

                        @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                            @if ($page == $users->currentPage())
                                <span class="page-link active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}" class="page-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="page-link disabled">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ========== CERRAR ALERTAS ==========
        document.querySelectorAll('.alert-close').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.alert').style.display = 'none';
            });
        });

        // Auto-cerrar alertas después de 5 segundos
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.style.display = 'none', 300);
            });
        }, 5000);

        // ========== FILTROS EN TIEMPO REAL ==========
        const searchInput = document.getElementById('searchUsers');
        const filterRole = document.getElementById('filterRole');
        const filterStatus = document.getElementById('filterStatus');
        const userRows = document.querySelectorAll('.user-row');

        function filterUsers() {
            const searchTerm = searchInput.value.toLowerCase();
            const roleFilter = filterRole.value.toLowerCase();
            const statusFilter = filterStatus.value.toLowerCase();

            userRows.forEach(row => {
                const userRole = row.dataset.role;
                const userStatus = row.dataset.status;
                const userName = row.querySelector('.user-name').textContent.toLowerCase();
                const userEmail = row.querySelector('.user-email').textContent.toLowerCase();
                
                const matchesSearch = userName.includes(searchTerm) || userEmail.includes(searchTerm);
                const matchesRole = !roleFilter || userRole === roleFilter;
                const matchesStatus = !statusFilter || userStatus === statusFilter;

                if (matchesSearch && matchesRole && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterUsers);
        filterRole.addEventListener('change', filterUsers);
        filterStatus.addEventListener('change', filterUsers);

        // ========== ANIMACIONES EN LAS FILAS ==========
        userRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 10px 25px -5px rgba(166, 1, 179, 0.15)';
            });

            row.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    });
</script>
@endpush