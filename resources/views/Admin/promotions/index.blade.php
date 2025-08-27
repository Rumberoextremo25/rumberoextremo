@extends('layouts.admin')

@section('page_title_toolbar', 'Gestion de Promociones')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/promotion.css') }}"> {{-- Tus estilos específicos de promociones --}}
@endpush

@section('content')
    <div class="promotions-management-container">
        <div class="header-actions">
            <h2>Gestión de <span style="color: var(--secondary-color);">Promociones</span></h2>
            <a href="{{ route('admin.promotions.create') }}" class="add-promotion-btn">
                <i class="fas fa-plus"></i> Crear Nueva Promoción
            </a>
        </div>

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
    <script>
        window.promotionsBaseUrl = "{{ route('admin.promotions.index') }}";
    </script>
    <script src="{{ asset('js/admin/promotion.js') }}"></script>
@endpush