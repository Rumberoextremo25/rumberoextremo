@extends('layouts.admin')

@section('page_title_toolbar', 'Archivos de Pagos Generados')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/archivos.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
    <div class="main-content archivos-container">
        {{-- Header --}}
        <div class="page-header">
            <h1>
                <span class="text-gray-900">Archivos de</span>
                <span class="text-purple">Pagos Generados</span>
            </h1>
            <div class="header-actions">
                <a href="{{ route('admin.payouts.pendientes') }}" class="btn-primary">
                    <i class="fas fa-plus-circle"></i> Generar Nuevo Archivo
                </a>
            </div>
        </div>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="close-alert">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="close-alert">&times;</button>
            </div>
        @endif

        {{-- Estadísticas Rápidas --}}
        @php
            $totalArchivos = count($archivos);
            $totalSizeKB = collect($archivos)->sum('tamaño') / 1024;
            $archivosBNC = collect($archivos)
                ->filter(function ($a) {
                    return str_contains($a['nombre'], 'bnc');
                })
                ->count();
            $ultimaGeneracion = !empty($archivos) ? $archivos[0]['fecha_modificacion'] ?? null : null;
        @endphp

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-export"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Archivos</div>
                    <div class="stat-value">{{ $totalArchivos }}</div>
                    <div class="stat-sub">{{ $archivosBNC }} archivos BNC</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Espacio Ocupado</div>
                    <div class="stat-value">{{ number_format($totalSizeKB / 1024, 2) }} MB</div>
                    <div class="stat-sub">{{ number_format($totalSizeKB, 0) }} KB</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Última Generación</div>
                    <div class="stat-value">
                        @if ($ultimaGeneracion)
                            {{ \Carbon\Carbon::parse($ultimaGeneracion)->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="stat-sub">
                        @if ($ultimaGeneracion)
                            {{ \Carbon\Carbon::parse($ultimaGeneracion)->format('H:i:s') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="filtros-section">
            <div class="filtros-titulo">
                <i class="fas fa-filter"></i>
                <span>Filtrar Archivos</span>
            </div>
            <form action="{{ route('admin.payouts.archivos') }}" method="GET" class="filtros-grid">
                <div class="filtro-group">
                    <label for="tipo">Tipo de Archivo</label>
                    <select name="tipo" id="tipo" class="form-control">
                        <option value="">Todos</option>
                        <option value="bnc" {{ request('tipo') == 'bnc' ? 'selected' : '' }}>Archivos BNC</option>
                        <option value="reporte" {{ request('tipo') == 'reporte' ? 'selected' : '' }}>Reportes</option>
                        <option value="comprobante" {{ request('tipo') == 'comprobante' ? 'selected' : '' }}>Comprobantes
                        </option>
                    </select>
                </div>
                <div class="filtro-group">
                    <label for="busqueda">Buscar por nombre</label>
                    <input type="text" name="busqueda" id="busqueda" class="form-control"
                        placeholder="Nombre del archivo..." value="{{ request('busqueda') }}">
                </div>
                <div class="filtro-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" name="fecha" id="fecha" class="form-control"
                        value="{{ request('fecha') }}">
                </div>
                <div class="filtro-actions">
                    <button type="submit" class="btn-filtro primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.payouts.archivos') }}" class="btn-filtro secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabla de Archivos --}}
        <div class="archivos-table-container">
            <div class="table-header">
                <h3>
                    <i class="fas fa-list"></i>
                    Archivos Generados
                </h3>
                <span class="badge">{{ $totalArchivos }} archivo(s)</span>
            </div>

            @if (empty($archivos))
                <div class="no-data-message">
                    <i class="fas fa-folder-open icon"></i>
                    <h3>No hay archivos generados</h3>
                    <p>Los archivos aparecerán aquí cuando generes pagos BNC o reportes.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="archivos-table">
                        <thead>
                            <tr>
                                <th>Archivo</th>
                                <th>Tipo</th>
                                <th>Tamaño</th>
                                <th>Fecha Modificación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($archivos as $archivo)
                                @php
                                    $extension = pathinfo($archivo['nombre'], PATHINFO_EXTENSION);
                                    $tipo = match (true) {
                                        str_contains($archivo['nombre'], 'bnc') || $extension === 'txt' => 'bnc',
                                        $extension === 'pdf' => 'reporte',
                                        in_array($extension, ['jpg', 'jpeg', 'png']) => 'comprobante',
                                        default => 'desconocido',
                                    };
                                    $tipoTexto = match ($tipo) {
                                        'bnc' => 'BNC',
                                        'reporte' => 'Reporte',
                                        'comprobante' => 'Comprobante',
                                        default => 'Otro',
                                    };
                                    $tipoIcono = match ($tipo) {
                                        'bnc' => 'fas fa-file-csv',
                                        'reporte' => 'fas fa-file-pdf',
                                        'comprobante' => 'fas fa-file-image',
                                        default => 'fas fa-file',
                                    };
                                    $tipoClase = match ($tipo) {
                                        'bnc' => 'bnc',
                                        'reporte' => 'reporte',
                                        'comprobante' => 'comprobante',
                                        default => '',
                                    };

                                    $sizeKB = $archivo['tamaño'] / 1024;
                                    $sizeClass = $sizeKB < 100 ? 'small' : ($sizeKB < 1024 ? 'medium' : 'large');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="file-info">
                                            <div class="file-icon {{ $tipoClase }}">
                                                <i class="{{ $tipoIcono }}"></i>
                                            </div>
                                            <div class="file-details">
                                                <div class="file-name">{{ $archivo['nombre'] }}</div>
                                                <div class="file-meta">
                                                    <span>
                                                        <i class="fas fa-folder"></i>
                                                        {{ dirname($archivo['ruta']) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $tipo }}">{{ $tipoTexto }}</span>
                                    </td>
                                    <td>
                                        <span class="file-size {{ $sizeClass }}">
                                            @if ($sizeKB < 1)
                                                {{ number_format($archivo['tamaño'], 0) }} B
                                            @elseif($sizeKB < 1024)
                                                {{ number_format($sizeKB, 2) }} KB
                                            @else
                                                {{ number_format($sizeKB / 1024, 2) }} MB
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($archivo['fecha_modificacion'])->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @if ($tipo === 'bnc')
                                                <a href="/ruta/directa/{{ $parametro }}">Enlace</a>
                                                class="btn-icon download" title="Descargar archivo BNC">
                                                <i class="fas fa-download"></i>
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $archivo['ruta']) }}"
                                                    class="btn-icon download" title="Descargar archivo" download>
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif

                                            <button class="btn-icon info" title="Ver detalles"
                                                onclick="verDetallesArchivo({{ json_encode($archivo) }}, '{{ $tipo }}', '{{ $tipoTexto }}', '{{ $sizeClass }}')">
                                                <i class="fas fa-info-circle"></i>
                                            </button>

                                            <button class="btn-icon delete" title="Eliminar archivo"
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
            @endif
        </div>
    </div>

    {{-- Modal de Detalles del Archivo --}}
    <div id="detallesModal" class="modal-overlay hidden">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-file-alt"></i>
                    Detalles del Archivo
                </h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="file-details-grid">
                    <div class="detail-item full-width">
                        <label>Nombre del Archivo</label>
                        <span id="detalle-nombre"></span>
                    </div>
                    <div class="detail-item">
                        <label>Tipo</label>
                        <span id="detalle-tipo" class="badge"></span>
                    </div>
                    <div class="detail-item">
                        <label>Tamaño</label>
                        <span id="detalle-tamano" class="file-size"></span>
                    </div>
                    <div class="detail-item">
                        <label>Fecha de Creación</label>
                        <span id="detalle-fecha-creacion"></span>
                    </div>
                    <div class="detail-item">
                        <label>Fecha Modificación</label>
                        <span id="detalle-fecha-mod"></span>
                    </div>
                    <div class="detail-item full-width">
                        <label>Ruta Completa</label>
                        <code id="detalle-ruta" class="file-path"></code>
                    </div>
                    <div class="detail-item full-width">
                        <label>Vista Previa</label>
                        <div id="detalle-preview"
                            style="background: #f3f4f6; padding: 1rem; border-radius: 0.5rem; text-align: center;">
                            <p class="text-muted">Previsualización no disponible</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cerrar</button>
                <a href="#" id="btn-descargar-detalle" class="btn btn-primary" download>
                    <i class="fas fa-download"></i> Descargar
                </a>
            </div>
        </div>
    </div>

    {{-- Modal de Confirmación de Eliminación --}}
    <div id="confirmarModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                    Confirmar Eliminación
                </h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar el archivo:</p>
                <p><strong id="eliminar-nombre-archivo"></strong>?</p>
                <p class="text-muted" style="margin-top: 1rem;">
                    <i class="fas fa-info-circle"></i>
                    Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalEliminar()">Cancelar</button>
                <form id="eliminar-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-primary" style="background: #ef4444;">
                        <i class="fas fa-trash"></i> Eliminar Archivo
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cerrar alertas
            document.querySelectorAll('.close-alert').forEach(button => {
                button.addEventListener('click', function() {
                    this.parentElement.style.display = 'none';
                });
            });

            // Inicializar modales
            const detallesModal = document.getElementById('detallesModal');
            const confirmarModal = document.getElementById('confirmarModal');
            const closeButtons = document.querySelectorAll('.close-modal-btn');

            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    detallesModal.classList.add('hidden');
                    confirmarModal.classList.add('hidden');
                });
            });

            window.addEventListener('click', function(event) {
                if (event.target === detallesModal) {
                    detallesModal.classList.add('hidden');
                }
                if (event.target === confirmarModal) {
                    confirmarModal.classList.add('hidden');
                }
            });
        });

        function verDetallesArchivo(archivo, tipo, tipoTexto, sizeClass) {
            const modal = document.getElementById('detallesModal');
            const btnDescargar = document.getElementById('btn-descargar-detalle');

            // Configurar enlace de descarga
            if (tipo === 'bnc') {
                // Construir la URL manualmente sin usar route()
                btnDescargar.href = `/admin/payouts/descargar-bnc/${encodeURIComponent(archivo.nombre)}`;
            } else {
                btnDescargar.href = `/storage/${archivo.ruta}`;
            }

            // Llenar datos
            document.getElementById('detalle-nombre').textContent = archivo.nombre;

            const tipoSpan = document.getElementById('detalle-tipo');
            tipoSpan.textContent = tipoTexto;
            tipoSpan.className = `badge badge-${tipo}`;

            const tamanoSpan = document.getElementById('detalle-tamano');
            const sizeKB = archivo.tamaño / 1024;
            if (sizeKB < 1) {
                tamanoSpan.textContent = archivo.tamaño + ' B';
            } else if (sizeKB < 1024) {
                tamanoSpan.textContent = sizeKB.toFixed(2) + ' KB';
            } else {
                tamanoSpan.textContent = (sizeKB / 1024).toFixed(2) + ' MB';
            }
            tamanoSpan.className = `file-size ${sizeClass}`;

            // Fechas (simuladas ya que no tenemos estos datos)
            document.getElementById('detalle-fecha-creacion').textContent = archivo.fecha_modificacion;
            document.getElementById('detalle-fecha-mod').textContent = archivo.fecha_modificacion;

            document.getElementById('detalle-ruta').textContent = archivo.ruta;

            // Preview simple
            const previewDiv = document.getElementById('detalle-preview');
            if (tipo === 'reporte' || tipo === 'comprobante') {
                previewDiv.innerHTML = `
                <i class="fas fa-file-pdf" style="font-size: 3rem; color: #ef4444; margin-bottom: 1rem;"></i>
                <p>Archivo PDF - Vista previa no disponible</p>
            `;
            } else if (tipo === 'bnc') {
                previewDiv.innerHTML = `
                <i class="fas fa-file-csv" style="font-size: 3rem; color: #8a2be2; margin-bottom: 1rem;"></i>
                <p>Archivo de texto BNC - Contiene datos de pagos</p>
            `;
            }

            modal.classList.remove('hidden');
        }

        function confirmarEliminacion(nombreArchivo) {
            const modal = document.getElementById('confirmarModal');
            const form = document.getElementById('eliminar-form');

            document.getElementById('eliminar-nombre-archivo').textContent = nombreArchivo;
            form.action = `/admin/payouts/eliminar-archivo/${encodeURIComponent(nombreArchivo)}`;

            modal.classList.remove('hidden');
        }

        function cerrarModal() {
            document.getElementById('detallesModal').classList.add('hidden');
        }

        function cerrarModalEliminar() {
            document.getElementById('confirmarModal').classList.add('hidden');
        }
    </script>
@endpush
