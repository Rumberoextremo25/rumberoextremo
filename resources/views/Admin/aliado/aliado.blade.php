{{-- resources/views/aliados/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Gestión de Aliados')

@section('page_title_toolbar', 'Listado de Aliados')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Enlazamos al nuevo archivo CSS para la gestión de aliados --}}
@endpush

@section('content')
    <div class="main-content">
        <div class="aliados-card-container">
            <div class="table-header">
                <h2 class="title">Listado de Aliados</h2>
                <a href={{ route('aliados.create') }} class="add-aliado-btn">
                    <i class="fas fa-plus"></i> Añadir nuevo aliado
                </a>
            </div>
            
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="buscar aliados...">
            </div>

            <div class="table-responsive">
                <table class="aliados-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo de Aliado</th>
                            <th>Contacto</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Fecha de registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Ejemplo de una fila de datos --}}
                        {{-- @forelse($aliados as $aliado) --}}
                        <tr>
                            <td>1</td>
                            <td>Rumbero Extremo C.A.</td>
                            <td>Empresa</td>
                            <td>Juan Pérez</td>
                            <td>contacto@rumberoextremo.com</td>
                            <td>+58 412 1234567</td>
                            <td>
                                <span class="status-badge status-activo">Activo</span>
                            </td>
                            <td>16/06/2025</td>
                            <td class="actions">
                                <a href="{{ route('aliados.index', 1) }}" class="btn-icon view">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('aliado.edit', 1) }}" class="btn-icon edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('aliados.destroy', 1) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon delete" onclick="return confirm('¿Estás seguro de que quieres eliminar a este aliado?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        {{-- @empty --}}
                        <tr>
                            <td colspan="9" class="empty-state">No hay aliados registrados.</td>
                        </tr>
                        {{-- @endforelse --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Tus scripts, si los necesitas --}}
@endpush