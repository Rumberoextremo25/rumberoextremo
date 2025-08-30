{{-- resources/views/Admin/aliado/aliado.blade.php --}}

@extends('layouts.admin')

@section('title', 'Gestión de Aliados')

@section('page_title_toolbar', 'Listado de Aliados')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Enlaza aquí tu archivo CSS de estilos --}}
@endpush

@section('content')
    <div class="users-section-container">
        <h2 class="section-title">Listado de Aliados</h2>

        <div class="header-actions">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar Aliados...">
            </div>
            <a href="{{ route('aliados.create') }}" class="add-user-btn">
                <i class="fas fa-plus-circle"></i> Añadir nuevo aliado
            </a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo de Aliado</th>
                        <th>Categoría</th>
                        <th>Subcategoría</th>
                        <th>Descuento</th>
                        <th>Estado</th>
                        <th>Fecha de registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allies as $ally)
                        <tr>
                            <td data-label="ID">{{ $ally->id }}</td>
                            <td data-label="Nombre Empresa">{{ $ally->company_name }}</td>
                            <td data-label="Tipo de Aliado">
                                <span class="badge badge-type-{{ strtolower($ally->businessType->name ?? 'sin-tipo') }}">
                                    {{ $ally->businessType->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td data-label="Categoria">{{ $ally->category->name ?? 'N/A' }}</td>
                            <td data-label="Subcategoria">{{ $ally->subCategory->name ?? 'N/A' }}</td>
                            <td data-label="Descuento">{{ $ally->discount ?? 'N/A' }}%</td>
                            <td data-label="Estado">
                                <span class="badge badge-status-{{ strtolower($ally->status) }}">
                                    {{ $ally->status }}
                                </span>
                            </td>
                            <td data-label="Fecha de registro">{{ \Carbon\Carbon::parse($ally->registration_date)->format('Y-m-d') }}</td>
                            <td data-label="Acciones">
                                <div class="action-group">
                                    <a href="{{ route('aliados.index', $ally->id) }}" class="action-btn view" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('aliado.edit', $ally->id) }}" class="action-btn edit" title="Editar Aliado">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="empty-state">No hay aliados registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection