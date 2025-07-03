@extends('layouts.admin') {{-- Asume que tu layout base se llama admin.blade.php --}}

@section('title', 'Rumbero Extremo - Gestión de Productos')

@section('page_title', 'Gestión de Productos por Aliado')

@section('content')
    <div class="products-section-container">
        <div class="header-actions">
            <h2>Gestión de Productos</h2>
            <button class="add-button" onclick="location.href='{{ route('products.create') }}'">
                <i class="fas fa-plus-circle"></i> Añadir Nuevo Producto
            </button>
        </div>

        {{-- Mensajes de Sesión --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Barra de Búsqueda --}}
        <div class="search-box mb-3">
            <input type="text" id="productSearch"
                placeholder="Buscar por nombre de producto, aliado, descripción o estado...">
        </div>

        {{-- Tabla de Productos --}}
        <div class="table-responsive"> {{-- Contenedor para scroll horizontal en pantallas pequeñas --}}
            <table class="products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Aliado</th>
                        <th>Descripción</th>
                        <th>Precio Base (USD)</th>
                        <th>Descuento (%)</th>
                        <th>Precio Final (USD)</th>
                        <th>Estado</th>
                        <th>Fecha de Alta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td data-label="ID">{{ $product->id }}</td>
                            <td data-label="Producto">{{ $product->name }}</td>
                            <td data-label="Aliado">{{ $product->ally_name }}</td>
                            <td data-label="Descripción">{{ $product->description }}</td>
                            <td data-label="Precio Base (USD)">{{ number_format($product->base_price, 2) }}</td>
                            <td data-label="Descuento (%)">{{ $product->discount_percentage }}%</td>
                            <td data-label="Precio Final (USD)">{{ number_format($product->final_price, 2) }}</td>
                            <td data-label="Estado">
                                <span class="status-badge {{ strtolower($product->status) }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td data-label="Fecha de Alta">{{ $product->created_at->format('d/m/Y') }}</td>
                            <td data-label="Acciones" class="actions-cell">
                                <button class="action-button edit-btn"
                                    onclick="location.href='{{ route('products.edit', $product->id) }}'"
                                    title="Editar Producto">
                                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Editar</span>
                                </button>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                    style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-button delete-btn" title="Eliminar Producto"
                                        onclick="return confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.');">
                                        <i class="fas fa-trash-alt"></i> <span class="d-none d-md-inline">Eliminar</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No hay productos registrados en este momento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div> {{-- Fin .table-responsive --}}
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Highlight active sidebar link (assuming 'products' is the base route)
            const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
            sidebarLinks.forEach(link => {
                link.classList.remove('active');
                // Check if the current URL or the link's href contains '/admin/products'
                if (window.location.pathname.includes('/admin/products') || (link.getAttribute('href') &&
                        link.getAttribute('href').includes('/admin/products'))) {
                    link.classList.add('active');
                }
            });

            // Client-side search functionality
            const searchInput = document.getElementById('productSearch');
            // Get all rows, including the "no products" message initially, then filter.
            const tableBody = document.querySelector('.products-table tbody');
            let productRows = Array.from(tableBody.querySelectorAll('tr')); // Get all rows initially

            // Function to filter rows
            const filterProducts = () => {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let resultsFound = false;

                // Loop through all *original* product rows (excluding the "no products" message if it was initially present)
                productRows.filter(row => !row.classList.contains('text-center')).forEach(row => {
                    const productName = row.querySelector('td[data-label="Producto"]').textContent
                        .toLowerCase();
                    const alliedName = row.querySelector('td[data-label="Aliado"]').textContent
                        .toLowerCase();
                    const productDescription = row.querySelector('td[data-label="Descripción"]')
                        .textContent.toLowerCase();
                    const productStatus = row.querySelector('td[data-label="Estado"]').textContent
                        .toLowerCase();

                    if (
                        productName.includes(searchTerm) ||
                        alliedName.includes(searchTerm) ||
                        productDescription.includes(searchTerm) ||
                        productStatus.includes(searchTerm)
                    ) {
                        row.style.display = ''; // Show row
                        resultsFound = true;
                    } else {
                        row.style.display = 'none'; // Hide row
                    }
                });

                // Handle "No hay productos" message based on search results
                let noRecordsMessage = tableBody.querySelector('.text-center');
                if (!noRecordsMessage) {
                    // Create it if it doesn't exist (e.g., if there were products initially)
                    noRecordsMessage = document.createElement('tr');
                    const td = document.createElement('td');
                    td.colSpan = 10;
                    td.classList.add('text-center');
                    noRecordsMessage.appendChild(td);
                    tableBody.appendChild(noRecordsMessage);
                }

                if (!resultsFound && searchTerm !== '') {
                    // No results found for the search term
                    noRecordsMessage.style.display = '';
                    noRecordsMessage.textContent = 'No se encontraron productos que coincidan con la búsqueda.';
                } else if (!resultsFound && searchTerm === '' && productRows.filter(row => !row.classList
                        .contains('text-center')).length === 0) {
                    // No products at all (initial state or after deleting all)
                    noRecordsMessage.style.display = '';
                    noRecordsMessage.textContent = 'No hay productos registrados en este momento.';
                } else {
                    // Results found or search cleared and products exist
                    noRecordsMessage.style.display = 'none';
                }
            };

            // Initial filter when page loads (useful if search input has pre-filled value)
            filterProducts();

            // Event listener for search input
            searchInput.addEventListener('keyup', filterProducts);
        });
    </script>
@endsection
