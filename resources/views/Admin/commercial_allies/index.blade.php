@extends('layouts.admin')

@section('page_title_toolbar', 'Gestion de Aliados Comerciales')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/commercial.css') }}">
@endpush

@section('content')
    {{-- Loading Overlay --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-ring"></div>
            <span class="loading-text">Procesando...</span>
        </div>
    </div>

    <div class="allies-wrapper">
        {{-- Header con Gradiente --}}
        <div class="allies-header-bar">
            <div class="header-content">
                <div class="page-title">
                    <span class="title-main">Gestion de</span>
                    <span class="title-accent">Aliados Comerciales</span>
                </div>
                <div class="page-subtitle">
                    <i class="fas fa-handshake"></i>
                    <span>Administra y gestiona tus aliados comerciales de manera eficiente</span>
                </div>
            </div>
            <div class="header-actions">
                <div class="user-greeting">
                    <i class="fas fa-user-circle"></i>
                    <span>Bienvenido, <strong>{{ Auth::user()->name ?? 'Admin' }}</strong></span>
                </div>
                <div class="avatar-circle">
                    {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                </div>
            </div>
        </div>

        {{-- Tarjetas de Estadisticas --}}
        <div class="stats-grid">
            @php
                $totalAllies = $allies->total();
                $activeCount = $allies->where('is_active', true)->count();
                $inactiveCount = $allies->where('is_active', false)->count();
                $avgRating = number_format($allies->avg('rating') ?? 0, 1);
            @endphp
            <div class="stat-card" data-color="purple">
                <div class="stat-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $totalAllies }}</span>
                    <span class="stat-label">Total Aliados</span>
                </div>
            </div>
            <div class="stat-card" data-color="green">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $activeCount }}</span>
                    <span class="stat-label">Aliados Activados</span>
                </div>
            </div>
            <div class="stat-card" data-color="orange">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $avgRating }}</span>
                    <span class="stat-label">Rating Promedio</span>
                </div>
            </div>
            <div class="stat-card" data-color="red">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $inactiveCount }}</span>
                    <span class="stat-label">Inactivos</span>
                </div>
            </div>
        </div>

        {{-- Alertas Modernizadas --}}
        @if (session('success'))
            <div class="alert alert-success" id="successAlert">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" onclick="closeAlert(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error" id="errorAlert">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="alert-close" onclick="closeAlert(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        {{-- Barra de Acciones --}}
        <div class="actions-bar">
            <div class="actions-left">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar aliados..." id="searchInput">
                </div>
                <div class="filter-dropdown">
                    <select class="filter-select" id="statusFilter">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activados</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <a href="{{ route('admin.commercial-allies.create') }}" class="btn-add">
                <i class="fas fa-plus"></i>
                <span>Crear Nuevo Aliado</span>
            </a>
        </div>

        {{-- Tabla de Aliados --}}
        <div class="table-container">
            @if ($allies->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-store-slash"></i>
                    <h3>No hay aliados comerciales registrados</h3>
                    <p>Comienza agregando tu primer aliado comercial para gestionar tu red de negocios</p>
                    <a href="{{ route('admin.commercial-allies.create') }}" class="btn-add">
                        <i class="fas fa-plus"></i>
                        Añadir el Primer Aliado
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Aliado</th>
                                <th>Logo</th>
                                <th>Contacto</th>
                                <th>Rating</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Fecha Registro</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allies as $ally)
                                <tr>
                                    <td>
                                        <span class="ally-id">#{{ $ally->id }}</span>
                                    </td>
                                    <td>
                                        <div class="ally-info">
                                            <div class="ally-avatar">
                                                {{ substr($ally->name, 0, 1) }}
                                            </div>
                                            <div class="ally-details">
                                                <span class="ally-name">{{ $ally->name }}</span>
                                                @if($ally->category)
                                                    <span class="ally-category">
                                                        <i class="fas fa-tag"></i>
                                                        {{ $ally->category }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($ally->logo_url)
                                            <div class="logo-wrapper">
                                                <img src="{{ asset('storage/' . $ally->logo_url) }}"
                                                    alt="{{ $ally->name }}" class="ally-logo-modern" loading="lazy">
                                            </div>
                                        @else
                                            <div class="logo-placeholder">
                                                <i class="fas fa-building"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="ally-contact">
                                            @if($ally->email)
                                                <span><i class="fas fa-envelope"></i> {{ $ally->email }}</span>
                                            @endif
                                            @if($ally->phone)
                                                <span><i class="fas fa-phone"></i> {{ $ally->phone }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="rating-modern">
                                            <div class="stars">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    @if ($i <= $ally->rating)
                                                        <i class="fas fa-star star-filled"></i>
                                                    @else
                                                        <i class="far fa-star star-empty"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <span class="rating-value">{{ number_format($ally->rating, 1) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $typeClass = 'badge-type-basico';
                                            $typeIcon = 'fa-user';
                                            if(isset($ally->type)) {
                                                if($ally->type == 'premium') {
                                                    $typeClass = 'badge-type-premium';
                                                    $typeIcon = 'fa-crown';
                                                } elseif($ally->type == 'colaborador') {
                                                    $typeClass = 'badge-type-colaborador';
                                                    $typeIcon = 'fa-handshake';
                                                }
                                            }
                                        @endphp
                                        <span class="badge {{ $typeClass }}">
                                            <i class="fas {{ $typeIcon }}"></i>
                                            {{ ucfirst($ally->type ?? 'Basico') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ally->is_active)
                                            <span class="badge badge-status-activo">
                                                <i class="fas fa-check-circle"></i>
                                                Activado
                                            </span>
                                        @else
                                            <span class="badge badge-status-inactivo">
                                                <i class="fas fa-times-circle"></i>
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <span>{{ $ally->created_at->format('d/m/Y') }}</span>
                                            <small>{{ $ally->created_at->diffForHumans() }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.commercial-allies.show', $ally->id) }}"
                                                class="action-btn view" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.commercial-allies.edit', $ally->id) }}"
                                                class="action-btn edit" title="Editar Aliado">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="action-btn delete"
                                                onclick="openDeleteModal({{ $ally->id }}, '{{ $ally->name }}')"
                                                title="Eliminar Aliado">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <form id="delete-form-{{ $ally->id }}"
                                                action="{{ route('admin.commercial-allies.destroy', $ally->id) }}"
                                                method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginacion --}}
                @if ($allies->hasPages())
                    <div class="pagination-wrapper">
                        <div class="pagination-info">
                            <i class="fas fa-store"></i>
                            <span>
                                Mostrando <strong>{{ $allies->firstItem() }}</strong> -
                                <strong>{{ $allies->lastItem() }}</strong> de
                                <strong>{{ $allies->total() }}</strong> aliados
                            </span>
                        </div>
                        <div class="pagination">
                            {{ $allies->onEachSide(1)->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Modal de Confirmacion --}}
    <div id="deleteModal" class="modal-modern">
        <div class="modal-card">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="modal-title">Confirmar Eliminacion</h3>
            <div class="modal-body">
                <p>¿Estas seguro de que quieres eliminar al aliado</p>
                <p class="modal-highlight" id="modalAllyName"></p>
                <p class="modal-warning">Esta accion no se puede deshacer y se perderan todos los datos asociados.</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="button" class="btn-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash-alt"></i>
                    Eliminar
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentDeleteId = null;

        // Loading overlay
        function showLoading(show) {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.style.display = show ? 'flex' : 'none';
            }
        }

        // Cerrar alertas
        window.closeAlert = function(button) {
            const alert = button.closest('.alert');
            alert.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => {
                alert.remove();
            }, 300);
        };

        // Auto-cerrar alertas
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                if (alert && alert.style.display !== 'none') {
                    alert.style.animation = 'slideOut 0.3s ease forwards';
                    setTimeout(() => {
                        if (alert && alert.remove) alert.remove();
                    }, 300);
                }
            }, 5000);
        });

        // Modal de eliminacion
        window.openDeleteModal = function(id, name) {
            currentDeleteId = id;
            document.getElementById('modalAllyName').textContent = name;
            document.getElementById('deleteModal').classList.add('active');
        };

        window.closeDeleteModal = function() {
            document.getElementById('deleteModal').classList.remove('active');
            currentDeleteId = null;
        };

        window.confirmDelete = function() {
            if (currentDeleteId) {
                closeDeleteModal();
                showLoading(true);
                document.getElementById(`delete-form-${currentDeleteId}`).submit();
            }
        };

        // Cerrar modal con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('deleteModal').classList.contains('active')) {
                closeDeleteModal();
            }
        });

        // Cerrar modal haciendo clic fuera
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Animacion de entrada para las filas
        document.querySelectorAll('tbody tr').forEach((row, index) => {
            row.style.animation = `fadeInUp 0.3s ease forwards ${index * 0.05}s`;
        });
    </script>
@endpush
