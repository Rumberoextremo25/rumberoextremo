@extends('layouts.admin')

@section('title', 'Gestión de Banners')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/banner.css') }}">
@endpush

@section('content')
<div class="allies-wrapper">
    {{-- HEADER CON GRADIENTE --}}
    <div class="allies-header-bar">
        <div class="header-content">
            <div class="page-title">
                <span class="title-main">Gestión de</span>
                <span class="title-accent">Banners</span>
            </div>
            <div class="page-subtitle">
                <i class="fas fa-images"></i>
                <span>Administra los banners publicitarios del sitio</span>
            </div>
        </div>
        <div class="header-actions">
            <div class="user-greeting">
                <i class="fas fa-chart-line"></i>
                <span>Total: <strong>{{ $banners->count() }}</strong></span>
            </div>
            <a href="{{ route('admin.banners.create') }}" class="btn-add">
                <i class="fas fa-plus"></i>
                Crear Nuevo Banner
            </a>
        </div>
    </div>

    {{-- ALERTAS MODERNAS --}}
    @if (session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- TARJETAS DE ESTADÍSTICAS --}}
    <div class="stats-grid">
        <div class="stat-card" data-color="purple">
            <div class="stat-icon">
                <i class="fas fa-images"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $banners->count() }}</span>
                <span class="stat-label">Total Banners</span>
            </div>
        </div>
        <div class="stat-card" data-color="green">
            <div class="stat-icon">
                <i class="fas fa-eye"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $banners->where('is_active', true)->count() }}</span>
                <span class="stat-label">Banners Activos</span>
            </div>
        </div>
        <div class="stat-card" data-color="orange">
            <div class="stat-icon">
                <i class="fas fa-eye-slash"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $banners->where('is_active', false)->count() }}</span>
                <span class="stat-label">Banners Inactivos</span>
            </div>
        </div>
        <div class="stat-card" data-color="red">
            <div class="stat-icon">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $banners->max('order') ?? 0 }}</span>
                <span class="stat-label">Orden Máximo</span>
            </div>
        </div>
    </div>

    {{-- BARRA DE ACCIONES --}}
    <div class="actions-bar">
        <div class="actions-left">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchBanner" placeholder="Buscar banners por título...">
            </div>
            <div class="filter-dropdown">
                <select id="filterStatus" class="filter-select">
                    <option value="all">Todos los estados</option>
                    <option value="active">Solo activos</option>
                    <option value="inactive">Solo inactivos</option>
                </select>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </div>

    {{-- TABLA DE BANNERS --}}
    <div class="table-container">
        @if ($banners->isEmpty())
            <div class="empty-state">
                <i class="fas fa-images"></i>
                <h3>No hay banners para mostrar</h3>
                <p>Comienza creando tu primer banner publicitario</p>
                <a href="{{ route('admin.banners.create') }}" class="btn-add">
                    <i class="fas fa-plus"></i>
                    Crear Primer Banner
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table" id="bannersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Imagen</th>
                            <th>Orden</th>
                            <th>Estado</th>
                            <th class="actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($banners as $banner)
                            <tr>
                                <td data-label="ID">
                                    <span class="ally-id">#{{ $banner->id }}</span>
                                </td>
                                <td data-label="Título">
                                    <div class="ally-info">
                                        <div class="ally-details">
                                            <span class="ally-name">{{ $banner->title }}</span>
                                            @if($banner->description)
                                                <span class="ally-category">
                                                    <i class="fas fa-align-left"></i>
                                                    {{ Str::limit($banner->description, 40) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Imagen">
                                    @if($banner->image_url)
                                        <div class="logo-wrapper" onclick="openImageModal('{{ asset('storage/' . $banner->image_url) }}', '{{ $banner->title }}')" style="cursor: pointer;">
                                            <img src="{{ asset('storage/' . $banner->image_url) }}" alt="{{ $banner->title }}" class="ally-logo-modern">
                                        </div>
                                    @else
                                        <div class="logo-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td data-label="Orden">
                                    <span class="rating-value">{{ $banner->order }}</span>
                                </td>
                                <td data-label="Estado">
                                    @if($banner->is_active)
                                        <span class="badge badge-status-active">
                                            <i class="fas fa-check-circle"></i>
                                            Activado
                                        </span>
                                    @else
                                        <span class="badge badge-status-inactive">
                                            <i class="fas fa-times-circle"></i>
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="actions">
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.banners.edit', $banner->id) }}" 
                                           class="action-btn edit" 
                                           title="Editar banner">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="action-btn view" 
                                                title="Vista previa"
                                                onclick="openImageModal('{{ asset('storage/' . $banner->image_url) }}', '{{ $banner->title }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" 
                                                class="action-btn delete" 
                                                title="Eliminar banner"
                                                onclick="confirmDelete({{ $banner->id }}, '{{ $banner->title }}')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    <form action="{{ route('admin.banners.destroy', $banner->id) }}" 
                                          method="POST" 
                                          class="delete-form"
                                          id="delete-form-{{ $banner->id }}" 
                                          style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- PAGINACIÓN --}}
    @if(method_exists($banners, 'links'))
        <div class="pagination-wrapper">
            <div class="pagination-info">
                <i class="fas fa-images"></i>
                <span>Mostrando {{ $banners->firstItem() ?? 0 }} - {{ $banners->lastItem() ?? 0 }} de {{ $banners->total() }} banners</span>
            </div>
            {{ $banners->links() }}
        </div>
    @endif

    {{-- MODAL DE VISTA PREVIA DE IMAGEN --}}
    <div class="modal-modern" id="imageModal">
        <div class="modal-card" style="max-width: 600px;">
            <div class="modal-icon" style="background: #dbeafe; color: #3b82f6;">
                <i class="fas fa-eye"></i>
            </div>
            <h3 class="modal-title">Vista Previa</h3>
            <div class="modal-body">
                <img id="modalImage" src="" alt="Vista previa del banner" style="max-width: 100%; border-radius: 12px; margin-bottom: 1rem;">
                <p id="modalImageCaption" class="modal-highlight"></p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeImageModal()">
                    <i class="fas fa-times"></i>
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN --}}
    <div class="modal-modern" id="deleteModal">
        <div class="modal-card">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="modal-title">Confirmar Eliminación</h3>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar el banner:</p>
                <p class="modal-highlight" id="deleteBannerTitle"></p>
                <p class="modal-warning">
                    <i class="fas fa-exclamation-circle"></i>
                    Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="button" class="btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt"></i>
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    {{-- LOADING OVERLAY --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-ring"></div>
            <span class="loading-text">Procesando...</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables para el modal de eliminación
        let deleteFormId = null;

        // Auto-cerrar alertas después de 5 segundos
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 5000);
        });

        // Función de búsqueda
        const searchInput = document.getElementById('searchBanner');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#bannersTable tbody tr');
                
                rows.forEach(row => {
                    const title = row.querySelector('.ally-name')?.textContent.toLowerCase() || '';
                    if (title.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Filtro por estado
        const filterSelect = document.getElementById('filterStatus');
        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                const filterValue = this.value;
                const rows = document.querySelectorAll('#bannersTable tbody tr');
                
                rows.forEach(row => {
                    const statusBadge = row.querySelector('.badge');
                    if (!statusBadge) return;
                    
                    const isActive = statusBadge.classList.contains('badge-status-active');
                    
                    if (filterValue === 'all') {
                        row.style.display = '';
                    } else if (filterValue === 'active' && isActive) {
                        row.style.display = '';
                    } else if (filterValue === 'inactive' && !isActive) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Efectos hover en botones
        document.querySelectorAll('.action-btn, .btn-add, .btn-secondary, .btn-danger').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });

            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Mostrar/ocultar loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    });

    // Función para vista previa de imagen
    function openImageModal(imageUrl, title) {
        if (!imageUrl) return;
        
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const modalCaption = document.getElementById('modalImageCaption');
        
        modalImage.src = imageUrl;
        modalCaption.textContent = title;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Funciones para eliminación
    let deleteFormId = null;

    function confirmDelete(id, title) {
        const modal = document.getElementById('deleteModal');
        const bannerTitle = document.getElementById('deleteBannerTitle');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        deleteFormId = id;
        bannerTitle.textContent = `"${title}"`;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        confirmBtn.onclick = function() {
            document.getElementById(`delete-form-${deleteFormId}`).submit();
        };
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
        deleteFormId = null;
    }

    // Cerrar modales con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
            closeDeleteModal();
        }
    });

    // Cerrar modales al hacer clic fuera
    window.onclick = function(event) {
        const imageModal = document.getElementById('imageModal');
        const deleteModal = document.getElementById('deleteModal');
        
        if (event.target === imageModal) {
            closeImageModal();
        }
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    };
</script>
@endpush
@endsection