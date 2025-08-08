@extends('layouts.admin') {{-- Asume que tu layout base se llama admin.blade.php --}}

@section('title', 'Gestión de Aliados')
@section('page_title_toolbar', 'Gestión de Aliados')

@push('styles')
    {{-- Asegúrate de que Font Awesome esté cargado en tu layout global --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    {{-- Enlaza al archivo CSS específico para la vista de aliados --}}
    <link rel="stylesheet" href="{{ asset('css/admin/aliados.css') }}">
@endpush

@section('content')
    <div class="allies-management-container">
        <div class="header-actions">
            <h2>Gestión de Aliados</h2>
            <a href="{{ route('aliados.create') }}" class="add-ally-btn">
                <i class="fas fa-plus-circle"></i> Añadir Nuevo Aliado
            </a>
        </div>

        {{-- Mensajes de Sesión --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Barra de Búsqueda --}}
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="allySearch"
                placeholder="Buscar por nombre, RIF, contacto, email, categoría, subcategoría o estado...">
        </div>

        {{-- Tabla de Aliados --}}
        <div class="table-responsive">
            <table class="allies-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>RIF</th>
                        <th>Categoría</th>
                        <th>Subcategoría</th>
                        <th>Descuento</th>
                        <th>Descripción</th> {{-- Nueva columna para la descripción --}}
                        <th>Contacto</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($allies as $ally)
                        <tr>
                            <td data-label="ID">{{ $ally->id }}</td>
                            <td data-label="Imagen">
                                @if ($ally->image_url)
                                    <img src="{{ asset('storage/' . $ally->image_url) }}" alt="Imagen de {{ $ally->company_name }}" class="ally-image">
                                @else
                                    <span class="no-image">No disponible</span>
                                @endif
                            </td>
                            <td data-label="Nombre">{{ $ally->company_name }}</td>
                            <td data-label="RIF">{{ $ally->company_rif }}</td>
                            <td data-label="Categoría">{{ $ally->category->name ?? 'N/A' }}</td>
                            <td data-label="Subcategoría">{{ $ally->subCategory->name ?? 'N/A' }}</td>
                            <td data-label="Descuento">{{ $ally->discount ?? 'N/A' }}</td>
                            <td data-label="Descripción">{{ $ally->description ?? 'N/A' }}</td> {{-- Muestra la descripción --}}
                            <td data-label="Contacto">{{ $ally->contact_person_name }}</td>
                            <td data-label="Email">{{ $ally->contact_email }}</td>
                            <td data-label="Estado">
                                <span class="status-badge status-{{ strtolower($ally->status) }}">
                                    {{ ucfirst($ally->status) }}
                                </span>
                            </td>
                            <td data-label="Acciones" class="actions">
                                <a href="{{ route('aliado.edit', $ally->id) }}" class="btn-icon edit-btn"
                                    title="Editar Aliado">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('aliados.destroy', $ally->id) }}" method="POST"
                                    style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon delete-btn" title="Eliminar Aliado"
                                        onclick="return confirm('¿Estás seguro de que quieres eliminar a {{ $ally->company_name }}? Esta acción no se puede deshacer.');">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            {{-- COLSPAN AJUSTADO A 12 para la nueva columna 'Descripción' --}}
                            <td colspan="12" class="no-records-message">No hay aliados registrados en este momento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/aliados.js') }}"></script>
@endpush