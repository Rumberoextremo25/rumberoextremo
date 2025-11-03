@extends('layouts.admin')

@section('page_title_toolbar', 'Archivos de Pagos Generados')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/payout.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <h2 class="page-title text-gray-900">
            <span class="text-gray-900">Archivos de</span>
            <span class="text-purple">Pagos Generados</span>
        </h2>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="close-alert">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="close-alert">&times;</button>
            </div>
        @endif

        {{-- Estadísticas Rápidas --}}
        <div class="stats-grid mb-6">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-file-export"></i>
                </div>
                <div class="stat-value">{{ $archivos->total() }}</div>
                <div class="stat-label">Total Archivos</div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-file-csv"></i>
                </div>
                <div class="stat-value">{{ $archivos->where('tipo', 'bnc')->count() }}</div>
                <div class="stat-label">Archivos BNC</div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="stat-value">{{ $archivos->where('tipo', 'reporte')->count() }}</div>
                <div class="stat-label">Reportes PDF</div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="stat-value">{{ number_format($archivos->sum('size_kb') / 1024, 2) }} MB</div>
                <div class="stat-label">Espacio Usado</div>
            </div>
        </div>

        {{-- Filtros y Búsqueda --}}
        <div class="stats-card mb-4">
            <div class="filters-section">
                <form action="{{ route('admin.payouts.archivos') }}" method="GET" class="filters-form">
                    <div class="filters-grid">
                        <div class="form-group">
                            <label for="tipo_archivo">Tipo de Archivo:</label>
                            <select name="tipo_archivo" id="tipo_archivo" class="form-control">
                                <option value="">Todos los tipos</option>
                                <option value="bnc" {{ request('tipo_archivo') == 'bnc' ? 'selected' : '' }}>Archivos BNC</option>
                                <option value="reporte" {{ request('tipo_archivo') == 'reporte' ? 'selected' : '' }}>Reportes</option>
                                <option value="comprobante" {{ request('tipo_archivo') == 'comprobante' ? 'selected' : '' }}>Comprobantes</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fecha_inicio">Fecha Inicio:</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" 
                                   value="{{ request('fecha_inicio', date('Y-m-01')) }}">
                        </div>

                        <div class="form-group">
                            <label for="fecha_fin">Fecha Fin:</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" 
                                   value="{{ request('fecha_fin', date('Y-m-d')) }}">
                        </div>

                        <div class="form-group">
                            <label for="busqueda">Buscar:</label>
                            <input type="text" name="busqueda" id="busqueda" class="form-control" 
                                   placeholder="Nombre de archivo..." value="{{ request('busqueda') }}">
                        </div>

                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="filter-actions">
                                <button type="submit" class="action-button">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.payouts.archivos') }}" class="action-button secondary">
                                    <i class="fas fa-redo"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Lista de Archivos --}}
        <div class="stats-card">
            <div class="card-header">
                <h3>Archivos Generados</h3>
                <div class="card-actions">
                    <span class="text-muted">Mostrando {{ $archivos->count() }} de {{ $archivos->total() }} archivos</span>
                </div>
            </div>

            @if($archivos->isEmpty())
                <div class="no-data-message">
                    <i class="fas fa-folder-open"></i>
                    <h4>No se encontraron archivos</h4>
                    <p>No hay archivos que coincidan con los criterios de búsqueda.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="payouts-table">
                        <thead>
                            <tr>
                                <th>Nombre del Archivo</th>
                                <th>Tipo</th>
                                <th>Tamaño</th>
                                <th>Pagos Incluidos</th>
                                <th>Monto Total</th>
                                <th>Fecha Generación</th>
                                <th>Generado Por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($archivos as $archivo)
                                <tr>
                                    <td>
                                        <div class="file-info">
                                            <div class="file-icon">
                                                @if($archivo['tipo'] === 'bnc')
                                                    <i class="fas fa-file-csv text-success"></i>
                                                @elseif($archivo['tipo'] === 'reporte')
                                                    <i class="fas fa-file-pdf text-danger"></i>
                                                @else
                                                    <i class="fas fa-file text-info"></i>
                                                @endif
                                            </div>
                                            <div class="file-details">
                                                <div class="file-name">{{ $archivo['nombre'] }}</div>
                                                <div class="file-description">{{ $archivo['descripcion'] ?? 'Sin descripción' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $archivo['tipo'] === 'bnc' ? 'success' : ($archivo['tipo'] === 'reporte' ? 'danger' : 'info') }}">
                                            {{ $archivo['tipo'] === 'bnc' ? 'BNC' : ($archivo['tipo'] === 'reporte' ? 'Reporte' : 'Otro') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="file-size">
                                            @if($archivo['size_kb'] < 1024)
                                                {{ $archivo['size_kb'] }} KB
                                            @else
                                                {{ number_format($archivo['size_kb'] / 1024, 2) }} MB
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <span class="payouts-count">{{ $archivo['pagos_incluidos'] ?? 0 }}</span>
                                    </td>
                                    <td class="text-success">
                                        <strong>Bs. {{ number_format($archivo['monto_total'] ?? 0, 2, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <div class="date">{{ \Carbon\Carbon::parse($archivo['fecha_generacion'])->format('d/m/Y') }}</div>
                                            <div class="time">{{ \Carbon\Carbon::parse($archivo['fecha_generacion'])->format('H:i') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-name">{{ $archivo['generado_por']['nombre'] ?? 'Sistema' }}</div>
                                            <div class="user-email">{{ $archivo['generado_por']['email'] ?? '' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @if($archivo['tipo'] === 'bnc')
                                                <a href="{{ route('admin.payouts.descargar-bnc', $archivo['nombre']) }}" 
                                                   class="btn-action btn-download" 
                                                   title="Descargar archivo BNC">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @else
                                                <a href="{{ $archivo['url_descarga'] ?? '#' }}" 
                                                   class="btn-action btn-download" 
                                                   title="Descargar archivo"
                                                   {{ !isset($archivo['url_descarga']) ? 'disabled' : '' }}>
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif

                                            <button class="btn-action btn-info" 
                                                    title="Ver información"
                                                    onclick="mostrarDetallesArchivo({{ json_encode($archivo) }})">
                                                <i class="fas fa-info-circle"></i>
                                            </button>

                                            <button class="btn-action btn-delete" 
                                                    title="Eliminar archivo"
                                                    onclick="confirmarEliminacion('{{ $archivo['nombre'] }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($archivos->hasPages())
                    <div class="pagination-container">
                        {{ $archivos->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- Botón de Generar Nuevo Archivo --}}
        <div class="action-section mt-6">
            <div class="action-buttons-grid">
                <a href="{{ route('admin.payouts.pendientes') }}" class="action-button large">
                    <i class="fas fa-arrow-left"></i> Volver a Pagos Pendientes
                </a>
                
                <button type="button" class="action-button large primary" onclick="generarNuevoArchivo()">
                    <i class="fas fa-plus-circle"></i> Generar Nuevo Archivo BNC
                </button>
            </div>
        </div>
    </div>

    {{-- Modal de Detalles del Archivo --}}
    <div id="detallesModal" class="modal-overlay hidden">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Detalles del Archivo</h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="file-details-grid">
                    <div class="detail-item">
                        <label>Nombre del Archivo:</label>
                        <span id="detail-nombre"></span>
                    </div>
                    <div class="detail-item">
                        <label>Tipo:</label>
                        <span id="detail-tipo"></span>
                    </div>
                    <div class="detail-item">
                        <label>Tamaño:</label>
                        <span id="detail-tamaño"></span>
                    </div>
                    <div class="detail-item">
                        <label>Pagos Incluidos:</label>
                        <span id="detail-pagos"></span>
                    </div>
                    <div class="detail-item">
                        <label>Monto Total:</label>
                        <span id="detail-monto" class="text-success"></span>
                    </div>
                    <div class="detail-item">
                        <label>Fecha de Generación:</label>
                        <span id="detail-fecha"></span>
                    </div>
                    <div class="detail-item">
                        <label>Generado Por:</label>
                        <span id="detail-usuario"></span>
                    </div>
                    <div class="detail-item full-width">
                        <label>Descripción:</label>
                        <p id="detail-descripcion" class="description-text"></p>
                    </div>
                    <div class="detail-item full-width">
                        <label>Ruta del Archivo:</label>
                        <code id="detail-ruta" class="file-path"></code>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-modal-btn">Cerrar</button>
                <button type="button" class="btn confirm-modal-btn" id="btn-descargar-detalle">
                    <i class="fas fa-download"></i> Descargar
                </button>
            </div>
        </div>
    </div>

    {{-- Modal de Confirmación de Eliminación --}}
    <div id="confirmacionEliminarModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmar Eliminación</h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar el archivo <strong id="nombre-archivo-eliminar"></strong>?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-modal-btn">Cancelar</button>
                <form id="form-eliminar-archivo" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar Archivo</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal de detalles
        const detallesModal = document.getElementById('detallesModal');
        const closeModalButtons = document.querySelectorAll('.close-modal-btn, .cancel-modal-btn');
        
        // Modal de eliminación
        const confirmacionEliminarModal = document.getElementById('confirmacionEliminarModal');

        // Cerrar modales
        closeModalButtons.forEach(button => {
            button.addEventListener('click', function() {
                detallesModal.style.display = 'none';
                confirmacionEliminarModal.style.display = 'none';
            });
        });

        // Cerrar modal al hacer click fuera
        window.addEventListener('click', function(event) {
            if (event.target === detallesModal) {
                detallesModal.style.display = 'none';
            }
            if (event.target === confirmacionEliminarModal) {
                confirmacionEliminarModal.style.display = 'none';
            }
        });

        // Cerrar alertas
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    });

    function mostrarDetallesArchivo(archivo) {
        const modal = document.getElementById('detallesModal');
        const btnDescargar = document.getElementById('btn-descargar-detalle');
        
        // Llenar datos en el modal
        document.getElementById('detail-nombre').textContent = archivo.nombre;
        document.getElementById('detail-tipo').innerHTML = `<span class="badge badge-${archivo.tipo === 'bnc' ? 'success' : (archivo.tipo === 'reporte' ? 'danger' : 'info')}">${archivo.tipo === 'bnc' ? 'BNC' : (archivo.tipo === 'reporte' ? 'Reporte' : 'Otro')}</span>`;
        document.getElementById('detail-tamaño').textContent = archivo.size_kb < 1024 ? archivo.size_kb + ' KB' : (archivo.size_kb / 1024).toFixed(2) + ' MB';
        document.getElementById('detail-pagos').textContent = archivo.pagos_incluidos || 0;
        document.getElementById('detail-monto').textContent = 'Bs. ' + (archivo.monto_total || 0).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('detail-fecha').textContent = new Date(archivo.fecha_generacion).toLocaleString('es-ES');
        document.getElementById('detail-usuario').textContent = archivo.generado_por ? archivo.generado_por.nombre + ' (' + archivo.generado_por.email + ')' : 'Sistema';
        document.getElementById('detail-descripcion').textContent = archivo.descripcion || 'Sin descripción';
        document.getElementById('detail-ruta').textContent = archivo.ruta || 'No disponible';
        
        // Configurar botón de descarga
        if (archivo.tipo === 'bnc') {
            btnDescargar.onclick = function() {
                window.location.href = "{{ route('admin.payouts.descargar-bnc', '') }}/" + encodeURIComponent(archivo.nombre);
            };
        } else if (archivo.url_descarga) {
            btnDescargar.onclick = function() {
                window.location.href = archivo.url_descarga;
            };
        } else {
            btnDescargar.disabled = true;
            btnDescargar.title = 'Descarga no disponible';
        }
        
        modal.style.display = 'flex';
    }

    function confirmarEliminacion(nombreArchivo) {
        const modal = document.getElementById('confirmacionEliminarModal');
        const form = document.getElementById('form-eliminar-archivo');
        
        document.getElementById('nombre-archivo-eliminar').textContent = nombreArchivo;
        form.action = "{{ route('admin.payouts.eliminar-archivo', '') }}/" + encodeURIComponent(nombreArchivo);
        
        modal.style.display = 'flex';
    }

    function generarNuevoArchivo() {
        Swal.fire({
            title: 'Generar Nuevo Archivo BNC',
            text: 'Serás redirigido a la página de pagos pendientes para generar un nuevo archivo BNC.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#8a2be2',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.payouts.pendientes') }}";
            }
        });
    }

    // Busqueda en tiempo real (opcional)
    document.getElementById('busqueda')?.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.payouts-table tbody tr');
        
        rows.forEach(row => {
            const fileName = row.querySelector('.file-name').textContent.toLowerCase();
            const description = row.querySelector('.file-description').textContent.toLowerCase();
            
            if (fileName.includes(searchTerm) || description.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
@endpush