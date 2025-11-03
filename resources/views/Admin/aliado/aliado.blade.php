{{-- resources/views/Admin/aliado/aliado.blade.php --}}
@extends('layouts.admin')

@section('title', 'Gestión de Aliados')

@section('page_title_toolbar', 'Listado de Aliados')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/aliados.css') }}">
@endpush

@section('content')
    <div class="users-section-container">
        {{-- Header Moderno --}}
        <div class="page-header-modern">
            <div class="header-content">
                <h1 class="page-title">
                    <span class="title-text">Gestión de</span>
                    <span class="title-accent">Aliados Comerciales</span>
                </h1>
                <p class="page-subtitle">
                    <i class="fas fa-handshake"></i>
                    Administra y gestiona tus aliados comerciales
                </p>
            </div>
            <a href="{{ route('aliados.create') }}" class="modern-primary-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Nuevo Aliado</span>
            </a>
        </div>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="modern-alert success">
                <div class="alert-content">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button class="alert-close" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="modern-alert error">
                <div class="alert-content">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button class="alert-close" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        {{-- Panel de Control --}}
        <div class="control-panel">
            <div class="search-container-modern">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar aliados por nombre, categoría o estado...">
                <div class="search-actions">
                    <button class="search-btn" id="searchButton">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div class="filter-actions">
                <button class="filter-btn" id="filterToggle">
                    <i class="fas fa-filter"></i>
                    Filtros
                </button>
                <div class="table-stats">
                    <span class="stat-badge">
                        <i class="fas fa-users"></i>
                        Total: {{ $allies->count() }} aliados
                    </span>
                </div>
            </div>
        </div>

        {{-- Tabla Moderna --}}
        <div class="modern-table-container">
            <div class="table-wrapper">
                <table class="modern-data-table">
                    <thead>
                        <tr>
                            <th class="column-id">ID</th>
                            <th class="column-company">Empresa</th>
                            <th class="column-type">Tipo</th>
                            <th class="column-category">Categoría</th>
                            <th class="column-subcategory">Subcategoría</th>
                            <th class="column-discount">Descuento</th>
                            <th class="column-status">Estado</th>
                            <th class="column-date">Registro</th>
                            <th class="column-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allies as $ally)
                            <tr class="table-row">
                                <td class="cell-id" data-label="ID">
                                    <span class="id-badge">#{{ $ally->id }}</span>
                                </td>
                                <td class="cell-company" data-label="Empresa">
                                    <div class="company-info">
                                        <div class="company-name">{{ $ally->company_name }}</div>
                                        @if($ally->contact_person_name)
                                            <div class="contact-person">
                                                <i class="fas fa-user"></i>
                                                {{ $ally->contact_person_name }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="cell-type" data-label="Tipo">
                                    <span class="type-badge badge-type-{{ strtolower($ally->businessType->name ?? 'sin-tipo') }}">
                                        <i class="fas fa-tag"></i>
                                        {{ $ally->businessType->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="cell-category" data-label="Categoría">
                                    <span class="category-text">{{ $ally->category->name ?? 'N/A' }}</span>
                                </td>
                                <td class="cell-subcategory" data-label="Subcategoría">
                                    <span class="subcategory-text">{{ $ally->subCategory->name ?? 'N/A' }}</span>
                                </td>
                                <td class="cell-discount" data-label="Descuento">
                                    @if($ally->discount)
                                        <span class="discount-badge">
                                            <i class="fas fa-percentage"></i>
                                            {{ $ally->discount }}%
                                        </span>
                                    @else
                                        <span class="no-discount">N/A</span>
                                    @endif
                                </td>
                                <td class="cell-status" data-label="Estado">
                                    <span class="status-badge badge-status-{{ strtolower($ally->status) }}">
                                        <i class="fas fa-circle"></i>
                                        {{ ucfirst($ally->status) }}
                                    </span>
                                </td>
                                <td class="cell-date" data-label="Registro">
                                    <div class="date-info">
                                        <i class="fas fa-calendar"></i>
                                        {{ \Carbon\Carbon::parse($ally->registered_at)->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="cell-actions" data-label="Acciones">
                                    <div class="action-buttons">
                                        <a href="{{ route('aliados.show', $ally->id) }}" class="action-btn view-btn" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                            <span class="btn-tooltip">Ver Detalles</span>
                                        </a>
                                        <a href="{{ route('aliado.edit', $ally->id) }}" class="action-btn edit-btn" title="Editar">
                                            <i class="fas fa-edit"></i>
                                            <span class="btn-tooltip">Editar</span>
                                        </a>
                                        <form action="{{ route('aliados.destroy', $ally->id) }}" method="POST" class="action-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete-btn" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este aliado?')">
                                                <i class="fas fa-trash-alt"></i>
                                                <span class="btn-tooltip">Eliminar</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-state">
                                    <div class="empty-content">
                                        <i class="fas fa-users-slash"></i>
                                        <h3>No hay aliados registrados</h3>
                                        <p>Comienza agregando tu primer aliado comercial</p>
                                        <a href="{{ route('aliados.create') }}" class="modern-primary-btn outline">
                                            <i class="fas fa-plus"></i>
                                            Crear Primer Aliado
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginación --}}
        @if($allies->hasPages())
            <div class="pagination-modern">
                <div class="pagination-info">
                    Mostrando {{ $allies->firstItem() }} - {{ $allies->lastItem() }} de {{ $allies->total() }} aliados
                </div>
                <div class="pagination-links">
                    {{ $allies->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Búsqueda en tiempo real
        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('.modern-data-table tbody tr');
        
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                
                tableRows.forEach(row => {
                    if (row.classList.contains('empty-state')) return;
                    
                    const text = row.textContent.toLowerCase();
                    if (searchTerm === '' || text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Cerrar alertas automáticamente
        setTimeout(() => {
            document.querySelectorAll('.modern-alert').forEach(alert => {
                if (alert) {
                    alert.style.display = 'none';
                }
            });
        }, 5000);

        // Efectos hover en botones
        document.querySelectorAll('.action-btn, .modern-primary-btn').forEach(btn => {
            if (btn) {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            }
        });

        // Toggle de filtros
        const filterToggle = document.getElementById('filterToggle');
        if (filterToggle) {
            filterToggle.addEventListener('click', function() {
                alert('Funcionalidad de filtros en desarrollo');
            });
        }

        // Botón de búsqueda
        const searchButton = document.getElementById('searchButton');
        if (searchButton) {
            searchButton.addEventListener('click', function() {
                searchInput.focus();
            });
        }
    });
</script>
@endpush