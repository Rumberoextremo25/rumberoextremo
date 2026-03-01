@extends('layouts.admin')

@section('page_title_toolbar', 'Lotes de Pagos')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/lotes.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
    <div class="main-content lotes-container">
        {{-- Header --}}
        <div class="page-header">
            <h1>
                <span class="text-gray-900">Lotes de</span>
                <span class="text-purple">Pagos</span>
            </h1>
            <div class="header-actions">
                <a href="{{ route('admin.payouts.pendientes') }}" class="btn-primary">
                    <i class="fas fa-plus-circle"></i> Nuevo Lote
                </a>
                <a href="{{ route('admin.payouts.archivos') }}" class="btn-secondary">
                    <i class="fas fa-file-export"></i> Ver Archivos
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
            $totalLotes = count($lotes);
            $lotesProcesados = collect($lotes)
                ->filter(function ($l) {
                    return $l['estado'] === 'procesado';
                })
                ->count();
            $lotesPendientes = collect($lotes)
                ->filter(function ($l) {
                    return $l['estado'] === 'pendiente';
                })
                ->count();
            $totalPagos = collect($lotes)->sum('cantidad_pagos');
            $totalMonto = collect($lotes)->sum('monto_total');
        @endphp

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Lotes</div>
                    <div class="stat-value">{{ $totalLotes }}</div>
                    <div class="stat-sub">{{ $lotesProcesados }} procesados</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Pagos Incluidos</div>
                    <div class="stat-value">{{ number_format($totalPagos) }}</div>
                    <div class="stat-sub">en todos los lotes</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Monto Total</div>
                    <div class="stat-value">Bs. {{ number_format($totalMonto, 2, ',', '.') }}</div>
                    <div class="stat-sub">suma de lotes</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Pendientes</div>
                    <div class="stat-value">{{ $lotesPendientes }}</div>
                    <div class="stat-sub">por procesar</div>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="filtros-section">
            <div class="filtros-titulo">
                <i class="fas fa-filter"></i>
                <span>Filtrar Lotes</span>
            </div>
            <form action="{{ route('admin.payouts.lotes') }}" method="GET" class="filtros-grid">
                <div class="filtro-group">
                    <label for="estado">Estado</label>
                    <select name="estado" id="estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="procesado" {{ request('estado') == 'procesado' ? 'selected' : '' }}>Procesados
                        </option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendientes
                        </option>
                        <option value="error" {{ request('estado') == 'error' ? 'selected' : '' }}>Con Error</option>
                    </select>
                </div>
                <div class="filtro-group">
                    <label for="fecha_desde">Fecha Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" class="form-control"
                        value="{{ request('fecha_desde', now()->subMonth()->format('Y-m-d')) }}">
                </div>
                <div class="filtro-group">
                    <label for="fecha_hasta">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control"
                        value="{{ request('fecha_hasta', now()->format('Y-m-d')) }}">
                </div>
                <div class="filtro-actions">
                    <button type="submit" class="btn-filtro primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.payouts.lotes') }}" class="btn-filtro secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        {{-- Vista de Tarjetas --}}
        <div class="lotes-grid">
            @forelse($lotes as $lote)
                <div class="lote-card {{ $lote['estado'] }}">
                    <div class="lote-header">
                        <div class="lote-titulo">
                            <div class="lote-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div>
                                <div class="lote-nombre">{{ $lote['nombre'] }}</div>
                                <div class="lote-fecha">{{ \Carbon\Carbon::parse($lote['fecha'])->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                        <span class="lote-badge {{ $lote['estado'] }}">
                            {{ ucfirst($lote['estado']) }}
                        </span>
                    </div>

                    <div class="lote-body">
                        <div class="lote-info">
                            <div class="lote-info-row">
                                <span class="info-label">Pagos Incluidos</span>
                                <span class="info-value">{{ number_format($lote['cantidad_pagos']) }}</span>
                            </div>
                            <div class="lote-info-row">
                                <span class="info-label">Monto Total</span>
                                <span class="info-value success">Bs.
                                    {{ number_format($lote['monto_total'], 2, ',', '.') }}</span>
                            </div>
                            <div class="lote-info-row">
                                <span class="info-label">Procesados</span>
                                <span class="info-value">{{ number_format($lote['procesados'] ?? 0) }}</span>
                            </div>
                            <div class="lote-info-row">
                                <span class="info-label">Con Error</span>
                                <span class="info-value danger">{{ number_format($lote['errores'] ?? 0) }}</span>
                            </div>
                        </div>

                        @if (($lote['procesados'] ?? 0) > 0)
                            @php
                                $porcentaje = ($lote['procesados'] / $lote['cantidad_pagos']) * 100;
                            @endphp
                            <div class="lote-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $porcentaje }}%"></div>
                                </div>
                                <div class="progress-text">{{ number_format($porcentaje, 1) }}% completado</div>
                            </div>
                        @endif
                    </div>

                    <div class="lote-footer">
                        <div class="lote-info">
                            <span class="info-label">Generado por</span>
                            <span class="info-value">{{ $lote['generado_por'] ?? 'Sistema' }}</span>
                        </div>
                        <div class="lote-actions">
                            <button class="btn-icon info" title="Ver detalles"
                                onclick="verDetallesLote('{{ $lote['id'] }}')">
                                <i class="fas fa-eye"></i>
                            </button>

                            @if ($lote['estado'] === 'procesado')
                                <a href="/admin/payouts/descargar-bnc/{{ urlencode($lote['archivo']) }}"
                                    class="btn-icon download" title="Descargar archivo">
                                    <i class="fas fa-download"></i>
                                </a>
                            @endif

                            @if ($lote['estado'] === 'pendiente')
                                <button class="btn-icon process" title="Procesar lote"
                                    onclick="procesarLote('{{ $lote['id'] }}')">
                                    <i class="fas fa-play"></i>
                                </button>
                            @endif

                            <button class="btn-icon delete" title="Eliminar lote"
                                onclick="confirmarEliminacion('{{ $lote['id'] }}', '{{ $lote['nombre'] }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="no-data-message" style="grid-column: 1/-1;">
                    <i class="fas fa-layer-group icon"></i>
                    <h3>No hay lotes de pagos</h3>
                    <p>Los lotes aparecerán aquí cuando generes archivos de pagos BNC.</p>
                </div>
            @endforelse
        </div>

        {{-- Vista de Tabla (Alternativa) --}}
        @if (!empty($lotes))
            <div class="lotes-table-container">
                <div class="table-header">
                    <h3>
                        <i class="fas fa-table"></i>
                        Vista Detallada
                    </h3>
                    <span class="badge">{{ $totalLotes }} lotes</span>
                </div>

                <div class="table-responsive">
                    <table class="lotes-table">
                        <thead>
                            <tr>
                                <th>ID Lote</th>
                                <th>Nombre</th>
                                <th>Fecha Generación</th>
                                <th>Pagos</th>
                                <th>Monto Total</th>
                                <th>Procesados</th>
                                <th>Errores</th>
                                <th>Estado</th>
                                <th>Generado Por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lotes as $lote)
                                <tr>
                                    <td><strong>{{ $lote['id'] }}</strong></td>
                                    <td>{{ $lote['nombre'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($lote['fecha'])->format('d/m/Y H:i') }}</td>
                                    <td class="text-success">{{ number_format($lote['cantidad_pagos']) }}</td>
                                    <td class="text-success">Bs. {{ number_format($lote['monto_total'], 2, ',', '.') }}
                                    </td>
                                    <td>{{ number_format($lote['procesados'] ?? 0) }}</td>
                                    <td class="text-danger">{{ number_format($lote['errores'] ?? 0) }}</td>
                                    <td>
                                        <span class="lote-table-badge {{ $lote['estado'] }}">
                                            {{ ucfirst($lote['estado']) }}
                                        </span>
                                    </td>
                                    <td>{{ $lote['generado_por'] ?? 'Sistema' }}</td>
                                    <td>
                                        <div class="action-buttons" style="display: flex; gap: 0.25rem;">
                                            <button class="btn-icon info"
                                                onclick="verDetallesLote('{{ $lote['id'] }}')" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            @if ($lote['estado'] === 'procesado' && !empty($lote['archivo']))
                                                <a href="/admin/payouts/descargar-bnc/{{ urlencode($lote['archivo']) }}"
                                                    class="btn-icon download" title="Descargar archivo">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif

                                            @if ($lote['estado'] === 'pendiente')
                                                <button class="btn-icon process"
                                                    onclick="procesarLote('{{ $lote['id'] }}')" title="Procesar lote">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            @endif

                                            <button class="btn-icon delete"
                                                onclick="confirmarEliminacion('{{ $lote['id'] }}', '{{ $lote['nombre'] }}')"
                                                title="Eliminar lote">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal de Detalles del Lote --}}
    <div id="detallesModal" class="modal-overlay hidden">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-layer-group"></i>
                    Detalles del Lote
                </h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div id="detalle-contenido">
                    <p class="text-muted">Cargando detalles...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cerrar</button>
                <a href="#" id="btn-descargar-lote" class="btn btn-primary" style="display: none;">
                    <i class="fas fa-download"></i> Descargar Archivo
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
                <p>¿Estás seguro de que deseas eliminar el lote:</p>
                <p><strong id="eliminar-nombre-lote"></strong>?</p>
                <p class="text-muted" style="margin-top: 1rem;">
                    <i class="fas fa-info-circle"></i>
                    Esta acción no se puede deshacer y eliminará todos los registros asociados.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalEliminar()">Cancelar</button>
                <form id="eliminar-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-primary" style="background: #ef4444;">
                        <i class="fas fa-trash"></i> Eliminar Lote
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

        function verDetallesLote(loteId) {
            const modal = document.getElementById('detallesModal');
            const contenido = document.getElementById('detalle-contenido');
            const btnDescargar = document.getElementById('btn-descargar-lote');

            // Simular carga de detalles (esto debería ser una llamada AJAX)
            contenido.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #8a2be2;"></i>
                <p class="text-muted">Cargando detalles del lote...</p>
            </div>
        `;

            modal.classList.remove('hidden');
            btnDescargar.style.display = 'none';

            // Simular carga (reemplazar con llamada real)
            setTimeout(() => {
                contenido.innerHTML = `
                <div class="file-details-grid">
                    <div class="detail-item full-width">
                        <label>ID Lote</label>
                        <span>${loteId}</span>
                    </div>
                    <div class="detail-item">
                        <label>Fecha Generación</label>
                        <span>{{ now()->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Total Pagos</label>
                        <span class="text-success">15 pagos</span>
                    </div>
                    <div class="detail-item">
                        <label>Monto Total</label>
                        <span class="text-success">Bs. 1.234.567,89</span>
                    </div>
                    <div class="detail-item full-width">
                        <label>Pagos Incluidos</label>
                        <div class="pagos-list">
                            ${generarListaPagos()}
                        </div>
                    </div>
                </div>
            `;
            }, 1000);
        }

        function generarListaPagos() {
            const pagos = [{
                    id: 1,
                    aliado: 'Restaurante La Esquina',
                    monto: 500000,
                    estado: 'completed'
                },
                {
                    id: 2,
                    aliado: 'Tienda Deportiva El Gol',
                    monto: 750000,
                    estado: 'completed'
                },
                {
                    id: 3,
                    aliado: 'Spa Relajación Total',
                    monto: 300000,
                    estado: 'processing'
                },
            ];

            return pagos.map(p => `
            <div class="pago-item">
                <div class="pago-info">
                    <span class="pago-id">#${p.id} - ${p.aliado}</span>
                </div>
                <div>
                    <span class="pago-monto">Bs. ${p.monto.toLocaleString('es-ES')}</span>
                    <span class="pago-estado badge-${p.estado}">${p.estado}</span>
                </div>
            </div>
        `).join('');
        }

        function procesarLote(loteId) {
            Swal.fire({
                title: 'Procesar Lote',
                text: '¿Estás seguro de que quieres procesar este lote?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#8a2be2',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Procesar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí iría la llamada para procesar el lote
                    Swal.fire({
                        title: 'Procesando',
                        text: 'El lote está siendo procesado...',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
        }

        function confirmarEliminacion(loteId, nombreLote) {
            const modal = document.getElementById('confirmarModal');
            const form = document.getElementById('eliminar-form');

            document.getElementById('eliminar-nombre-lote').textContent = nombreLote;
            form.action = `/admin/payouts/lotes/${loteId}`;

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
