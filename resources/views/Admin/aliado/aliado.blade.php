{{-- resources/views/productos/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Gestión de Productos')

@section('page_title_toolbar', 'Listado de Productos')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="main-content">
        <div class="products-card-container">
            <div class="table-header">
                <h2 class="title">Listado de Productos</h2>
                <a href={{ route('aliados.create') }} class="add-product-btn">
                    <i class="fas fa-plus"></i> Añadir nuevo producto
                </a>
            </div>
            
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="buscar productos...">
            </div>

            <div class="table-responsive">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Aliado</th>
                            <th>Descripción</th>
                            <th>Precio base (USD)</th>
                            <th>Descuento (%)</th>
                            <th>Precio final (USD)</th>
                            <th>Estado</th>
                            <th>Fecha de alta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Ejemplo de una fila de datos --}}
                        <tr>
                            <td>1</td>
                            <td>Devant la Riviere</td>
                            <td>Herbert Diaz</td>
                            <td>Prueba de sistemas</td>
                            <td>50.00</td>
                            <td>20%</td>
                            <td>40.00</td>
                            <td>
                                <span class="status-badge status-disponible">Disponible</span>
                            </td>
                            <td>16/06/2025</td>
                            <td class="actions">
                                <button class="btn-icon">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        {{-- Puedes iterar sobre tus productos aquí --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Tus scripts, si los necesitas --}}
@endpush