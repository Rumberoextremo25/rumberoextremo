@extends('layouts.admin')

{{-- Define el título de la página en la toolbar --}}
@section('page_title_toolbar', 'Gestión de Aliados Comerciales')

{{-- Agrega los estilos CSS específicos de esta vista --}}
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

{{-- Contenido principal de la página --}}
@section('content')
    <div class="allies-management-container p-6 md:p-10 max-w-7xl mx-auto">
        <div class="bg-white p-6 md:p-10 rounded-3xl shadow-lg">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 md:mb-0">
                    <span class="text-gray-900">Gestión de</span>
                    <span style="color: #8a2be2;">Aliados Comerciales</span>
                </h2>
                <a href="{{ route('admin.commercial-allies.create') }}" class="add-ally-btn">
                    <i class="fas fa-plus"></i> Crear Nuevo Aliado
                </a>
            </div>

            {{-- Mensaje de éxito o error --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="ml-auto text-green-700" onclick="this.parentElement.style.display='none';">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="ml-auto text-red-700" onclick="this.parentElement.style.display='none';">&times;</button>
                </div>
            @endif

            <div class="allies-table-container">
                @if ($allies->isEmpty())
                    <p class="no-records-message flex flex-col items-center justify-center p-16">
                        <i class="fas fa-store-slash text-5xl mb-4" style="color: #e5e7eb;"></i>
                        <br>
                        No hay aliados comerciales para mostrar.
                        <br>
                        <a href="{{ route('admin.commercial-allies.create') }}" class="add-ally-btn mt-4">
                            <i class="fas fa-plus"></i> Añadir el primer Aliado
                        </a>
                    </p>
                @else
                    <table class="allies-table w-full">
                        <thead>
                            <tr>
                                <th class="py-4">ID</th>
                                <th class="py-4">Nombre</th>
                                <th class="py-4">Logo</th>
                                <th class="py-4">Rating</th>
                                <th class="py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allies as $ally)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-4 font-medium text-gray-900" data-label="ID">{{ $ally->id }}</td>
                                    <td class="py-4" data-label="Nombre">{{ $ally->name }}</td>
                                    <td class="py-4" data-label="Logo">
                                        @if($ally->logo_url)
                                            <img src="{{ $ally->logo_url }}" alt="{{ $ally->name }}" class="ally-logo">
                                        @else
                                            <span style="color: #9ca3af;">No logo</span>
                                        @endif
                                    </td>
                                    <td class="py-4" data-label="Rating">
                                        <div class="star-rating flex items-center gap-1">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $ally->rating)
                                                    <i class="fas fa-star"></i>
                                                @else
                                                    <i class="far fa-star"></i>
                                                @endif
                                            @endfor
                                            <span class="text-sm ml-1 text-gray-600">({{ number_format($ally->rating, 1) }})</span>
                                        </div>
                                    </td>
                                    <td class="py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.commercial-allies.edit', $ally->id) }}" class="btn-icon edit-btn" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.commercial-allies.destroy', $ally->id) }}" method="POST" class="delete-form" data-ally-name="{{ $ally->name }}">
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
                <p>¿Estás seguro de que quieres eliminar al aliado "<strong id="allyNameToDelete"></strong>"? Esta acción es irreversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-modal-btn">Cancelar</button>
                <button type="button" class="btn confirm-modal-btn">Eliminar</button>
            </div>
        </div>
    </div>
@endsection

{{-- Agrega los scripts específicos para la vista --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('confirmationModal');
        const closeBtn = modal.querySelector('.close-modal-btn');
        const cancelBtn = modal.querySelector('.cancel-modal-btn');
        const confirmBtn = modal.querySelector('.confirm-modal-btn');
        const allyNameDisplay = document.getElementById('allyNameToDelete');
        let formToDelete = null;

        // Open modal on delete form submission
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                formToDelete = this;
                const allyName = this.dataset.allyName;
                allyNameDisplay.textContent = allyName;
                modal.classList.remove('hidden');
                modal.style.display = 'flex'; // Asegura que el overlay se muestre correctamente
            });
        });

        // Close modal handlers
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
        cancelBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Confirm deletion and submit the form
        confirmBtn.addEventListener('click', function() {
            if (formToDelete) {
                formToDelete.submit();
            }
        });
    });
</script>
@endpush