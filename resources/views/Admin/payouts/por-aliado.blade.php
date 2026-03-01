@extends('layouts.admin')

@section('page_title_toolbar', 'Pagos por Aliado')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/por-aliado.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
    <div class="main-content por-aliado-container">
        {{-- Header --}}
        <div class="page-header">
            <div class="header-left">
                <a href="{{ route('admin.payouts.resumen-aliado') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Volver al Resumen
                </a>
                <h1>
                    <span class="text-gray-900">Pagos de</span>
                    <span class="text-purple">{{ $estadisticasAliado['aliado_nombre'] ?? 'Aliado' }}</span>
                </h1>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.payouts.exportar-reporte', ['formato' => 'pdf', 'aliado_id' => $aliadoId]) }}"
                    class="btn-export">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </a>
                <a href="{{ route('admin.payouts.exportar-reporte', ['formato' => 'excel', 'aliado_id' => $aliadoId]) }}"
                    class="btn-export">
                    <i class="fas fa-file-excel"></i> Exportar Excel
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

        {{-- Información del Aliado --}}
        <div class="aliado-info-card">
            <div class="aliado-avatar-large">
                {{ substr($estadisticasAliado['aliado_nombre'] ?? 'A', 0, 1) }}
            </div>
            <div class="aliado-info-content">
                <div class="aliado-nombre-large">{{ $estadisticasAliado['aliado_nombre'] ?? 'Aliado' }}</div>
                <div class="aliado-meta">
                    <div class="aliado-meta-item">
                        <i class="fas fa-id-card"></i>
                        <span>ID: {{ $aliadoId }}</span>
                    </div>
                    <div class="aliado-meta-item">
                        <i class="fas fa-envelope"></i>
                        <span>{{ $estadisticasAliado['email'] ?? 'Email no disponible' }}</span>
                    </div>
                    <div class="aliado-meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Desde {{ $estadisticasAliado['primer_pago'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tarjetas de Estadísticas --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Pagos</div>
                    <div class="stat-value">{{ number_format($estadisticasAliado['total_payouts'] ?? 0) }}</div>
                    <div class="stat-sub">pagos realizados</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Monto Total</div>
                    <div class="stat-value">Bs. {{ number_format($estadisticasAliado['total_monto'] ?? 0, 2, ',', '.') }}
                    </div>
                    <div class="stat-sub">pagados al aliado</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Promedio por Pago</div>
                    @php
                        $promedio =
                            ($estadisticasAliado['total_payouts'] ?? 0) > 0
                                ? ($estadisticasAliado['total_monto'] ?? 0) /
                                    ($estadisticasAliado['total_payouts'] ?? 1)
                                : 0;
                    @endphp
                    <div class="stat-value">Bs. {{ number_format($promedio, 2, ',', '.') }}</div>
                    <div class="stat-sub">por transacción</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Último Pago</div>
                    <div class="stat-value">
                        @if ($ultimoPago = collect($pagos)->first())
                            {{ \Carbon\Carbon::parse($ultimoPago['fecha_generacion'] ?? $ultimoPago['created_at'])->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="stat-sub">
                        @if ($ultimoPago)
                            {{ \Carbon\Carbon::parse($ultimoPago['fecha_generacion'] ?? $ultimoPago['created_at'])->format('H:i') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráficas --}}
        <div class="graficas-grid">
            {{-- Gráfica de Evolución Mensual --}}
            <div class="grafica-card">
                <div class="grafica-header">
                    <h4>
                        <i class="fas fa-chart-line"></i>
                        Evolución Mensual
                    </h4>
                    <span class="badge">Últimos 6 meses</span>
                </div>
                <div class="grafica-container">
                    <canvas id="evolucionChart"></canvas>
                </div>
            </div>

            {{-- Gráfica de Distribución por Estado --}}
            <div class="grafica-card">
                <div class="grafica-header">
                    <h4>
                        <i class="fas fa-chart-pie"></i>
                        Distribución por Estado
                    </h4>
                    <span class="badge">total {{ $estadisticasAliado['total_payouts'] ?? 0 }} pagos</span>
                </div>
                <div class="grafica-container">
                    <canvas id="estadosChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="filtros-section">
            <div class="filtros-titulo">
                <i class="fas fa-filter"></i>
                <span>Filtrar Pagos</span>
            </div>
            <form action="{{ route('admin.payouts.por-aliado', $aliadoId) }}" method="GET" class="filtros-grid">
                <div class="filtro-group">
                    <label for="status">Estado</label>
                    <select name="status" id="status" class="form-control">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Todos</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendientes</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>En Proceso
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completados
                        </option>
                        <option value="reverted" {{ request('status') == 'reverted' ? 'selected' : '' }}>Revertidos
                        </option>
                    </select>
                </div>
                <div class="filtro-group">
                    <label for="fecha_inicio">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                        value="{{ request('fecha_inicio', now()->subYear()->format('Y-m-d')) }}">
                </div>
                <div class="filtro-group">
                    <label for="fecha_fin">Fecha Fin</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                        value="{{ request('fecha_fin', now()->format('Y-m-d')) }}">
                </div>
                <div class="filtro-group">
                    <label for="busqueda">Búsqueda</label>
                    <input type="text" name="busqueda" id="busqueda" class="form-control"
                        placeholder="ID, referencia..." value="{{ request('busqueda') }}">
                </div>
                <div class="filtro-actions">
                    <button type="submit" class="btn-filtro primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.payouts.por-aliado', $aliadoId) }}" class="btn-filtro secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabla de Pagos --}}
        <div class="pagos-table-container">
            <div class="table-header">
                <h3>
                    <i class="fas fa-list"></i>
                    Historial de Pagos
                </h3>
                <span class="badge">{{ $pagination['total'] ?? 0 }} registro(s)</span>
            </div>

            @if (empty($pagos))
                <div class="no-data-message" style="text-align: center; padding: 4rem;">
                    <i class="fas fa-money-bill-wave" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280;">No hay pagos registrados</h3>
                    <p style="color: #9ca3af;">Este aliado no tiene pagos para los filtros seleccionados.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="pagos-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Venta ID</th>
                                <th>Monto Venta</th>
                                <th>Comisión</th>
                                <th>Neto Pagado</th>
                                <th>Estado</th>
                                <th>Fecha Generación</th>
                                <th>Fecha Pago</th>
                                <th>Referencia</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pagos as $pago)
                                @php
                                    $estado = $pago['estado'] ?? 'pending';
                                    $estadoClase = match ($estado) {
                                        'completed' => 'badge-completed',
                                        'processing' => 'badge-processing',
                                        'pending' => 'badge-pending',
                                        'reverted' => 'badge-reverted',
                                        default => 'badge-pending',
                                    };
                                    $estadoTexto = match ($estado) {
                                        'completed' => 'Completado',
                                        'processing' => 'En Proceso',
                                        'pending' => 'Pendiente',
                                        'reverted' => 'Revertido',
                                        default => 'Pendiente',
                                    };
                                @endphp
                                <tr>
                                    <td><strong>#{{ $pago['id'] }}</strong></td>
                                    <td>#{{ $pago['venta']['id'] ?? 'N/A' }}</td>
                                    <td class="text-success">Bs.
                                        {{ number_format($pago['venta']['monto_total'] ?? 0, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge badge-warning">{{ $pago['comision_porcentaje'] ?? 0 }}%</span>
                                        <br>
                                        <small class="text-danger">Bs.
                                            {{ number_format($pago['comision_monto'] ?? 0, 2, ',', '.') }}</small>
                                    </td>
                                    <td class="text-success">Bs. {{ number_format($pago['neto'] ?? 0, 2, ',', '.') }}</td>
                                    <td><span class="badge {{ $estadoClase }}">{{ $estadoTexto }}</span></td>
                                    <td>
                                        @if (isset($pago['fecha_generacion']))
                                            {{ \Carbon\Carbon::parse($pago['fecha_generacion'])->format('d/m/Y H:i') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($pago['created_at'])->format('d/m/Y H:i') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($pago['fecha_pago']))
                                            {{ \Carbon\Carbon::parse($pago['fecha_pago'])->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($pago['referencia_pago']))
                                            <code>{{ $pago['referencia_pago'] }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.payouts.show', $payoutId) }}" class="btn-icon view"
                                                title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if ($estado === 'pending')
                                                <a href="{{ route('admin.payouts.edit', $payoutId) }}"
                                                    class="btn-icon edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif

                                            @if ($estado === 'completed')
                                                <button class="btn-icon revert" title="Revertir pago"
                                                    onclick="confirmarRevertir({{ $payoutId }})">
                                                    <i class="fas fa-undo-alt"></i>
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
                @if (isset($pagination))
                    <div class="pagination-section">
                        <div class="pagination-info">
                            Mostrando {{ ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 }}
                            - {{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }}
                            de {{ $pagination['total'] }} pagos
                        </div>
                        <div class="pagination-links">
                            @if ($pagination['current_page'] > 1)
                                <a href="{{ route('admin.payouts.por-aliado', array_merge([$aliadoId], request()->query(), ['page' => $pagination['current_page'] - 1])) }}"
                                    class="pagination-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            @endif

                            @for ($i = 1; $i <= $pagination['last_page']; $i++)
                                @if ($i == $pagination['current_page'])
                                    <span class="pagination-link active">{{ $i }}</span>
                                @else
                                    <a href="{{ route('admin.payouts.por-aliado', array_merge([$aliadoId], request()->query(), ['page' => $i])) }}"
                                        class="pagination-link">{{ $i }}</a>
                                @endif
                            @endfor

                            @if ($pagination['current_page'] < $pagination['last_page'])
                                <a href="{{ route('admin.payouts.por-aliado', array_merge([$aliadoId], request()->query(), ['page' => $pagination['current_page'] + 1])) }}"
                                    class="pagination-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Modal de Confirmación para Revertir --}}
    <div id="revertirModal" class="modal-overlay hidden"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
        <div class="modal-content" style="background: white; border-radius: 1rem; max-width: 500px; width: 90%;">
            <div class="modal-header"
                style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                    Revertir Pago
                </h3>
                <button type="button" class="close-modal-btn"
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <form action="" method="POST" id="revertirForm">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <p>¿Estás seguro de que quieres revertir este pago?</p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>

                    <div class="form-group" style="margin-top: 1rem;">
                        <label for="motivo" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Motivo de
                            la reversión</label>
                        <textarea name="motivo" id="motivo" class="form-control" rows="3"
                            placeholder="Indique el motivo de la reversión..."
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;" required></textarea>
                    </div>
                </div>
                <div class="modal-footer"
                    style="display: flex; justify-content: flex-end; gap: 1rem; padding: 1.5rem; border-top: 1px solid #e5e7eb;">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalRevertir()"
                        style="padding: 0.75rem 1.5rem; background: #f3f4f6; border: none; border-radius: 0.5rem; cursor: pointer;">Cancelar</button>
                    <button type="submit" class="btn btn-primary"
                        style="padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none; border-radius: 0.5rem; cursor: pointer;">
                        <i class="fas fa-undo-alt"></i> Revertir Pago
                    </button>
                </div>
            </form>
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

            // Inicializar gráficas
            initGraficas();

            // Modal de revertir
            const revertirModal = document.getElementById('revertirModal');
            const closeButtons = document.querySelectorAll('.close-modal-btn');

            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    revertirModal.style.display = 'none';
                });
            });

            window.addEventListener('click', function(event) {
                if (event.target === revertirModal) {
                    revertirModal.style.display = 'none';
                }
            });
        });

        function initGraficas() {
            // Gráfica de evolución mensual
            const evolucionCtx = document.getElementById('evolucionChart').getContext('2d');
            new Chart(evolucionCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    datasets: [{
                        label: 'Monto Pagado (Bs.)',
                        data: @json($evolucionMensual['data'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]),
                        borderColor: '#8a2be2',
                        backgroundColor: 'rgba(138, 43, 226, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => 'Bs. ' + value.toLocaleString('es-ES')
                            }
                        }
                    }
                }
            });

            // Gráfica de distribución por estado
            const estadosCtx = document.getElementById('estadosChart').getContext('2d');
            new Chart(estadosCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pendientes', 'En Proceso', 'Completados', 'Revertidos'],
                    datasets: [{
                        data: [
                            {{ $estadisticasAliado['estados']['pending'] ?? 0 }},
                            {{ $estadisticasAliado['estados']['processing'] ?? 0 }},
                            {{ $estadisticasAliado['estados']['completed'] ?? 0 }},
                            {{ $estadisticasAliado['estados']['reverted'] ?? 0 }}
                        ],
                        backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        window.confirmarRevertir = function(payoutId) {
            const modal = document.getElementById('revertirModal');
            const form = document.getElementById('revertirForm');

            form.action = `/admin/payouts/${payoutId}/revertir`;
            modal.style.display = 'flex';
        };

        function cerrarModalRevertir() {
            document.getElementById('revertirModal').style.display = 'none';
        }
    </script>
@endpush
