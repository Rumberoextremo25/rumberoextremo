@extends('layouts.admin')

@section('page_title_toolbar', 'Estadísticas de Pagos')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/payout.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <h2 class="page-title text-gray-900">
            <span class="text-gray-900">Estadísticas de</span>
            <span class="text-purple">Pagos</span>
        </h2>

        {{-- Filtros --}}
        <div class="stats-card mb-4">
            <form action="{{ route('admin.payouts.estadisticas') }}" method="GET" class="filters-form">
                <div class="filters-grid">
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
                        <label for="tipo_estadistica">Tipo:</label>
                        <select name="tipo_estadistica" id="tipo_estadistica" class="form-control">
                            <option value="general" {{ request('tipo_estadistica') == 'general' ? 'selected' : '' }}>General</option>
                            <option value="mensual" {{ request('tipo_estadistica') == 'mensual' ? 'selected' : '' }}>Mensual</option>
                            <option value="por_aliado" {{ request('tipo_estadistica') == 'por_aliado' ? 'selected' : '' }}>Por Aliado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="action-button">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>

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

        {{-- Resumen General --}}
        <div class="stats-grid mb-6">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value">Bs. {{ number_format($estadisticas['monto_total_pagado'] ?? 0, 2, ',', '.') }}</div>
                <div class="stat-label">Total Pagado</div>
                <div class="stat-trend {{ ($estadisticas['tendencia_monto_total'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ ($estadisticas['tendencia_monto_total'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($estadisticas['tendencia_monto_total'] ?? 0) }}%
                </div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value">{{ $estadisticas['total_pagos'] ?? 0 }}</div>
                <div class="stat-label">Total Pagos</div>
                <div class="stat-trend {{ ($estadisticas['tendencia_total_pagos'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ ($estadisticas['tendencia_total_pagos'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($estadisticas['tendencia_total_pagos'] ?? 0) }}%
                </div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-value">{{ $estadisticas['total_aliados'] ?? 0 }}</div>
                <div class="stat-label">Aliados Activos</div>
                <div class="stat-trend {{ ($estadisticas['tendencia_aliados'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ ($estadisticas['tendencia_aliados'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($estadisticas['tendencia_aliados'] ?? 0) }}%
                </div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-value">{{ number_format($estadisticas['comision_promedio'] ?? 0, 1) }}%</div>
                <div class="stat-label">Comisión Promedio</div>
                <div class="stat-trend">
                    <i class="fas fa-minus"></i>
                    0%
                </div>
            </div>
        </div>

        {{-- Gráficos --}}
        <div class="charts-grid">
            {{-- Gráfico de Pagos por Mes --}}
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Pagos por Mes</h3>
                    <div class="chart-actions">
                        <button class="chart-action-btn" data-chart="pagos-mes">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="pagosMesChart" height="300"></canvas>
                </div>
            </div>

            {{-- Gráfico de Distribución por Aliado --}}
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Distribución por Aliado</h3>
                    <div class="chart-actions">
                        <button class="chart-action-btn" data-chart="distribucion-aliado">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="distribucionAliadoChart" height="300"></canvas>
                </div>
            </div>

            {{-- Gráfico de Estados de Pago --}}
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Estados de Pago</h3>
                    <div class="chart-actions">
                        <button class="chart-action-btn" data-chart="estados-pago">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="estadosPagoChart" height="300"></canvas>
                </div>
            </div>

            {{-- Gráfico de Tendencia de Comisiones --}}
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Tendencia de Comisiones</h3>
                    <div class="chart-actions">
                        <button class="chart-action-btn" data-chart="tendencia-comisiones">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="tendenciaComisionesChart" height="300"></canvas>
                </div>
            </div>
        </div>

        {{-- Tabla de Top Aliados --}}
        <div class="stats-card mt-6">
            <div class="card-header">
                <h3>Top 10 Aliados por Monto Pagado</h3>
            </div>
            <div class="table-responsive">
                <table class="payouts-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Aliado</th>
                            <th>Email</th>
                            <th>Total Pagado (Bs.)</th>
                            <th>Total Pagos</th>
                            <th>Comisión Promedio</th>
                            <th>Último Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($estadisticas['top_aliados'] ?? []) as $index => $aliado)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $aliado['nombre'] ?? 'N/A' }}</div>
                                    <small class="text-muted">ID: {{ $aliado['id'] ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $aliado['email'] ?? 'N/A' }}</td>
                                <td class="text-success">
                                    <strong>Bs. {{ number_format($aliado['total_pagado'] ?? 0, 2, ',', '.') }}</strong>
                                </td>
                                <td>{{ $aliado['total_pagos'] ?? 0 }}</td>
                                <td>
                                    <span class="badge badge-info">{{ number_format($aliado['comision_promedio'] ?? 0, 1) }}%</span>
                                </td>
                                <td>
                                    @if(isset($aliado['ultimo_pago']))
                                        {{ \Carbon\Carbon::parse($aliado['ultimo_pago'])->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="no-data-message">
                                        <i class="fas fa-chart-bar"></i>
                                        <p>No hay datos de aliados disponibles</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Estadísticas Detalladas --}}
        <div class="stats-grid mt-6">
            <div class="stat-card-large">
                <div class="stat-card-header">
                    <h4>Resumen por Estado</h4>
                </div>
                <div class="stat-card-body">
                    @foreach(($estadisticas['resumen_estados'] ?? []) as $estado => $datos)
                        <div class="status-item">
                            <div class="status-info">
                                <span class="status-badge status-{{ $estado }}">{{ ucfirst($estado) }}</span>
                                <span class="status-count">{{ $datos['count'] ?? 0 }} pagos</span>
                            </div>
                            <div class="status-amount">
                                Bs. {{ number_format($datos['monto'] ?? 0, 2, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="stat-card-large">
                <div class="stat-card-header">
                    <h4>Eficiencia de Pagos</h4>
                </div>
                <div class="stat-card-body">
                    <div class="efficiency-item">
                        <div class="efficiency-label">Tasa de Completación</div>
                        <div class="efficiency-value">
                            {{ number_format($estadisticas['tasa_completacion'] ?? 0, 1) }}%
                        </div>
                        <div class="efficiency-bar">
                            <div class="efficiency-progress" style="width: {{ $estadisticas['tasa_completacion'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="efficiency-item">
                        <div class="efficiency-label">Tiempo Promedio de Procesamiento</div>
                        <div class="efficiency-value">
                            {{ $estadisticas['tiempo_promedio_procesamiento'] ?? 0 }} días
                        </div>
                        <div class="efficiency-bar">
                            <div class="efficiency-progress" style="width: {{ min(($estadisticas['tiempo_promedio_procesamiento'] ?? 0) * 10, 100) }}%"></div>
                        </div>
                    </div>

                    <div class="efficiency-item">
                        <div class="efficiency-label">Pagos con Retraso</div>
                        <div class="efficiency-value">
                            {{ $estadisticas['pagos_retraso'] ?? 0 }}
                        </div>
                        <div class="efficiency-bar">
                            <div class="efficiency-progress retraso" style="width: {{ min((($estadisticas['pagos_retraso'] ?? 0) / max(($estadisticas['total_pagos'] ?? 1), 1)) * 100, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Exportar Reporte --}}
        <div class="stats-card mt-6">
            <div class="card-header">
                <h3>Exportar Reporte</h3>
            </div>
            <div class="export-actions">
                <form action="{{ route('admin.payouts.exportar-reporte') }}" method="POST" class="export-form">
                    @csrf
                    <input type="hidden" name="fecha_inicio" value="{{ request('fecha_inicio', date('Y-m-01')) }}">
                    <input type="hidden" name="fecha_fin" value="{{ request('fecha_fin', date('Y-m-d')) }}">
                    
                    <div class="export-options">
                        <div class="form-group">
                            <label for="formato">Formato:</label>
                            <select name="formato" id="formato" class="form-control">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="action-button export-btn">
                            <i class="fas fa-file-export"></i> Exportar Reporte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Datos para los gráficos (estos vendrían del controlador)
        const chartData = @json($estadisticas['chart_data'] ?? []);

        // Gráfico de Pagos por Mes
        const pagosMesCtx = document.getElementById('pagosMesChart').getContext('2d');
        new Chart(pagosMesCtx, {
            type: 'bar',
            data: {
                labels: chartData.meses || ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Monto Total (Bs.)',
                    data: chartData.montos_mensuales || [0, 0, 0, 0, 0, 0],
                    backgroundColor: 'rgba(138, 43, 226, 0.8)',
                    borderColor: 'rgba(138, 43, 226, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Evolución Mensual de Pagos'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Bs. ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Distribución por Aliado
        const distribucionAliadoCtx = document.getElementById('distribucionAliadoChart').getContext('2d');
        new Chart(distribucionAliadoCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.aliados_labels || ['Aliado 1', 'Aliado 2', 'Aliado 3'],
                datasets: [{
                    data: chartData.aliados_montos || [30, 40, 30],
                    backgroundColor: [
                        'rgba(138, 43, 226, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(23, 162, 184, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Distribución por Aliado'
                    }
                }
            }
        });

        // Gráfico de Estados de Pago
        const estadosPagoCtx = document.getElementById('estadosPagoChart').getContext('2d');
        new Chart(estadosPagoCtx, {
            type: 'pie',
            data: {
                labels: chartData.estados_labels || ['Completado', 'Pendiente', 'Fallido'],
                datasets: [{
                    data: chartData.estados_counts || [60, 30, 10],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Distribución por Estado'
                    }
                }
            }
        });

        // Gráfico de Tendencia de Comisiones
        const tendenciaComisionesCtx = document.getElementById('tendenciaComisionesChart').getContext('2d');
        new Chart(tendenciaComisionesCtx, {
            type: 'line',
            data: {
                labels: chartData.meses_comisiones || ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Comisión Promedio (%)',
                    data: chartData.comisiones_mensuales || [5.0, 5.2, 4.8, 5.1, 5.0, 4.9],
                    backgroundColor: 'rgba(23, 162, 184, 0.2)',
                    borderColor: 'rgba(23, 162, 184, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Tendencia de Comisiones'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 0,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });

        // Descargar gráficos
        document.querySelectorAll('.chart-action-btn').forEach(button => {
            button.addEventListener('click', function() {
                const chartId = this.getAttribute('data-chart');
                const canvas = document.querySelector(`#${chartId.replace('-', '')}Chart`);
                
                if (canvas) {
                    const link = document.createElement('a');
                    link.download = `grafico-${chartId}-${new Date().toISOString().split('T')[0]}.png`;
                    link.href = canvas.toDataURL();
                    link.click();
                }
            });
        });

        // Cerrar alertas
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });

        // Actualizar filtros del formulario de exportación
        document.querySelector('#fecha_inicio').addEventListener('change', function() {
            document.querySelector('input[name="fecha_inicio"]').value = this.value;
        });

        document.querySelector('#fecha_fin').addEventListener('change', function() {
            document.querySelector('input[name="fecha_fin"]').value = this.value;
        });
    });
</script>
@endpush