@extends('layouts.admin')

@section('title', 'Gestión de Promociones')

@push('styles') {{-- Agregamos el CSS específico de esta vista --}}
    {{-- Asegúrate de que Font Awesome y Google Fonts estén en tu layout admin global para evitar duplicados --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    {{-- Enlazamos al nuevo archivo CSS para la gestión de promociones --}}
    <link rel="stylesheet" href="{{ asset('css/admin/promotion.css') }}">
@endpush

@section('content')
    <div class="promotions-management-container">
        <div class="header-actions">
            <h2>Gestión de <span style="color: var(--secondary-color);">Promociones</span></h2>
            <a href="{{ route('admin.promotions.create') }}" class="add-promotion-btn">
                <i class="fas fa-plus"></i> Crear Nueva Promoción
            </a>
        </div>

        {{-- Mensaje de éxito o error --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Barra de búsqueda (si la implementas en el futuro) --}}
        {{-- <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="promotionSearch" placeholder="Buscar promociones...">
        </div> --}}

        <div class="table-responsive">
            @if ($promotions->isEmpty())
                <p class="no-records-message">
                    <i class="fas fa-tags" style="font-size: 3rem; margin-bottom: 1rem; color: var(--border-color);"></i>
                    <br>
                    No hay promociones para mostrar.
                    <br>
                    <a href="{{ route('admin.promotions.create') }}" class="add-promotion-btn" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i> Añadir la primera Promoción
                    </a>
                </p>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Imagen</th>
                            <th>Descuento</th>
                            <th>Precio</th>
                            <th>Expira</th>
                            <th class="actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($promotions as $promotion)
                            <tr>
                                <td data-label="ID">{{ $promotion->id }}</td>
                                <td data-label="Título">{{ $promotion->title }}</td>
                                <td data-label="Imagen">
                                    @if($promotion->image_url)
                                        <img src="{{ $promotion->image_url }}" alt="{{ $promotion->title }}">
                                    @else
                                        <span style="color: var(--light-text-color);">No imagen</span>
                                    @endif
                                </td>
                                <td data-label="Descuento">{{ $promotion->discount }}</td>
                                <td data-label="Precio">${{ number_format((float)($promotion->price ?? 0), 2) }}</td>
                                <td data-label="Expira">{{ $promotion->expires_at ? $promotion->expires_at->format('Y-m-d') : 'N/A' }}</td>
                                <td class="actions">
                                    <a href="{{ route('admin.promotions.edit', $promotion->id) }}" class="btn-icon edit-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.promotions.destroy', $promotion->id) }}" method="POST" class="delete-form" data-promotion-title="{{ $promotion->title }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon delete-btn" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Custom Confirmation Modal --}}
    <div id="confirmationModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmar Eliminación</h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar la promoción "<strong id="promotionTitleToDelete"></strong>"? Esta acción es irreversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-modal-btn">Cancelar</button>
                <button type="button" class="btn confirm-modal-btn">Eliminar</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Pasa la URL base para la gestión de promociones al JS --}}
    <script>
        window.promotionsBaseUrl = "{{ route('admin.promotions.index') }}";
    </script>
    {{-- Carga tu script externo aquí --}}
    <script src="{{ asset('js/admin/promotion.js') }}"></script>
@endpush