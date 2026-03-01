@extends('layouts.admin')

@section('title', 'Gestión de Promociones Rumberas')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/promotion.css') }}">
@endpush

@section('content')
<div class="rumbero-wrapper">
    {{-- HEADER CON FRASE RUMBERA --}}
    <div class="rumbero-header">
        <div class="header-content">
            <h1 class="header-title">
                Gestión de <span class="gradient-text">Promociones</span>
            </h1>
            <p class="header-subtitle">
                <i class="fas fa-fire"></i>
                ¡Prepara las ofertas más extremas para la comunidad!
            </p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.promotions.create') }}" class="btn-rumbero">
                <i class="fas fa-plus"></i>
                Nueva Promoción
                <i class="fas fa-fire"></i>
            </a>
        </div>
    </div>

    {{-- QUICK STATS --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $promotions->count() }}</span>
                <span class="stat-label">Total Promociones</span>
            </div>
            <div class="stat-trend">
                <i class="fas fa-arrow-up"></i>
                <span>+12%</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $promotions->filter(function($p) { return !$p->expires_at || $p->expires_at->isFuture(); })->count() }}</span>
                <span class="stat-label">Activadas</span>
            </div>
            <div class="stat-trend">
                <i class="fas fa-check-circle"></i>
                <span>Activas</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-hourglass-end"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $promotions->filter(function($p) { return $p->expires_at && $p->expires_at->isPast(); })->count() }}</span>
                <span class="stat-label">Expiradas</span>
            </div>
            <div class="stat-trend">
                <i class="fas fa-history"></i>
                <span>Archivar</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fas fa-percent"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $promotions->avg('discount') ? number_format($promotions->avg('discount'), 0) . '%' : '0%' }}</span>
                <span class="stat-label">Descuento Promedio</span>
            </div>
            <div class="stat-trend">
                <i class="fas fa-chart-line"></i>
                <span>+5%</span>
            </div>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if (session('success'))
        <div class="alert-modern success">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-content">
                <strong>¡Éxito rumbero!</strong>
                <p>{{ session('success') }}</p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-modern error">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="alert-content">
                <strong>¡Ojo rumbero!</strong>
                <p>{{ session('error') }}</p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- FILTROS Y BÚSQUEDA --}}
    <div class="filters-section">
        <div class="search-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" id="searchPromotion" placeholder="Buscar promociones por título...">
        </div>
        
        <div class="filter-wrapper">
            <select id="filterStatus" class="filter-select">
                <option value="all">Todas las promociones</option>
                <option value="active">Activadas</option>
                <option value="expired">Expiradas</option>
            </select>
            <i class="fas fa-chevron-down"></i>
        </div>

        <button class="btn-refresh" id="refreshTable">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>

    {{-- TABLA DE PROMOCIONES --}}
    <div class="table-wrapper">
        @if ($promotions->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <h3>¡No hay promociones rumberas!</h3>
                <p>Comienza creando la primera oferta extrema</p>
                <a href="{{ route('admin.promotions.create') }}" class="btn-rumbero">
                    <i class="fas fa-plus"></i>
                    Crear Primera Promoción
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Promoción</th>
                            <th>Imagen</th>
                            <th>Descuento</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($promotions as $promotion)
                            @php
                                $isExpired = $promotion->expires_at && $promotion->expires_at->isPast();
                                $isActive = !$isExpired && $promotion->is_active;
                            @endphp
                            <tr>
                                <td>
                                    <span class="id-badge">#{{ $promotion->id }}</span>
                                </td>
                                <td>
                                    <div class="promotion-info">
                                        <div class="promotion-title">{{ $promotion->title }}</div>
                                        @if($promotion->description)
                                            <div class="promotion-desc">
                                                <i class="fas fa-align-left"></i>
                                                {{ Str::limit($promotion->description, 50) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($promotion->image_url)
                                        <div class="image-preview" onclick="openImageModal('{{ Storage::url($promotion->image_url) }}', '{{ $promotion->title }}')">
                                            <img src="{{ Storage::url($promotion->image_url) }}" alt="{{ $promotion->title }}">
                                            <div class="image-overlay">
                                                <i class="fas fa-search-plus"></i>
                                            </div>
                                        </div>
                                    @else
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="discount-chip">
                                        <i class="fas fa-percent"></i>
                                        {{ $promotion->discount }}% OFF
                                    </span>
                                </td>
                                <td>
                                    <span class="price-chip">${{ number_format((float)($promotion->price ?? 0), 2) }}</span>
                                </td>
                                <td>
                                    @if($promotion->expires_at)
                                        <div class="status-container">
                                            <span class="date-chip {{ $isExpired ? 'expired' : 'active' }}">
                                                <i class="fas {{ $isExpired ? 'fa-calendar-times' : 'fa-calendar-check' }}"></i>
                                                {{ $promotion->expires_at->format('d/m/Y') }}
                                            </span>
                                            @if($isExpired)
                                                <span class="status-badge expired">
                                                    <i class="fas fa-clock"></i> Expirada
                                                </span>
                                            @else
                                                @if($promotion->is_active)
                                                    <span class="status-badge active">
                                                        <i class="fas fa-bolt"></i> Activada
                                                    </span>
                                                @else
                                                    <span class="status-badge inactive">
                                                        <i class="fas fa-pause-circle"></i> Inactiva
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    @else
                                        @if($promotion->is_active)
                                            <span class="status-badge active">
                                                <i class="fas fa-bolt"></i> Activada
                                            </span>
                                        @else
                                            <span class="status-badge inactive">
                                                <i class="fas fa-pause-circle"></i> Inactiva
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="{{ route('admin.promotions.edit', $promotion->id) }}" 
                                           class="action-btn edit" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="action-btn view" 
                                                title="Vista previa"
                                                onclick="openImageModal('{{ Storage::url($promotion->image_url) }}', '{{ $promotion->title }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" 
                                                class="action-btn delete" 
                                                title="Eliminar"
                                                onclick="confirmDelete({{ $promotion->id }}, '{{ $promotion->title }}')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    <form action="{{ route('admin.promotions.destroy', $promotion->id) }}" 
                                          method="POST" 
                                          id="delete-form-{{ $promotion->id }}" 
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
    @if(method_exists($promotions, 'links'))
        <div class="pagination-modern">
            <div class="pagination-info">
                <i class="fas fa-tags"></i>
                <span>Mostrando {{ $promotions->firstItem() ?? 0 }} - {{ $promotions->lastItem() ?? 0 }} de {{ $promotions->total() }} promociones</span>
            </div>
            {{ $promotions->links() }}
        </div>
    @endif

    {{-- MODAL DE VISTA PREVIA --}}
    <div class="modal-modern" id="imageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-eye"></i> Vista Previa</h3>
                <button class="modal-close" onclick="closeImageModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <img id="modalImage" src="" alt="Vista previa">
                <p id="modalImageCaption" class="image-caption"></p>
            </div>
        </div>
    </div>

    {{-- MODAL DE CONFIRMACIÓN --}}
    <div class="modal-modern" id="deleteModal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-icon warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="modal-title">¡Atención Rumbero!</h3>
            <div class="modal-body text-center">
                <p>¿Estás seguro de eliminar la promoción:</p>
                <p class="highlight-text" id="deletePromotionTitle"></p>
                <p class="warning-text">
                    <i class="fas fa-exclamation-circle"></i>
                    Esta acción es irreversible
                </p>
            </div>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="closeDeleteModal()">Cancelar</button>
                <button class="btn-danger" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Búsqueda en tiempo real
        const searchInput = document.getElementById('searchPromotion');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                document.querySelectorAll('.modern-table tbody tr').forEach(row => {
                    const title = row.querySelector('.promotion-title')?.textContent.toLowerCase() || '';
                    row.style.display = title.includes(searchTerm) ? '' : 'none';
                });
            });
        }

        // Filtro por estado
        const filterSelect = document.getElementById('filterStatus');
        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                const filterValue = this.value;
                document.querySelectorAll('.modern-table tbody tr').forEach(row => {
                    const statusBadge = row.querySelector('.status-badge');
                    if (!statusBadge) return;
                    
                    const isActive = statusBadge.classList.contains('active');
                    const isExpired = statusBadge.classList.contains('expired');
                    
                    if (filterValue === 'all') {
                        row.style.display = '';
                    } else if (filterValue === 'active' && isActive) {
                        row.style.display = '';
                    } else if (filterValue === 'expired' && isExpired) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Refresh button
        document.getElementById('refreshTable')?.addEventListener('click', () => location.reload());

        // Auto-cerrar alertas
        document.querySelectorAll('.alert-modern').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    });

    // Modal functions
    function openImageModal(imageUrl, title) {
        if (!imageUrl) return;
        document.getElementById('modalImage').src = imageUrl;
        document.getElementById('modalImageCaption').textContent = title;
        document.getElementById('imageModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    // Delete functions
    let deleteFormId = null;

    function confirmDelete(id, title) {
        deleteFormId = id;
        document.getElementById('deletePromotionTitle').textContent = `"${title}"`;
        document.getElementById('deleteModal').classList.add('active');
        document.body.style.overflow = 'hidden';
        
        document.getElementById('confirmDeleteBtn').onclick = () => {
            document.getElementById(`delete-form-${deleteFormId}`).submit();
        };
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
        document.body.style.overflow = '';
        deleteFormId = null;
    }

    // Close modals with Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeImageModal();
            closeDeleteModal();
        }
    });

    // Close modals clicking outside
    window.onclick = (e) => {
        if (e.target.classList.contains('modal-modern')) {
            closeImageModal();
            closeDeleteModal();
        }
    };
</script>
@endpush
@endsection