@extends('layouts.admin')

@section('page_title_toolbar', 'Lotes de Pagos')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/payout.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <h2 class="page-title text-gray-900">
            <span class="text-gray-900">Lotes de</span>
            <span class="text-purple">Pagos</span>
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

        {{-- Estadísticas de Lotes --}}
        <div class="stats-grid mb-6">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-value">{{ $lotes->total() }}</div>
                <div class="stat-label">Total Lotes</div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value">{{ $lotes->where('estado', 'completado')->count() }}</div>
                <div class="stat-label">Lotes Completados</div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value">{{ $lotes->where('estado', 'procesando')->count() }}</div>
                <div class="stat-label">En Proceso</div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value">{{ $lotes->where('estado', 'fallido')->count() }}</div>
                <div class="stat-label">Lotes Fallidos</div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="stats-card mb-4">
            <form action="{{ route('admin.payouts.lotes') }}" method="GET" class="filters-form">
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <select name="estado" id="estado" class="form-control">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="procesando" {{ request('estado') == 'procesando' ? 'selected' : '' }}>Procesando</option>
                            <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completado</option>
                            <option value="fallido" {{ request('estado') == 'fallido' ? 'selected' : '' }}>Fallido</option>
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
                        <label>&nbsp;</label>
                        <div class="filter-actions">
                            <button type="submit" class="action-button">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.payouts.lotes') }}" class="action-button secondary">
                                <i class="fas fa-redo"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Lista de Lotes --}}
        <div class="stats-card">
            <div class="card-header">
                <h3>Lotes de Pagos</h3>
                <div class="card-actions">
                    <span class="text-muted">Mostrando {{ $lotes->count() }} de {{ $lotes->total() }} lotes</span>
                </div>
            </div>

            @if($lotes->isEmpty())
                <div class="no-data-message">
                    <i class="fas fa-layer-group"></i>
                    <h4>No se encontraron lotes</h4>
                    <p>No hay lotes de pagos que coincidan con los criterios de búsqueda.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="payouts-table">
                        <thead>
                            <tr>
                                <th>ID Lote</th>
                                <th>Descripción</th>
                                <th>Pagos Incluidos</th>
                                <th>Monto Total</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Fecha Procesamiento</th>
                                <th>Creado Por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lotes as $lote)
                                <tr>
                                    <td>
                                        <strong>#{{ $lote['id'] }}</strong>
                                    </td>
                                    <td>
                                        <div class="lote-info">
                                            <div class="lote-descripcion">{{ $lote['descripcion'] }}</div>
                                            <div class="lote-metodo">{{ $lote['metodo_pago'] }} - {{ $lote['banco_destino'] }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $lote['total_pagos'] }}</span>
                                    </td>
                                    <td class="text-success">
                                        <strong>Bs. {{ number_format($lote['monto_total'], 2, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $lote['estado'] }}">
                                            {{ $lote['estado'] }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($lote['fecha_creacion'])->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        @if($lote['fecha_procesamiento'])
                                            {{ \Carbon\Carbon::parse($lote['fecha_procesamiento'])->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-name">{{ $lote['creado_por']['nombre'] }}</div>
                                            <div class="user-email">{{ $lote['creado_por']['email'] }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-info" 
                                                    title="Ver detalles"
                                                    onclick="verDetallesLote({{ json_encode($lote) }})">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            @if($lote['estado'] === 'pendiente')
                                                <button class="btn-action btn-success" 
                                                        title="Procesar lote"
                                                        onclick="procesarLote({{ $lote['id'] }})">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            @endif

                                            @if($lote['estado'] === 'completado')
                                                <button class="btn-action btn-warning" 
                                                        title="Revertir lote"
                                                        onclick="revertirLote({{ $lote['id'] }})">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            @endif

                                            <button class="btn-action btn-danger" 
                                                    title="Eliminar lote"
                                                    onclick="eliminarLote({{ $lote['id'] }})">
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
                @if($lotes->hasPages())
                    <div class="pagination-container">
                        {{ $lotes->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Modal de Detalles del Lote --}}
    <div id="detallesLoteModal" class="modal-overlay hidden">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Detalles del Lote #<span id="lote-id"></span></h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="lote-details-grid">
                    <div class="detail-item">
                        <label>Descripción:</label>
                        <span id="lote-descripcion"></span>
                    </div>
                    <div class="detail-item">
                        <label>Método de Pago:</label>
                        <span id="lote-metodo"></span>
                    </div>
                    <div class="detail-item">
                        <label>Banco Destino:</label>
                        <span id="lote-banco"></span>
                    </div>
                    <div class="detail-item">
                        <label>Total Pagos:</label>
                        <span id="lote-total-pagos"></span>
                    </div>
                    <div class="detail-item">
                        <label>Monto Total:</label>
                        <span id="lote-monto-total" class="text-success"></span>
                    </div>
                    <div class="detail-item">
                        <label>Estado:</label>
                        <span id="lote-estado"></span>
                    </div>
                    <div class="detail-item">
                        <label>Fecha Creación:</label>
                        <span id="lote-fecha-creacion"></span>
                    </div>
                    <div class="detail-item">
                        <label>Fecha Procesamiento:</label>
                        <span id="lote-fecha-procesamiento"></span>
                    </div>
                    <div class="detail-item full-width">
                        <label>Pagos Incluidos:</label>
                        <div id="lote-pagos" class="pagos-list"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-modal-btn">Cerrar</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function verDetallesLote(lote) {
        const modal = document.getElementById('detallesLoteModal');
        
        // Llenar datos del lote
        document.getElementById('lote-id').textContent = lote.id;
        document.getElementById('lote-descripcion').textContent = lote.descripcion;
        document.getElementById('lote-metodo').textContent = lote.metodo_pago;
        document.getElementById('lote-banco').textContent = lote.banco_destino;
        document.getElementById('lote-total-pagos').textContent = lote.total_pagos;
        document.getElementById('lote-monto-total').textContent = 'Bs. ' + lote.monto_total.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('lote-estado').innerHTML = `<span class="status-badge status-${lote.estado}">${lote.estado}</span>`;
        document.getElementById('lote-fecha-creacion').textContent = new Date(lote.fecha_creacion).toLocaleString('es-ES');
        document.getElementById('lote-fecha-procesamiento').textContent = lote.fecha_procesamiento ? new Date(lote.fecha_procesamiento).toLocaleString('es-ES') : 'N/A';
        
        // Llenar lista de pagos
        const pagosList = document.getElementById('lote-pagos');
        pagosList.innerHTML = '';
        
        if (lote.pagos && lote.pagos.length > 0) {
            lote.pagos.forEach(pago => {
                const pagoItem = document.createElement('div');
                pagoItem.className = 'pago-item';
                pagoItem.innerHTML = `
                    <div class="pago-info">
                        <strong>#${pago.id}</strong> - ${pago.aliado_nombre}
                    </div>
                    <div class="pago-monto">
                        Bs. ${pago.monto_neto.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </div>
                `;
                pagosList.appendChild(pagoItem);
            });
        } else {
            pagosList.innerHTML = '<p class="text-muted">No hay información de pagos disponible</p>';
        }
        
        modal.style.display = 'flex';
    }

    function procesarLote(loteId) {
        Swal.fire({
            title: 'Procesar Lote',
            text: '¿Estás seguro de que quieres procesar este lote de pagos?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#8a2be2',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Procesar Lote',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar formulario para procesar lote
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('admin.payouts.procesar-lote') }}";
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                const loteIdInput = document.createElement('input');
                loteIdInput.type = 'hidden';
                loteIdInput.name = 'lote_id';
                loteIdInput.value = loteId;
                form.appendChild(loteIdInput);
                
                const accionInput = document.createElement('input');
                accionInput.type = 'hidden';
                accionInput.name = 'accion';
                accionInput.value = 'confirmar';
                form.appendChild(accionInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function revertirLote(loteId) {
        Swal.fire({
            title: 'Revertir Lote',
            text: '¿Estás seguro de que quieres revertir este lote de pagos? Esta acción revertirá todos los pagos incluidos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Revertir Lote',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar formulario para revertir lote
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('admin.payouts.procesar-lote') }}";
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                const loteIdInput = document.createElement('input');
                loteIdInput.type = 'hidden';
                loteIdInput.name = 'lote_id';
                loteIdInput.value = loteId;
                form.appendChild(loteIdInput);
                
                const accionInput = document.createElement('input');
                accionInput.type = 'hidden';
                accionInput.name = 'accion';
                accionInput.value = 'revertir';
                form.appendChild(accionInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function eliminarLote(loteId) {
        Swal.fire({
            title: 'Eliminar Lote',
            text: '¿Estás seguro de que quieres eliminar este lote? Esta acción no se puede deshacer.',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Eliminar Lote',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Aquí iría la lógica para eliminar el lote
                Swal.fire('Eliminado', 'El lote ha sido eliminado correctamente.', 'success');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar modales
        document.querySelectorAll('.close-modal-btn, .cancel-modal-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('detallesLoteModal').style.display = 'none';
            });
        });

        // Cerrar alertas
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });

        // Cerrar modal al hacer click fuera
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('detallesLoteModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>

<style>
    .lote-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .lote-descripcion {
        font-weight: 600;
        color: var(--text-dark);
    }

    .lote-metodo {
        font-size: 12px;
        color: var(--text-muted);
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-pendiente {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .status-procesando {
        background: rgba(23, 162, 184, 0.1);
        color: var(--info-color);
    }

    .status-completado {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .status-fallido {
        background: rgba(220, 53, 69, 0.1);
        color: var(--error-color);
    }

    .action-buttons {
        display: flex;
        gap: 4px;
        justify-content: center;
    }

    .btn-action {
        padding: 6px 8px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
    }

    .btn-info {
        background: var(--info-color);
        color: white;
    }

    .btn-info:hover {
        background: #138496;
    }

    .btn-success {
        background: var(--success-color);
        color: white;
    }

    .btn-success:hover {
        background: #218838;
    }

    .btn-warning {
        background: var(--warning-color);
        color: #000;
    }

    .btn-warning:hover {
        background: #e0a800;
    }

    .btn-danger {
        background: var(--error-color);
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .lote-details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .detail-item.full-width {
        grid-column: 1 / -1;
    }

    .detail-item label {
        font-weight: 600;
        color: var(--text-dark);
        font-size: 14px;
    }

    .pagos-list {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 12px;
    }

    .pago-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .pago-item:last-child {
        border-bottom: none;
    }

    .pago-info {
        font-size: 14px;
    }

    .pago-monto {
        font-weight: 600;
        color: var(--success-color);
    }

    @media (max-width: 768px) {
        .lote-details-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-action {
            width: 100%;
        }
    }
</style>
@endpush