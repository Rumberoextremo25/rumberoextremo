@extends('layouts.admin')

@section('page_title_toolbar', 'Gestión de Banners')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/banner.css') }}">
@endpush

@section('content')

    <div class="card-container">
        <div class="card-header">
            <h2 class="card-title">Gestión de <span>Banners</span></h2>
            <a href="{{ route('admin.banners.create') }}" class="add-new-btn">
                <i class="fas fa-plus"></i> Crear Nuevo Banner
            </a>
        </div>

        {{-- Mensaje de éxito o error --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
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
                                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="banner-image">
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
                                    <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon delete-btn" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este banner? Esta acción es irreversible.');">
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cerrar alertas automáticamente después de 5 segundos
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.display = 'none';
                });
            }, 5000);

            // Efectos hover en botones
            document.querySelectorAll('.add-new-btn, .btn-icon').forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });

                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Mejorar la confirmación de eliminación
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('¿Estás seguro de que quieres eliminar este banner?\nEsta acción no se puede deshacer.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
@endpush