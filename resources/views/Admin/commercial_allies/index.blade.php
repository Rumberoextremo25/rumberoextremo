@extends('layouts.admin') {{-- Asume que tu layout base se llama admin.blade.php --}}

@section('title', 'Gestión de Aliados Comerciales')

@push('styles') {{-- Agregamos el CSS específico de esta vista --}}
    {{-- Asegúrate de que Font Awesome y Google Fonts estén en tu layout admin global para evitar duplicados --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    {{-- Enlazamos al nuevo archivo CSS para la gestión de aliados --}}
    <link rel="stylesheet" href="{{ asset('css/admin/commercial.css') }}">
@endpush

@section('content')
    <div class="allies-management-container">
        <div class="header-actions">
            <h2>Gestión de <span style="color: var(--secondary-color);">Aliados Comerciales</span></h2>
            <a href="{{ route('admin.commercial-allies.create') }}" class="add-ally-btn">
                <i class="fas fa-plus"></i> Crear Nuevo Aliado
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

        <div class="table-responsive">
            @if ($allies->isEmpty())
                <p class="no-records-message">
                    <i class="fas fa-store-slash" style="font-size: 3rem; margin-bottom: 1rem; color: var(--border-color);"></i>
                    <br>
                    No hay aliados comerciales para mostrar.
                    <br>
                    <a href="{{ route('admin.commercial-allies.create') }}" class="add-ally-btn" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i> Añadir el primer Aliado
                    </a>
                </p>
            @else
                <table class="data-table"> {{-- Cambiado a .data-table para consistencia --}}
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Logo</th>
                            <th>Rating</th>
                            <th class="actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allies as $ally)
                            <tr>
                                <td data-label="ID">{{ $ally->id }}</td>
                                <td data-label="Nombre">{{ $ally->name }}</td>
                                <td data-label="Logo">
                                    @if($ally->logo_url)
                                        <img src="{{ $ally->logo_url }}" alt="{{ $ally->name }}">
                                    @else
                                        <span style="color: var(--light-text-color);">No logo</span>
                                    @endif
                                </td>
                                <td data-label="Rating">
                                    <div class="star-rating">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $ally->rating)
                                                <i class="fas fa-star"></i> {{-- Estrella rellena --}}
                                            @else
                                                <i class="far fa-star"></i> {{-- Estrella vacía --}}
                                            @endif
                                        @endfor
                                        ({{ number_format($ally->rating, 1) }})
                                    </div>
                                </td>
                                <td class="actions">
                                    <a href="{{ route('admin.commercial-allies.edit', $ally->id) }}" class="btn-icon edit-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    {{-- Agregamos la clase delete-form y data-ally-name para el JS --}}
                                    <form action="{{ route('admin.commercial-allies.destroy', $ally->id) }}" method="POST" class="delete-form" data-ally-name="{{ $ally->name }}">
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
                <p>¿Estás seguro de que quieres eliminar al aliado "<strong id="allyNameToDelete"></strong>"? Esta acción es irreversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-modal-btn">Cancelar</button>
                <button type="button" class="btn confirm-modal-btn">Eliminar</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Pasa la URL base para la gestión de aliados al JS (si fuera necesario para futuras funcionalidades) --}}
    <script>
        window.alliesBaseUrl = "{{ route('admin.commercial-allies.index') }}";
    </script>
    {{-- Carga tu script externo aquí --}}
    <script src="{{ asset('js/admin/commercial.js') }}"></script>
@endpush