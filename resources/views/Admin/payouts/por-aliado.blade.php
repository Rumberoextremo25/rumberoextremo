@extends('layouts.admin')

@section('page_title_toolbar', 'Pagos por Aliado')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/payout.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <h2 class="page-title text-gray-900">
            <span class="text-gray-900">Pagos de</span>
            <span class="text-purple">{{ $estadisticasAliado['aliado']['nombre'] ?? 'Aliado' }}</span>
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

        {{-- Información del Aliado --}}
        <div class="stats-card mb-6">
            <div class="aliado-header">
                <div class="aliado-avatar">
                    {{ substr($estadisticasAliado['aliado']['nombre'] ?? 'A', 0, 1) }}
                </div>
                <div class="aliado-info">
                    <h3>{{ $estadisticasAliado['aliado']['nombre'] ?? 'N/A' }}</h3>
                    <div class="aliado-details">
                        <span class="aliado-email">{{ $estadisticasAliado['aliado']['email'] ?? 'N/A' }}</span>
                        <span class="aliado-id">ID: {{ $aliadoId }}</span>
                    </div>
                </div>
                <div class="aliado-status">
                    <span class="status-badge status-active">Activo</span>
                </div>
            </div>
        </div>

        {{-- Estadísticas del Aliado --}}
        <div class="stats-grid mb-6">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value">Bs. {{ number_format($estadisticasAliado['total_pagado'] ?? 0, 2, ',', '.') }}</div>
                <div class="stat-label">Total Pagado</div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-value">{{ $estadisticasAliado['total_pagos'] ?? 0 }}</div>
                <div class="stat-label">Total Pagos</div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-value">{{ number_format($estadisticasAliado['comision_promedio'] ?? 0, 1) }}%</div>
                <div class="stat-label">Comisión Promedio</div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value">{{ $estadisticasAliado['pagos_pendientes'] ?? 0 }}</div>
                <div class="stat-label">Pagos Pendientes</div>
            </div>
        </div>

        {{-- Información Bancaria --}}
        <div class="stats-card mb-6">
            <div class="card-header">
                <h3>Información Bancaria</h3>
            </div>
            <div class="bank-info-grid">
                <div class="bank-info-item">
                    <label>Cuenta Bancaria:</label>
                    <span class="bank-value">{{ $estadisticasAliado['aliado']['cuenta_bancaria'] ?? 'No registrada' }}</span>
                </div>
                <div class="bank-info-item">
                    <label>Tipo de Cuenta:</label>
                    <span class="bank-value">{{ $estadisticasAliado['aliado']['tipo_cuenta'] ?? 'N/A' }}</span>
                </div>
                <div class="bank-info-item">
                    <label>Banco:</label>
                    <span class="bank-value">{{ $estadisticasAliado['aliado']['banco'] ?? 'N/A' }}</span>
                </div>
                <div class="bank-info-item">
                    <label>Fecha Registro:</label>
                    <span class="bank-value">{{ \Carbon\Carbon::parse($estadisticasAliado['aliado']['fecha_registro'] ?? now())->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="stats-card mb-4">
            <form action="{{ route('admin.payouts.por-aliado', $aliadoId) }}" method="GET" class="filters-form">
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <select name="estado" id="estado" class="form-control">
                            <option value="">Todos los estados</option>
                            <option value="pending" {{ request('estado') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="processed" {{ request('estado') == 'processed' ? 'selected' : '' }}>Procesado</option>
                            <option value="failed" {{ request('estado') == 'failed' ? 'selected' : '' }}>Fallido</option>
                            <option value="reverted" {{ request('estado') == 'reverted' ? 'selected' : '' }}>Revertido</option>
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
                            <a href="{{ route('admin.payouts.por-aliado', $aliadoId) }}" class="action-button secondary">
                                <i class="fas fa-redo"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Lista de Pagos --}}
        <div class="stats-card">
            <div class="card-header">
                <h3>Historial de Pagos</h3>
                <div class="card-actions">
                    <span class="text-muted">Mostrando {{ $payouts->count() }} pagos</span>
                </div>
            </div>

            @if($payouts->isEmpty())
                <div class="no-data-message">
                    <i class="fas fa-receipt"></i>
                    <h4>No se encontraron pagos</h4>
                    <p>No hay pagos para este aliado que coincidan con los criterios de búsqueda.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="payouts-table">
                        <thead>
                            <tr>
                                <th>ID Pago</th>
                                <th>ID Venta</th>
                                <th>Monto Venta</th>
                                <th>Comisión</th>
                                <th>Neto a Pagar</th>
                                <th>Estado</th>
                                <th>Fecha Generación</th>
                                <th>Fecha Pago</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payouts as $payout)
                                <tr>
                                    <td>
                                        <strong>#{{ $payout['id'] }}</strong>
                                    </td>
                                    <td>
                                        #{{ $payout['venta']['id'] }}
                                    </td>
                                    <td class="text-success">
                                        <strong>Bs. {{ number_format($payout['montos']['monto_venta'], 2, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ number_format($payout['montos']['comision_porcentaje'], 1) }}%</span>
                                        <br>
                                        <small>Bs. {{ number_format($payout['montos']['comision_monto'], 2, ',', '.') }}</small>
                                    </td>
                                    <td class="text-success">
                                        <strong>Bs. {{ number_format($payout['montos']['neto'], 2, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $payout['estado'] }}">
                                            {{ $payout['estado'] }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($payout['fechas']['generacion'])->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        @if($payout['fechas']['pago'] ?? false)
                                            {{ \Carbon\Carbon::parse($payout['fechas']['pago'])->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.payouts.show', $payout['id']) }}" 
                                               class="btn-action btn-info" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <a href="{{ route('admin.payouts.edit', $payout['id']) }}" 
                                               class="btn-action btn-warning" 
                                               title="Editar pago">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            @if($payout['estado'] === 'pending')
                                                <button class="btn-action btn-success" 
                                                        title="Confirmar pago"
                                                        onclick="confirmarPagoIndividual({{ $payout['id'] }})">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($payouts->hasPages())
                    <div class="pagination-container">
                        {{ $payouts->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- Resumen por Estado --}}
        <div class="stats-card mt-6">
            <div class="card-header">
                <h3>Resumen por Estado</h3>
            </div>
            <div class="status-summary-grid">
                @foreach($estadisticasAliado['resumen_estados'] ?? [] as $estado => $datos)
                    <div class="status-summary-item">
                        <div class="status-info">
                            <span class="status-badge status-{{ $estado }}">{{ ucfirst($estado) }}</span>
                            <span class="status-count">{{ $datos['count'] }} pagos</span>
                        </div>
                        <div class="status-amount">
                            Bs. {{ number_format($datos['monto'], 2, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function confirmarPagoIndividual(payoutId) {
        Swal.fire({
            title: 'Confirmar Pago',
            text: '¿Estás seguro de que quieres confirmar este pago individual?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#8a2be2',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Confirmar Pago',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Aquí iría la lógica para confirmar el pago individual
                Swal.fire('Confirmado', 'El pago ha sido confirmado correctamente.', 'success');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar alertas
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    });
</script>

<style>
    .aliado-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
    }

    .aliado-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), #6f42c1);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 700;
    }

    .aliado-info {
        flex: 1;
    }

    .aliado-info h3 {
        margin: 0 0 8px 0;
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .aliado-details {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .aliado-email {
        font-size: 16px;
        color: var(--text-muted);
    }

    .aliado-id {
        font-size: 14px;
        color: var(--text-muted);
        background: var(--bg-light);
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-block;
    }

    .aliado-status {
        display: flex;
        align-items: center;
    }

    .status-badge.status-active {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
    }

    .bank-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
        padding: 20px;
    }

    .bank-info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .bank-info-item label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    .bank-value {
        font-weight: 500;
        color: var(--text-dark);
        font-size: 14px;
    }

    .status-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        padding: 20px;
    }

    .status-summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        background: var(--bg-light);
        border-radius: 8px;
        border-left: 4px solid;
    }

    .status-summary-item .status-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .status-count {
        font-size: 12px;
        color: var(--text-muted);
    }

    .status-amount {
        font-weight: 600;
        color: var(--text-dark);
    }

    .status-summary-item:nth-child(1) {
        border-left-color: var(--warning-color);
    }

    .status-summary-item:nth-child(2) {
        border-left-color: var(--success-color);
    }

    .status-summary-item:nth-child(3) {
        border-left-color: var(--error-color);
    }

    .status-summary-item:nth-child(4) {
        border-left-color: var(--info-color);
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
        text-decoration: none;
    }

    .btn-info {
        background: var(--info-color);
        color: white;
    }

    .btn-info:hover {
        background: #138496;
    }

    .btn-warning {
        background: var(--warning-color);
        color: #000;
    }

    .btn-warning:hover {
        background: #e0a800;
    }

    .btn-success {
        background: var(--success-color);
        color: white;
    }

    .btn-success:hover {
        background: #218838;
    }

    @media (max-width: 768px) {
        .aliado-header {
            flex-direction: column;
            text-align: center;
        }
        
        .bank-info-grid {
            grid-template-columns: 1fr;
        }
        
        .status-summary-grid {
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