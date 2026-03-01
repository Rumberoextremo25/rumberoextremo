{{-- resources/views/Admin/aliado/aliado.blade.php --}}
@extends('layouts.admin')

@section('title', 'Gestión de Aliados')

@section('page_title_toolbar', 'Listado de Aliados')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/aliados.css') }}">
@endpush

@section('content')
    <div class="aliados-wrapper">
        {{-- Header con bienvenida --}}
        <div class="aliados-header-bar">
            <div class="header-content">
                <h1 class="page-title">
                    <span class="title-main">Gestión de</span>
                    <span class="title-accent">Aliados Comerciales</span>
                </h1>
                <p class="page-subtitle">
                    <i class="fas fa-handshake"></i>
                    Administra y gestiona tus aliados comerciales
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

        {{-- Alertas --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        @endif

        {{-- Barra de acciones --}}
        <div class="actions-bar">
            <div class="actions-left">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar aliados por empresa, tipo o categoría...">
                </div>
                
                <div class="filter-dropdown">
                    <select id="filterType" class="filter-select">
                        <option value="">Todos los tipos</option>
                        @foreach($businessTypes ?? [] as $type)
                            <option value="{{ strtolower($type->name) }}">{{ $type->name }}</option>
                        @endforeach
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
                <a href="{{ route('admin.aliados.create') }}" class="btn-add">
                    <i class="fas fa-plus-circle"></i>
                    Nuevo Aliado
                </a>
            </div>
        </div>

        {{-- Tarjetas de estadísticas rápidas --}}
        <div class="stats-grid">
            <div class="stat-card" data-color="purple">
                <div class="stat-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $allies->total() }}</span>
                    <span class="stat-label">Total Aliados</span>
                </div>
            </div>

            <div class="stat-card" data-color="green">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $allies->where('status', 'activo')->count() }}</span>
                    <span class="stat-label">Activos</span>
                </div>
            </div>

            <div class="stat-card" data-color="orange">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $allies->where('status', 'pendiente')->count() }}</span>
                    <span class="stat-label">Pendientes</span>
                </div>
            </div>

            <div class="stat-card" data-color="red">
                <div class="stat-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $allies->where('status', 'inactivo')->count() }}</span>
                    <span class="stat-label">Inactivos</span>
                </div>
            </div>
        </div>

        {{-- Tabla de aliados --}}
        @if($allies->isEmpty())
            <div class="empty-state">
                <i class="fas fa-handshake-slash"></i>
                <h3>No hay aliados registrados</h3>
                <p>Comienza agregando tu primer aliado comercial</p>
                <a href="{{ route('admin.aliados.create') }}" class="btn-add">
                    <i class="fas fa-plus-circle"></i>
                    Crear Primer Aliado
                </a>
            </div>
        @else
            <div class="table-container">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Empresa</th>
                                <th>Tipo</th>
                                <th>Categoría</th>
                                <th>Subcategoría</th>
                                <th>Descuento</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="aliadosTableBody">
                            @foreach($allies as $ally)
                                <tr class="aliado-row" 
                                    data-type="{{ strtolower($ally->businessType->name ?? '') }}" 
                                    data-status="{{ strtolower($ally->status) }}">
                                    <td>
                                        <span class="id-badge">#{{ $ally->id }}</span>
                                    </td>
                                    <td>
                                        <div class="company-info">
                                            <div class="company-name">{{ $ally->company_name }}</div>
                                            @if($ally->contact_person_name)
                                                <div class="contact-person">
                                                    <i class="fas fa-user"></i>
                                                    {{ $ally->contact_person_name }}
                                                </div>
                                            @endif
                                            @if($ally->contact_email)
                                                <div class="contact-email">
                                                    <i class="fas fa-envelope"></i>
                                                    {{ $ally->contact_email }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-type-{{ strtolower($ally->businessType->name ?? 'sin-tipo') }}">
                                            <i class="fas fa-tag"></i>
                                            {{ $ally->businessType->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="category-text">{{ $ally->category->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="subcategory-text">{{ $ally->subCategory->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @if($ally->discount)
                                            <span class="discount-badge">
                                                <i class="fas fa-percentage"></i>
                                                {{ $ally->discount }}%
                                            </span>
                                        @else
                                            <span class="no-discount">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-status-{{ strtolower($ally->status) }}">
                                            <i class="fas fa-circle"></i>
                                            {{ ucfirst($ally->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <i class="fas fa-calendar"></i>
                                            {{ \Carbon\Carbon::parse($ally->registered_at ?? $ally->created_at)->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.aliados.show', $ally->id) }}" class="action-btn view" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.aliados.edit', $ally->id) }}" class="action-btn edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.aliados.destroy', $ally->id) }}" method="POST" class="delete-form" 
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este aliado? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn delete" title="Eliminar">
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
            @if($allies->hasPages())
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        <i class="fas fa-list-ul"></i>
                        Mostrando {{ $allies->firstItem() ?? 0 }} - {{ $allies->lastItem() ?? 0 }} de {{ $allies->total() }} aliados
                    </div>

                    <div class="pagination">
                        @if ($allies->onFirstPage())
                            <span class="page-link disabled">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                        @else
                            <a href="{{ $allies->previousPageUrl() }}" class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        @endif

                        @foreach ($allies->getUrlRange(1, $allies->lastPage()) as $page => $url)
                            @if ($page == $allies->currentPage())
                                <span class="page-link active">{{ $page }}</span>
                            @elseif ($page >= $allies->currentPage() - 2 && $page <= $allies->currentPage() + 2)
                                <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                            @elseif ($page == 1 || $page == $allies->lastPage())
                                <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                            @elseif ($page == $allies->currentPage() - 3 || $page == $allies->currentPage() + 3)
                                <span class="page-link disabled">...</span>
                            @endif
                        @endforeach

                        @if ($allies->hasMorePages())
                            <a href="{{ $allies->nextPageUrl() }}" class="page-link">
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
        const searchInput = document.getElementById('searchInput');
        const filterType = document.getElementById('filterType');
        const filterStatus = document.getElementById('filterStatus');
        const aliadoRows = document.querySelectorAll('.aliado-row');

        function filterAliados() {
            const searchTerm = searchInput.value.toLowerCase();
            const typeFilter = filterType.value.toLowerCase();
            const statusFilter = filterStatus.value.toLowerCase();

            aliadoRows.forEach(row => {
                const rowType = row.dataset.type || '';
                const rowStatus = row.dataset.status || '';
                const rowText = row.textContent.toLowerCase();
                
                const matchesSearch = searchTerm === '' || rowText.includes(searchTerm);
                const matchesType = typeFilter === '' || rowType === typeFilter;
                const matchesStatus = statusFilter === '' || rowStatus === statusFilter;

                if (matchesSearch && matchesType && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterAliados);
        filterType.addEventListener('change', filterAliados);
        filterStatus.addEventListener('change', filterAliados);

        // ========== ANIMACIONES EN LAS FILAS ==========
        aliadoRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 10px 25px -5px rgba(166, 1, 179, 0.15)';
            });

            row.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });

        // ========== EFECTOS HOVER EN BOTONES ==========
        document.querySelectorAll('.action-btn, .btn-add, .page-link').forEach(btn => {
            if (btn && !btn.classList.contains('disabled') && !btn.classList.contains('active')) {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            }
        });
    });
</script>
@endpush