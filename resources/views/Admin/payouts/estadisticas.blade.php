@extends('layouts.admin')

@section('page_title_toolbar', 'Estadísticas de Pagos a Aliados')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/estadisticas.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
    @php
        // Asegurar que $estadisticas sea un array
        $estadisticas = $estadisticas ?? [];
        
        // Valores por defecto para estadísticas principales
        $totalPendiente = $estadisticas['total_pendiente'] ?? 0;
        $totalPagado = $estadisticas['total_pagado'] ?? 0;
        $totalCompletado = $estadisticas['total_completado'] ?? 0;
        $totalProcesando = $estadisticas['total_procesando'] ?? 0;
        $totalRevertido = $estadisticas['total_revertido'] ?? 0;
        $totalAliados = $estadisticas['total_aliados'] ?? 0;
        $archivosGenerados = $estadisticas['archivos_generados'] ?? 0;
        
        // Payouts por estado
        $payoutsPorEstado = $estadisticas['payouts_por_estado'] ?? [];
        $pendingCount = $payoutsPorEstado['pending'] ?? 0;
        $processingCount = $payoutsPorEstado['processing'] ?? 0;
        $completedCount = $payoutsPorEstado['completed'] ?? 0;
        $revertedCount = $payoutsPorEstado['reverted'] ?? 0;
        $totalPayouts = $pendingCount + $processingCount + $completedCount + $revertedCount;
        
        // Estadísticas mensuales
        $estadisticasMensuales = $estadisticas['estadisticas_mensuales'] ?? [];
        $datosMensuales = collect($estadisticasMensuales)->pluck('total_monto')->toArray();
        
        // Asegurar que haya al menos 12 valores para la gráfica
        while (count($datosMensuales) < 12) {
            $datosMensuales[] = 0;
        }
        
        // Top aliados
        $topAliados = $topAliados ?? [];
        $totalGeneralTop = collect($topAliados)->sum('total_monto');
        
        // Evolución mensual detallada
        $evolucionMensual = $evolucionMensual ?? [];
        
        // Fecha de actualización
        $fechaActualizacion = $fechaActualizacion ?? now()->format('d/m/Y H:i:s');
    @endphp

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <span class="text-gray-900">Estadísticas de</span>
                <span class="text-purple">Pagos a Aliados</span>
            </h1>
            <div class="update-badge">
                <i class="fas fa-sync-alt"></i>
                <span>Actualizado: {{ $fechaActualizacion }}</span>
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

        {{-- Tarjetas de estadísticas principales --}}
        <div class="stats-grid-large">
            <div class="stat-card-large">
                <div class="stat-icon-large">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content-large">
                    <div class="stat-label-large">Pagos Pendientes</div>
                    <div class="stat-value-large">{{ number_format($pendingCount) }}</div>
                    <div class="stat-trend">
                        <span class="trend-up">
                            <i class="fas fa-arrow-up"></i> Bs. {{ number_format($totalPendiente, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="stat-card-large">
                <div class="stat-icon-large">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content-large">
                    <div class="stat-label-large">Pagos Completados</div>
                    <div class="stat-value-large">{{ number_format($completedCount) }}</div>
                    <div class="stat-trend">
                        <span class="trend-up">
                            <i class="fas fa-arrow-up"></i> Bs. {{ number_format($totalPagado, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="stat-card-large">
                <div class="stat-icon-large">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-content-large">
                    <div class="stat-label-large">Aliados Activos</div>
                    <div class="stat-value-large">{{ number_format($totalAliados) }}</div>
                    <div class="stat-trend">
                        <span class="trend-up">
                            <i class="fas fa-arrow-up"></i> Con pagos registrados
                        </span>
                    </div>
                </div>
            </div>

            <div class="stat-card-large">
                <div class="stat-icon-large">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="stat-content-large">
                    <div class="stat-label-large">Archivos Generados</div>
                    <div class="stat-value-large">{{ number_format($archivosGenerados) }}</div>
                    <div class="stat-trend">
                        <span class="trend-up">
                            <i class="fas fa-arrow-up"></i> Archivos BNC
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Distribución por estado --}}
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-header">
                    <i class="fas fa-clock"></i>
                    <h4>Pendientes</h4>
                </div>
                <div class="stat-number">{{ $pendingCount }}</div>
                <div class="stat-sub">Bs. {{ number_format($totalPendiente, 2, ',', '.') }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-header">
                    <i class="fas fa-sync-alt"></i>
                    <h4>Procesando</h4>
                </div>
                <div class="stat-number">{{ $processingCount }}</div>
                <div class="stat-sub">Bs. {{ number_format($totalProcesando, 2, ',', '.') }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-header">
                    <i class="fas fa-check-circle"></i>
                    <h4>Completados</h4>
                </div>
                <div class="stat-number">{{ $completedCount }}</div>
                <div class="stat-sub">Bs. {{ number_format($totalPagado, 2, ',', '.') }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-header">
                    <i class="fas fa-undo-alt"></i>
                    <h4>Revertidos</h4>
                </div>
                <div class="stat-number">{{ $revertedCount }}</div>
                <div class="stat-sub">Bs. {{ number_format($totalRevertido, 2, ',', '.') }}</div>
            </div>
        </div>

        {{-- Gráficas --}}
        <div class="charts-grid">
            {{-- Gráfica de distribución por estado --}}
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Distribución de Pagos por Estado</h3>
                    <span class="badge">{{ $totalPayouts }} total</span>
                </div>
                <div class="chart-container">
                    <canvas id="estadosChart"></canvas>
                </div>
            </div>

            {{-- Gráfica de evolución mensual --}}
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Evolución de Pagos Mensuales</h3>
                    <span class="badge">Últimos 12 meses</span>
                </div>
                <div class="chart-container">
                    <canvas id="evolucionChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Aliados --}}
        <div class="top-aliados-card">
            <div class="top-aliados-header">
                <h3>Top 10 Aliados con Mayor Volumen de Pagos</h3>
                <span class="badge">Total pagado: Bs. {{ number_format($totalGeneralTop, 2, ',', '.') }}</span>
            </div>

            <table class="top-aliados-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Aliado</th>
                        <th>Email</th>
                        <th>Total Pagos</th>
                        <th>Monto Total</th>
                        <th>Participación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topAliados as $index => $aliado)
                        @php
                            $aliadoId = $aliado['aliado_id'] ?? 'N/A';
                            $aliadoNombre = $aliado['aliado_nombre'] ?? 'Aliado';
                            $aliadoEmail = $aliado['email'] ?? 'N/A';
                            $totalPayouts = $aliado['total_payouts'] ?? 0;
                            $totalMonto = $aliado['total_monto'] ?? 0;
                            $inicial = !empty($aliadoNombre) ? substr($aliadoNombre, 0, 1) : 'A';
                            
                            $porcentaje = $totalGeneralTop > 0 ? round(($totalMonto / $totalGeneralTop) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="aliado-info">
                                    <div class="aliado-avatar">
                                        {{ $inicial }}
                                    </div>
                                    <div>
                                        <div class="aliado-name">{{ $aliadoNombre }}</div>
                                        <div class="aliado-email">ID: {{ $aliadoId }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $aliadoEmail }}</td>
                            <td class="text-success">{{ number_format($totalPayouts) }}</td>
                            <td class="text-success">Bs. {{ number_format($totalMonto, 2, ',', '.') }}</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $porcentaje }}%"></div>
                                </div>
                                <small class="text-muted">{{ $porcentaje }}% del total</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                No hay datos de aliados disponibles
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Evolución mensual detallada --}}
        @if(!empty($evolucionMensual))
        <div class="evolucion-card">
            <div class="evolucion-header">
                <h3>Evolución Mensual Detallada</h3>
                <div class="evolucion-tabs">
                    <button class="tab-btn active" onclick="cambiarVista('montos')">Montos</button>
                    <button class="tab-btn" onclick="cambiarVista('cantidades')">Cantidades</button>
                </div>
            </div>

            <table class="evolucion-table">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Pagos Completados</th>
                        <th>Monto Total</th>
                        <th>Pagos Pendientes</th>
                        <th>Monto Pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evolucionMensual as $mes)
                        @php
                            $nombreMes = $mes['mes'] ?? 'N/A';
                            $completadosCantidad = $mes['completados']['cantidad'] ?? 0;
                            $completadosMonto = $mes['completados']['monto'] ?? 0;
                            $pendientesCantidad = $mes['pendientes']['cantidad'] ?? 0;
                            $pendientesMonto = $mes['pendientes']['monto'] ?? 0;
                        @endphp
                        <tr>
                            <td><strong>{{ $nombreMes }}</strong></td>
                            <td>{{ number_format($completadosCantidad) }}</td>
                            <td class="text-success">Bs. {{ number_format($completadosMonto, 2, ',', '.') }}</td>
                            <td>{{ number_format($pendientesCantidad) }}</td>
                            <td class="text-warning">Bs. {{ number_format($pendientesMonto, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Botones de exportación --}}
        <div class="export-buttons">
            <a href="{{ route('admin.payouts.exportar-reporte', ['formato' => 'pdf', 'tipo' => 'estadisticas']) }}" class="export-btn">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </a>
            <a href="{{ route('admin.payouts.exportar-reporte', ['formato' => 'excel', 'tipo' => 'estadisticas']) }}" class="export-btn">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
            <a href="{{ route('admin.payouts.exportar-reporte', ['formato' => 'csv', 'tipo' => 'estadisticas']) }}" class="export-btn">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
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

            // Gráfica de distribución por estado
            const estadosCtx = document.getElementById('estadosChart').getContext('2d');
            new Chart(estadosCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pendientes', 'Procesando', 'Completados', 'Revertidos'],
                    datasets: [{
                        data: [
                            {{ $pendingCount }},
                            {{ $processingCount }},
                            {{ $completedCount }},
                            {{ $revertedCount }}
                        ],
                        backgroundColor: [
                            '#f59e0b',
                            '#3b82f6',
                            '#10b981',
                            '#ef4444'
                        ],
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

            // Gráfica de evolución mensual
            const evolucionCtx = document.getElementById('evolucionChart').getContext('2d');
            new Chart(evolucionCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    datasets: [{
                        label: 'Monto Pagado (Bs.)',
                        data: @json($datosMensuales),
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
                                callback: function(value) {
                                    return 'Bs. ' + value.toLocaleString('es-ES');
                                }
                            }
                        }
                    }
                }
            });
        });

        function cambiarVista(tipo) {
            // Aquí implementarías la lógica para cambiar entre vistas de montos y cantidades
            const tabs = document.querySelectorAll('.tab-btn');
            tabs.forEach(tab => tab.classList.remove('active'));
            if (event && event.target) {
                event.target.classList.add('active');
            }
            
            // Actualizar tabla según el tipo seleccionado
            console.log('Cambiar vista a:', tipo);
            
            // Aquí podrías agregar lógica para recargar la tabla con diferentes datos
            if (tipo === 'montos') {
                // Mostrar montos
            } else {
                // Mostrar cantidades
            }
        }
    </script>
@endpush