@extends('layouts.admin')

@section('page_title_toolbar', 'Dashboard de Pagos')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/payout.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <h2 class="page-title text-gray-900">
            <span class="text-gray-900">Dashboard de</span>
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

        {{-- KPIs Principales --}}
        <div class="stats-grid mb-6">
            <div class="stat-item kpi-card">
                <div class="kpi-header">
                    <div class="kpi-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="kpi-trend {{ ($estadisticas['tendencia_monto_total'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        <i class="fas fa-arrow-{{ ($estadisticas['tendencia_monto_total'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($estadisticas['tendencia_monto_total'] ?? 0) }}%
                    </div>
                </div>
                <div class="kpi-value">Bs. {{ number_format($estadisticas['monto_total_pagado'] ?? 0, 2, ',', '.') }}</div>
                <div class="kpi-label">Total Pagado</div>
                <div class="kpi-subtitle">Este mes: Bs. {{ number_format($estadisticas['monto_mes_actual'] ?? 0, 2, ',', '.') }}</div>
            </div>

            <div class="stat-item kpi-card">
                <div class="kpi-header">
                    <div class="kpi-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="kpi-trend {{ ($estadisticas['tendencia_total_pagos'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        <i class="fas fa-arrow-{{ ($estadisticas['tendencia_total_pagos'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($estadisticas['tendencia_total_pagos'] ?? 0) }}%
                    </div>
                </div>
                <div class="kpi-value">{{ number_format($estadisticas['total_pagos'] ?? 0, 0, ',', '.') }}</div>
                <div class="kpi-label">Total Pagos Procesados</div>
                <div class="kpi-subtitle">Este mes: {{ $estadisticas['pagos_mes_actual'] ?? 0 }}</div>
            </div>

            <div class="stat-item kpi-card">
                <div class="kpi-header">
                    <div class="kpi-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="kpi-trend {{ ($estadisticas['tendencia_aliados'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        <i class="fas fa-arrow-{{ ($estadisticas['tendencia_aliados'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($estadisticas['tendencia_aliados'] ?? 0) }}%
                    </div>
                </div>
                <div class="kpi-value">{{ number_format($estadisticas['total_aliados'] ?? 0, 0, ',', '.') }}</div>
                <div class="kpi-label">Aliados Activos</div>
                <div class="kpi-subtitle">{{ $estadisticas['nuevos_aliados_mes'] ?? 0 }} nuevos este mes</div>
            </div>

            <div class="stat-item kpi-card">
                <div class="kpi-header">
                    <div class="kpi-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="kpi-trend warning">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ $estadisticas['pagos_pendientes'] ?? 0 }}</div>
                <div class="kpi-label">Pagos Pendientes</div>
                <div class="kpi-subtitle">Bs. {{ number_format($estadisticas['monto_pendiente'] ?? 0, 2, ',', '.') }} por procesar</div>
            </div>
        </div>

        {{-- Gráficos Principales --}}
        <div class="dashboard-grid">
            {{-- Gráfico de Evolución Mensual --}}
            <div class="dashboard-card large">
                <div class="card-header">
                    <h3>Evolución de Pagos</h3>
                    <div class="card-actions">
                        <select id="chart-period" class="form-control-sm">
                            <option value="6m">Últimos 6 meses</option>
                            <option value="1y">Último año</option>
                            <option value="ytd">Este año</option>
                        </select>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="evolucionPagosChart" height="250"></canvas>
                </div>
            </div>

            {{-- Distribución por Aliados --}}
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Top Aliados</h3>
                    <div class="card-actions">
                        <button class="btn-action btn-info" onclick="verTodosAliados()">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="topAliadosChart" height="250"></canvas>
                </div>
            </div>

            {{-- Métricas de Eficiencia --}}
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Métricas de Eficiencia</h3>
                </div>
                <div class="metrics-container">
                    <div class="metric-item">
                        <div class="metric-info">
                            <div class="metric-label">Tasa de Completación</div>
                            <div class="metric-value">{{ number_format($estadisticas['tasa_completacion'] ?? 0, 1) }}%</div>
                        </div>
                        <div class="metric-bar">
                            <div class="metric-progress" style="width: {{ $estadisticas['tasa_completacion'] ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div class="metric-item">
                        <div class="metric-info">
                            <div class="metric-label">Tiempo Promedio</div>
                            <div class="metric-value">{{ $estadisticas['tiempo_promedio_procesamiento'] ?? 0 }} días</div>
                        </div>
                        <div class="metric-bar">
                            <div class="metric-progress warning" style="width: {{ min(($estadisticas['tiempo_promedio_procesamiento'] ?? 0) * 10, 100) }}%"></div>
                        </div>
                    </div>

                    <div class="metric-item">
                        <div class="metric-info">
                            <div class="metric-label">Pagos con Retraso</div>
                            <div class="metric-value">{{ $estadisticas['pagos_retraso'] ?? 0 }}</div>
                        </div>
                        <div class="metric-bar">
                            <div class="metric-progress danger" style="width: {{ min((($estadisticas['pagos_retraso'] ?? 0) / max(($estadisticas['total_pagos'] ?? 1), 1)) * 100, 100) }}%"></div>
                        </div>
                    </div>

                    <div class="metric-item">
                        <div class="metric-info">
                            <div class="metric-label">Satisfacción Aliados</div>
                            <div class="metric-value">{{ number_format($estadisticas['satisfaccion_aliados'] ?? 0, 1) }}%</div>
                        </div>
                        <div class="metric-bar">
                            <div class="metric-progress success" style="width: {{ $estadisticas['satisfaccion_aliados'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estados de Pago --}}
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Estados de Pago</h3>
                </div>
                <div class="chart-container">
                    <canvas id="estadosPagoChart" height="250"></canvas>
                </div>
            </div>
        </div>

        {{-- Sección de Acciones Rápidas --}}
        <div class="dashboard-grid mt-6">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Acciones Rápidas</h3>
                </div>
                <div class="quick-actions">
                    <a href="{{ route('admin.payouts.pendientes') }}" class="quick-action-btn">
                        <div class="quick-action-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="quick-action-info">
                            <div class="quick-action-title">Pagos Pendientes</div>
                            <div class="quick-action-subtitle">{{ $estadisticas['pagos_pendientes'] ?? 0 }} por procesar</div>
                        </div>
                        <div class="quick-action-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>

                    <a href="{{ route('admin.payouts.generate_bnc') }}" class="quick-action-btn" onclick="event.preventDefault(); generarArchivoBNC();">
                        <div class="quick-action-icon generate">
                            <i class="fas fa-file-export"></i>
                        </div>
                        <div class="quick-action-info">
                            <div class="quick-action-title">Generar BNC</div>
                            <div class="quick-action-subtitle">Crear archivo de pagos</div>
                        </div>
                        <div class="quick-action-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>

                    <a href="{{ route('admin.payouts.archivos') }}" class="quick-action-btn">
                        <div class="quick-action-icon files">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div class="quick-action-info">
                            <div class="quick-action-title">Archivos</div>
                            <div class="quick-action-subtitle">{{ $estadisticas['total_archivos'] ?? 0 }} generados</div>
                        </div>
                        <div class="quick-action-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>

                    <a href="{{ route('admin.payouts.estadisticas') }}" class="quick-action-btn">
                        <div class="quick-action-icon stats">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="quick-action-info">
                            <div class="quick-action-title">Estadísticas</div>
                            <div class="quick-action-subtitle">Ver reportes detallados</div>
                        </div>
                        <div class="quick-action-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Pagos Recientes --}}
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Pagos Recientes</h3>
                    <div class="card-actions">
                        <a href="{{ route('admin.payouts.index') }}" class="btn-action btn-info">
                            Ver Todos
                        </a>
                    </div>
                </div>
                <div class="recent-payouts">
                    @forelse(($payoutsRecientes ?? []) as $payout)
                        <div class="recent-payout-item">
                            <div class="payout-avatar">
                                {{ substr($payout['aliado']['nombre'] ?? 'A', 0, 1) }}
                            </div>
                            <div class="payout-info">
                                <div class="payout-aliado">{{ $payout['aliado']['nombre'] ?? 'N/A' }}</div>
                                <div class="payout-details">
                                    <span class="payout-amount">Bs. {{ number_format($payout['montos']['neto'] ?? 0, 2, ',', '.') }}</span>
                                    <span class="payout-date">{{ \Carbon\Carbon::parse($payout['fechas']['generacion'])->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="payout-status">
                                <span class="status-badge status-{{ $payout['estado'] ?? 'pending' }}">
                                    {{ $payout['estado'] ?? 'pending' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="no-data-message">
                            <i class="fas fa-receipt"></i>
                            <p>No hay pagos recientes</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Resumen Mensual --}}
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Resumen Mensual</h3>
                    <div class="card-actions">
                        <span class="current-month">{{ now()->format('F Y') }}</span>
                    </div>
                </div>
                <div class="monthly-summary">
                    <div class="summary-item">
                        <div class="summary-label">Total Pagado</div>
                        <div class="summary-value">Bs. {{ number_format($resumenMensual['total_pagado'] ?? 0, 2, ',', '.') }}</div>
                        <div class="summary-trend {{ ($resumenMensual['tendencia_total'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                            <i class="fas fa-arrow-{{ ($resumenMensual['tendencia_total'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($resumenMensual['tendencia_total'] ?? 0) }}%
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Pagos Procesados</div>
                        <div class="summary-value">{{ $resumenMensual['pagos_procesados'] ?? 0 }}</div>
                        <div class="summary-trend {{ ($resumenMensual['tendencia_pagos'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                            <i class="fas fa-arrow-{{ ($resumenMensual['tendencia_pagos'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($resumenMensual['tendencia_pagos'] ?? 0) }}%
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Comisión Total</div>
                        <div class="summary-value">Bs. {{ number_format($resumenMensual['comision_total'] ?? 0, 2, ',', '.') }}</div>
                        <div class="summary-trend {{ ($resumenMensual['tendencia_comision'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                            <i class="fas fa-arrow-{{ ($resumenMensual['tendencia_comision'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($resumenMensual['tendencia_comision'] ?? 0) }}%
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Aliados Activos</div>
                        <div class="summary-value">{{ $resumenMensual['aliados_activos'] ?? 0 }}</div>
                        <div class="summary-trend {{ ($resumenMensual['tendencia_aliados'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                            <i class="fas fa-arrow-{{ ($resumenMensual['tendencia_aliados'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($resumenMensual['tendencia_aliados'] ?? 0) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertas y Notificaciones --}}
        @if(($estadisticas['alertas'] ?? []) && count($estadisticas['alertas']) > 0)
            <div class="dashboard-card mt-6">
                <div class="card-header">
                    <h3>Alertas del Sistema</h3>
                    <div class="card-actions">
                        <span class="badge badge-danger">{{ count($estadisticas['alertas']) }}</span>
                    </div>
                </div>
                <div class="alerts-container">
                    @foreach($estadisticas['alertas'] as $alerta)
                        <div class="alert-item {{ $alerta['nivel'] }}">
                            <div class="alert-icon">
                                <i class="fas fa-{{ $alerta['icono'] ?? 'exclamation-triangle' }}"></i>
                            </div>
                            <div class="alert-content">
                                <div class="alert-title">{{ $alerta['titulo'] }}</div>
                                <div class="alert-message">{{ $alerta['mensaje'] }}</div>
                            </div>
                            <div class="alert-time">{{ $alerta['tiempo'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Datos para los gráficos
        const dashboardData = @json($estadisticas['dashboard_data'] ?? []);
        const resumenMensual = @json($resumenMensual ?? []);

        // Gráfico de Evolución de Pagos
        const evolucionPagosCtx = document.getElementById('evolucionPagosChart').getContext('2d');
        const evolucionPagosChart = new Chart(evolucionPagosCtx, {
            type: 'line',
            data: {
                labels: dashboardData.meses || ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'Monto Total (Bs.)',
                        data: dashboardData.montos_mensuales || [0, 0, 0, 0, 0, 0],
                        borderColor: 'rgba(138, 43, 226, 1)',
                        backgroundColor: 'rgba(138, 43, 226, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Número de Pagos',
                        data: dashboardData.pagos_mensuales || [0, 0, 0, 0, 0, 0],
                        borderColor: 'rgba(255, 193, 7, 1)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Monto (Bs.)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Bs. ' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Número de Pagos'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.dataset.yAxisID === 'y') {
                                    label += 'Bs. ' + context.parsed.y.toLocaleString();
                                } else {
                                    label += context.parsed.y.toLocaleString() + ' pagos';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Top Aliados
        const topAliadosCtx = document.getElementById('topAliadosChart').getContext('2d');
        new Chart(topAliadosCtx, {
            type: 'bar',
            data: {
                labels: dashboardData.top_aliados_labels || ['Aliado 1', 'Aliado 2', 'Aliado 3', 'Aliado 4', 'Aliado 5'],
                datasets: [{
                    label: 'Monto Pagado (Bs.)',
                    data: dashboardData.top_aliados_montos || [0, 0, 0, 0, 0],
                    backgroundColor: [
                        'rgba(138, 43, 226, 0.8)',
                        'rgba(138, 43, 226, 0.7)',
                        'rgba(138, 43, 226, 0.6)',
                        'rgba(138, 43, 226, 0.5)',
                        'rgba(138, 43, 226, 0.4)'
                    ],
                    borderColor: 'rgba(138, 43, 226, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            callback: function(value) {
                                return 'Bs. ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Estados de Pago
        const estadosPagoCtx = document.getElementById('estadosPagoChart').getContext('2d');
        new Chart(estadosPagoCtx, {
            type: 'doughnut',
            data: {
                labels: dashboardData.estados_labels || ['Completado', 'Pendiente', 'Fallido', 'Revertido'],
                datasets: [{
                    data: dashboardData.estados_counts || [0, 0, 0, 0],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(108, 117, 125, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                },
                cutout: '70%'
            }
        });

        // Cambiar período del gráfico
        document.getElementById('chart-period').addEventListener('change', function() {
            // Aquí iría la lógica para actualizar el gráfico con nuevos datos
            Swal.fire({
                title: 'Cargando datos...',
                text: 'Actualizando gráfico para el período seleccionado',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Simular carga de datos
            setTimeout(() => {
                Swal.close();
            }, 1000);
        });

        // Cerrar alertas
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    });

    function generarArchivoBNC() {
        Swal.fire({
            title: 'Generar Archivo BNC',
            text: '¿Quieres generar un nuevo archivo BNC con los pagos pendientes?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#8a2be2',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Generar Archivo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.payouts.pendientes') }}";
            }
        });
    }

    function verTodosAliados() {
        window.location.href = "{{ route('admin.payouts.resumen-aliado') }}";
    }

    // Actualizar dashboard cada 5 minutos
    setInterval(() => {
        // Aquí iría la lógica para actualizar el dashboard sin recargar la página
        console.log('Actualizando dashboard...');
    }, 300000);
</script>
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    .dashboard-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 24px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }

    .dashboard-card.large {
        grid-column: 1 / -1;
    }

    .kpi-card {
        text-align: center;
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid rgba(138, 43, 226, 0.1);
        border-radius: 12px;
    }

    .kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .kpi-icon {
        font-size: 24px;
        color: var(--primary-color);
        background: rgba(138, 43, 226, 0.1);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .kpi-trend {
        font-size: 14px;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 12px;
    }

    .kpi-trend.positive {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .kpi-trend.negative {
        background: rgba(220, 53, 69, 0.1);
        color: var(--error-color);
    }

    .kpi-trend.warning {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .kpi-value {
        font-size: 32px;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 4px;
        line-height: 1;
    }

    .kpi-label {
        font-size: 14px;
        color: var(--text-muted);
        margin-bottom: 8px;
        font-weight: 600;
    }

    .kpi-subtitle {
        font-size: 12px;
        color: var(--text-muted);
        opacity: 0.8;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border-color);
    }

    .card-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .card-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-control-sm {
        padding: 6px 12px;
        font-size: 14px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: white;
    }

    .metrics-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .metric-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .metric-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .metric-label {
        font-size: 14px;
        color: var(--text-dark);
        font-weight: 500;
    }

    .metric-value {
        font-size: 16px;
        font-weight: 700;
        color: var(--primary-color);
    }

    .metric-bar {
        height: 6px;
        background: var(--bg-light);
        border-radius: 3px;
        overflow: hidden;
    }

    .metric-progress {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
        background: var(--primary-color);
    }

    .metric-progress.success {
        background: var(--success-color);
    }

    .metric-progress.warning {
        background: var(--warning-color);
    }

    .metric-progress.danger {
        background: var(--error-color);
    }

    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: var(--bg-light);
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-dark);
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .quick-action-btn:hover {
        background: white;
        border-color: var(--primary-color);
        transform: translateX(4px);
        text-decoration: none;
        color: var(--text-dark);
    }

    .quick-action-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
    }

    .quick-action-icon.pending {
        background: var(--warning-color);
    }

    .quick-action-icon.generate {
        background: var(--primary-color);
    }

    .quick-action-icon.files {
        background: var(--info-color);
    }

    .quick-action-icon.stats {
        background: var(--success-color);
    }

    .quick-action-info {
        flex: 1;
    }

    .quick-action-title {
        font-weight: 600;
        margin-bottom: 2px;
    }

    .quick-action-subtitle {
        font-size: 12px;
        color: var(--text-muted);
    }

    .quick-action-arrow {
        color: var(--text-muted);
        transition: transform 0.3s ease;
    }

    .quick-action-btn:hover .quick-action-arrow {
        transform: translateX(4px);
        color: var(--primary-color);
    }

    .recent-payouts {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .recent-payout-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--bg-light);
        border-radius: 8px;
        transition: background 0.3s ease;
    }

    .recent-payout-item:hover {
        background: #e9ecef;
    }

    .payout-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .payout-info {
        flex: 1;
    }

    .payout-aliado {
        font-weight: 600;
        margin-bottom: 2px;
    }

    .payout-details {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: var(--text-muted);
    }

    .payout-amount {
        font-weight: 600;
        color: var(--success-color);
    }

    .payout-status {
        display: flex;
        align-items: center;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-completed {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .status-pending {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .status-failed {
        background: rgba(220, 53, 69, 0.1);
        color: var(--error-color);
    }

    .monthly-summary {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-label {
        font-size: 14px;
        color: var(--text-dark);
        font-weight: 500;
    }

    .summary-value {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .summary-trend {
        font-size: 12px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 8px;
    }

    .current-month {
        font-size: 14px;
        color: var(--text-muted);
        font-weight: 500;
    }

    .alerts-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .alert-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 8px;
        border-left: 4px solid;
    }

    .alert-item.critical {
        background: rgba(220, 53, 69, 0.05);
        border-left-color: var(--error-color);
    }

    .alert-item.warning {
        background: rgba(255, 193, 7, 0.05);
        border-left-color: var(--warning-color);
    }

    .alert-item.info {
        background: rgba(23, 162, 184, 0.05);
        border-left-color: var(--info-color);
    }

    .alert-icon {
        font-size: 16px;
        width: 24px;
        text-align: center;
    }

    .alert-item.critical .alert-icon {
        color: var(--error-color);
    }

    .alert-item.warning .alert-icon {
        color: var(--warning-color);
    }

    .alert-item.info .alert-icon {
        color: var(--info-color);
    }

    .alert-content {
        flex: 1;
    }

    .alert-title {
        font-weight: 600;
        margin-bottom: 2px;
    }

    .alert-message {
        font-size: 12px;
        color: var(--text-muted);
    }

    .alert-time {
        font-size: 10px;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .no-data-message {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
    }

    .no-data-message i {
        font-size: 32px;
        margin-bottom: 8px;
        display: block;
    }

    @media (max-width: 1200px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
        
        .dashboard-card.large {
            grid-column: 1;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .kpi-value {
            font-size: 24px;
        }
        
        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        
        .card-actions {
            align-self: flex-end;
        }
        
        .quick-action-btn {
            padding: 12px;
        }
        
        .quick-action-icon {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .dashboard-card {
            padding: 16px;
        }
    }
</style>
@endpush