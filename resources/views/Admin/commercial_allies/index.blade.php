@extends('layouts.admin')

@section('page_title_toolbar', 'Gestión de Aliados Comerciales')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/commercial.css') }}">
@endpush

@section('content')
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
    </div>

    <div class="allies-container no-side-padding">
        <div class="allies-card no-side-padding">
            {{-- Header Modernizado --}}
            <div class="page-header-with-actions">
                <div class="page-header">
                    <h1 class="page-title">
                        <span class="accent">Gestión de Aliados Comerciales</span>
                    </h1>
                    <p class="page-subtitle">
                        <i class="fas fa-handshake"></i>
                        Administra y gestiona tus aliados comerciales
                    </p>
                </div>
                <a href="{{ route('admin.commercial-allies.create') }}" class="add-ally-btn">
                    <i class="fas fa-plus"></i>
                    Crear Nuevo Aliado
                </a>
            </div>

            {{-- Alertas Modernizadas --}}
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="alert-close" onclick="this.parentElement.style.display='none';">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="alert-close" onclick="this.parentElement.style.display='none';">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            {{-- Contenido de la Tabla --}}
            <div class="allies-table-container expand-container">
                @if ($allies->isEmpty())
                    <div class="no-records-message">
                        <i class="fas fa-store-slash"></i>
                        <h3>No hay aliados comerciales registrados</h3>
                        <p>Comienza agregando tu primer aliado comercial</p>
                        <a href="{{ route('admin.commercial-allies.create') }}" class="add-ally-btn">
                            <i class="fas fa-plus"></i>
                            Añadir el Primer Aliado
                        </a>
                    </div>
                @else
                    <table class="allies-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Logo</th>
                                <th>Rating</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allies as $ally)
                                <tr>
                                    <td class="font-medium">#{{ $ally->id }}</td>
                                    <td>
                                        <div class="ally-info">
                                            <span class="ally-name">{{ $ally->name }}</span>
                                            @if ($ally->description)
                                                <span
                                                    class="ally-description">{{ Str::limit($ally->description, 50) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if ($ally->logo_url)
                                            <img src="{{ $ally->logo_url }}" alt="{{ $ally->name }}" class="ally-logo">
                                        @else
                                            <div class="no-logo-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="star-rating">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $ally->rating)
                                                    <i class="fas fa-star"></i>
                                                @else
                                                    <i class="far fa-star"></i>
                                                @endif
                                            @endfor
                                            <span class="rating-value">({{ number_format($ally->rating, 1) }})</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="status-badge {{ $ally->is_active ? 'status-active' : 'status-inactive' }}">
                                            {{ $ally->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.commercial-allies.edit', $ally->id) }}"
                                                class="btn-icon edit-btn" title="Editar Aliado">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.commercial-allies.destroy', $ally->id) }}"
                                                method="POST" class="delete-form" data-ally-name="{{ $ally->name }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-icon delete-btn" title="Eliminar Aliado">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.commercial-allies.show', $ally->id) }}"
                                                class="btn-icon view-btn" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Paginación Segura --}}
            @if (method_exists($allies, 'hasPages') && $allies->hasPages())
                <div class="pagination-container expand-container">
                    <div class="pagination-info">
                        Mostrando {{ $allies->firstItem() }} - {{ $allies->lastItem() }} de {{ $allies->total() }}
                        aliados
                    </div>
                    <div class="pagination-links">
                        {{ $allies->links() }}
                    </div>
                </div>
            @elseif ($allies->count() > 0)
                <div class="pagination-container expand-container">
                    <div class="pagination-info">
                        Total de {{ $allies->count() }} aliado{{ $allies->count() !== 1 ? 's' : '' }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de Confirmación Modernizado --}}
    <div id="confirmationModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-exclamation-triangle"></i>
                    Confirmar Eliminación
                </h3>
                <button type="button" class="close-modal-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar al aliado "<strong id="allyNameToDelete"></strong>"?</p>
                <p class="warning-text">Esta acción no se puede deshacer y se perderán todos los datos asociados.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-modal-btn">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="button" class="btn confirm-modal-btn">
                    <i class="fas fa-trash-alt"></i>
                    Eliminar Definitivamente
                </button>
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
            const allyNameDisplay = document.getElementById('allyNameToDelete');
            let formToDelete = null;

            // Mostrar loading
            function showLoading(show) {
                const loadingOverlay = document.getElementById('loadingOverlay');
                if (loadingOverlay) {
                    loadingOverlay.style.display = show ? 'flex' : 'none';
                }
            }

            // Abrir modal al enviar formulario de eliminación
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    formToDelete = this;
                    const allyName = this.dataset.allyName;
                    allyNameDisplay.textContent = allyName;
                    modal.style.display = 'flex';
                });
            });

            // Cerrar modal
            function closeModal() {
                modal.style.display = 'none';
                formToDelete = null;
            }

            // Event listeners para cerrar modal
            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Confirmar eliminación
            confirmBtn.addEventListener('click', function() {
                if (formToDelete) {
                    showLoading(true);
                    formToDelete.submit();
                }
            });

            // Efectos hover en botones
            document.querySelectorAll('.add-ally-btn, .btn-icon').forEach(button => {
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
