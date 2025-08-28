@extends('layouts.admin')

@section('page_title_toolbar', 'Gestión de Banners')

@section('content')

    <div class="card-container">
        <div class="card-header">
            <h2 class="card-title">Gestión de <span style="color: #8a3ffc;">Banners</span></h2>
            <a href="{{ route('admin.banners.create') }}" class="add-new-btn">
                <i class="fas fa-plus"></i> Crear Nuevo Banner
            </a>
        </div>

        {{-- Mensaje de éxito o error --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="table-container">
            @if ($banners->isEmpty())
                <div class="no-records-message">
                    <i class="fas fa-images no-records-icon"></i>
                    <p>No hay banners para mostrar.</p>
                    <a href="{{ route('admin.banners.create') }}" class="add-new-btn">
                        <i class="fas fa-plus"></i> Añadir el primer Banner
                    </a>
                </div>
            @else
                <table class="data-table">
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
                                <td data-label="Imagen" class="banner-image-cell">
                                    @if($banner->image_url)
                                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}">
                                    @else
                                        <span class="no-image-text">No imagen</span>
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
    {{-- Scripts específicos si los hubiera --}}

@endpush