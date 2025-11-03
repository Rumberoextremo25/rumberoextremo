@extends('layouts.admin')

@section('page_title_toolbar', 'Gestión de Promociones')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/promotion.css') }}">
@endpush

@section('content')
    <div class="promotions-management-container">
        <div class="bg-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 md:mb-0">
                    <span class="text-gray-900">Gestión de</span>
                    <span style="color: #8a2be2;">Promociones</span>
                </h2>
                <a href="{{ route('admin.promotions.create') }}" class="add-btn">
                    <i class="fas fa-plus"></i> Crear Nueva Promoción
                </a>
            </div>

            {{-- Mensaje de éxito o error --}}
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="ml-auto" onclick="this.parentElement.style.display='none';">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="ml-auto" onclick="this.parentElement.style.display='none';">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <div class="promotions-table-container">
                @if ($promotions->isEmpty())
                    <div class="no-records-message">
                        <i class="fas fa-tags"></i>
                        <p>No hay promociones para mostrar.</p>
                        <a href="{{ route('admin.promotions.create') }}" class="add-btn">
                            <i class="fas fa-plus"></i> Añadir la primera Promoción
                        </a>
                    </div>
                @else
                    <table class="promotions-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Imagen</th>
                                <th>Descuento</th>
                                <th>Precio</th>
                                <th>Expira</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($promotions as $promotion)
                                <tr>
                                    <td data-label="ID">{{ $promotion->id }}</td>
                                    <td data-label="Título">{{ $promotion->title }}</td>
                                    <td data-label="Imagen">
                                        @if($promotion->image_url)
                                            <img src="{{ $promotion->image_url }}" alt="{{ $promotion->title }}" class="promotion-image">
                                        @else
                                            <span class="no-image-text">No imagen</span>
                                        @endif
                                    </td>
                                    <td data-label="Descuento">{{ $promotion->discount }}</td>
                                    <td data-label="Precio">{{ number_format((float)($promotion->price ?? 0), 2) }}</td>
                                    <td data-label="Expira">{{ $promotion->expires_at ? $promotion->expires_at->format('Y-m-d') : 'N/A' }}</td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-2">
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
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- Custom Confirmation Modal --}}
    <div id="confirmationModal" class="modal-overlay hidden">
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
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('confirmationModal');
        const closeBtn = modal.querySelector('.close-modal-btn');
        const cancelBtn = modal.querySelector('.cancel-modal-btn');
        const confirmBtn = modal.querySelector('.confirm-modal-btn');
        const promotionTitleDisplay = document.getElementById('promotionTitleToDelete');
        let formToDelete = null;

        // Open modal on delete form submission
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                formToDelete = this;
                const promotionTitle = this.dataset.promotionTitle;
                promotionTitleDisplay.textContent = promotionTitle;
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            });
        });

        // Close modal handlers
        function closeModal() {
            modal.style.display = 'none';
            formToDelete = null;
        }

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Confirm deletion and submit the form
        confirmBtn.addEventListener('click', function() {
            if (formToDelete) {
                formToDelete.submit();
            }
        });

        // Efectos hover mejorados
        document.querySelectorAll('.add-btn, .btn-icon').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });

            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Cerrar alertas automáticamente después de 5 segundos
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    });
</script>
@endpush