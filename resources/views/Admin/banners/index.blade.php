@extends('layouts.admin')

@section('title', 'Gestión de Banners')

{{-- Incluye los estilos CSS definidos --}}
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    {{-- Enlaza al archivo CSS específico para la vista de banners --}}
    
@endsection

@section('content')
    <div class="allies-management-container"> {{-- Reutilizando la clase del contenedor principal --}}
        <div class="header-actions">
            <h2>Gestión de <span style="color: var(--secondary-color);">Banners</span></h2>
            <a href="{{ route('admin.banners.create') }}" class="add-ally-btn">
                <i class="fas fa-plus"></i> Crear Nuevo Banner
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
            @if ($banners->isEmpty())
                <p class="no-records-message">
                    <i class="fas fa-images" style="font-size: 3rem; margin-bottom: 1rem; color: var(--border-color);"></i>
                    <br>
                    No hay banners para mostrar.
                    <br>
                    <a href="{{ route('admin.banners.create') }}" class="add-ally-btn" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i> Añadir el primer Banner
                    </a>
                </p>
            @else
                <table class="data-table"> {{-- Reutilizando la clase de tabla de datos --}}
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Imagen</th>
                            <th>Orden</th>
                            <th>Activo</th>
                            <th class="actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($banners as $banner)
                            <tr>
                                <td data-label="ID">{{ $banner->id }}</td>
                                <td data-label="Título">{{ $banner->title }}</td>
                                <td data-label="Imagen">
                                    @if($banner->image_url)
                                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" style="max-width: 80px; height: auto; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                    @else
                                        <span style="color: var(--light-text-color);">No imagen</span>
                                    @endif
                                </td>
                                <td data-label="Orden">{{ $banner->order }}</td>
                                <td data-label="Activo">
                                    <span class="status-badge status-{{ $banner->is_active ? 'activo' : 'inactivo' }}">
                                        {{ $banner->is_active ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn-icon edit-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este banner? Esta acción es irreversible.');">
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
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/banner.js') }}"></script>
@endpush