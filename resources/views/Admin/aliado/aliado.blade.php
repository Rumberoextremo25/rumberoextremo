@extends('layouts.admin') {{-- Asume que tu layout base se llama admin.blade.php --}}

@section('title', 'Gestión de Aliados')
@section('page_title', 'Gestión de Aliados')

@section('content')
    <div class="allies-management-container">
        <div class="header-actions">
            <h2>Gestión de Aliados</h2>
            <button class="add-ally-btn" onclick="location.href='{{ route('create') }}'">
                <i class="fas fa-plus-circle"></i> Añadir Nuevo Aliado
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
        <div class="search-bar">
            <input type="text" id="allySearch"
                placeholder="Buscar por nombre, RIF, categoría, contacto, email, teléfono o estado...">
        </div>

        {{-- Tabla de Aliados --}}
        <div class="table-responsive"> {{-- Contenedor para scroll horizontal en pantallas pequeñas --}}
            <table class="allies-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>RIF</th>
                        <th>Categoría</th>
                        <th>Contacto</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($allies as $ally)
                        <tr>
                            <td data-label="ID">{{ $ally->id }}</td>
                            <td data-label="Nombre">{{ $ally->company_name }}</td>
                            <td data-label="RIF">{{ $ally->company_rif }}</td>
                            <td data-label="Categoría">{{ $ally->service_category }}</td>
                            <td data-label="Contacto">{{ $ally->contact_person_name }}</td>
                            <td data-label="Email">{{ $ally->contact_email }}</td>
                            <td data-label="Teléfono">{{ $ally->contact_phone }}
                                {{ $ally->contact_phone_alt ? ' / ' . $ally->contact_phone_alt : '' }}</td>
                            <td data-label="Estado">
                                <span class="status-badge status-{{ strtolower($ally->status) }}">
                                    {{ ucfirst($ally->status) }}
                                </span>
                            </td>
                            <td data-label="Registro">{{ $ally->registered_at->format('d/m/Y') }}</td>
                            <td data-label="Acciones" class="actions">
                                <a href="{{ route('aliado.edit', $ally->id) }}" class="btn-icon edit-btn"
                                    title="Editar Aliado">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('aliado.destroy', $ally->id) }}" method="POST"
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
                            <td colspan="10" class="no-records-message">No hay aliados registrados en este momento.</td>
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
            // Lógica para la barra lateral (si aplica)
            const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
            sidebarLinks.forEach(link => {
                // Eliminar clase 'active' de todos los enlaces al inicio
                link.classList.remove('active');
                // Si la URL actual contiene '/admin/allies', activa el enlace correspondiente
                if (window.location.pathname.includes('/admin/allies')) {
                    // Aquí podrías necesitar una lógica más específica si tienes múltiples enlaces de aliados
                    // Por ejemplo: if (link.href.includes('/admin/allies')) { link.classList.add('active'); }
                    // Para este ejemplo, simplemente añadimos la clase si la ruta base coincide
                    if (link.getAttribute('href') && link.getAttribute('href').includes('/admin/allies')) {
                        link.classList.add('active');
                    }
                }
            });

            // Lógica de búsqueda en la tabla
            const searchInput = document.getElementById('allySearch');
            const tableBody = document.querySelector('.allies-table tbody');
            // Obtener todas las filas de aliados, excluyendo el mensaje de "no records" si existe
            const allyRows = Array.from(tableBody.querySelectorAll('tr:not(.no-records-message)'));

            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let resultsFound = false;

                // Si no hay filas de aliados desde el principio, no hacer nada con la búsqueda
                if (allyRows.length === 0 && tableBody.querySelector('.no-records-message')) {
                    // Ya hay un mensaje de "no hay registros", no necesitamos buscar en filas vacías.
                    return;
                }

                allyRows.forEach(row => {
                    // Obtener el texto de cada celda relevante para la búsqueda
                    const rowText = Array.from(row.querySelectorAll('td'))
                        .map(td => td.textContent.toLowerCase())
                        .join(' '); // Unir todo el texto de la fila en una sola cadena

                    if (rowText.includes(searchTerm)) {
                        row.style.display = ''; // Mostrar la fila
                        resultsFound = true;
                    } else {
                        row.style.display = 'none'; // Ocultar la fila
                    }
                });

                // Manejo del mensaje "No se encontraron resultados"
                let noResultsRow = tableBody.querySelector('.no-records-message');
                if (!noResultsRow) {
                    // Si no existe, crear la fila de mensaje
                    noResultsRow = document.createElement('tr');
                    noResultsRow.classList.add('no-records-message');
                    const td = document.createElement('td');
                    td.setAttribute('colspan',
                        '10'); // Asegúrate de que el colspan sea el número correcto de columnas
                    noResultsRow.appendChild(td);
                    tableBody.appendChild(noResultsRow);
                }

                if (!resultsFound && searchTerm !== '') {
                    // Si no hay resultados y el campo de búsqueda no está vacío
                    noResultsRow.style.display = '';
                    noResultsRow.querySelector('td').textContent =
                        'No se encontraron aliados que coincidan con la búsqueda.';
                } else if (!resultsFound && searchTerm === '' && allyRows.length === 0) {
                    // Si no hay resultados, el campo de búsqueda está vacío y no hay aliados en absoluto
                    noResultsRow.style.display = '';
                    noResultsRow.querySelector('td').textContent =
                        'No hay aliados registrados en este momento.';
                } else {
                    // Si hay resultados, o si el campo de búsqueda está vacío y sí hay aliados
                    noResultsRow.style.display = 'none';
                }
            });
        });
    </script>
@endsection
