@extends('layouts.admin')

@section('page_title_toolbar', 'Reportes de Ventas')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/reports.css') }}">
@endpush

@section('content')
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
    </div>

    <div class="reports-container">
        <div class="reports-card">
            {{-- Header Modernizado --}}
            <div class="page-header">
                <h1 class="page-title">
                    <span class="accent">Análisis de Ventas</span>
                </h1>
                <p class="page-subtitle">
                    <i class="fas fa-chart-line"></i>
                    Dashboard completo de métricas y tendencias de ventas
                </p>
            </div>

            {{-- Información del Rol de Usuario --}}
            <div class="user-context-badge">
                @if ($userRole === 'admin' || $userRole === 'administrador')
                    <div class="badge-role badge-admin">
                        <i class="fas fa-crown"></i> Vista Administrador - Todas las ventas del sistema
                    </div>
                @elseif($allyId && $metrics['current_ally_info'])
                    <div class="badge-role badge-ally">
                        <i class="fas fa-handshake"></i> Vista Aliado - {{ $metrics['current_ally_info']->company_name }}
                    </div>
                    <div class="ally-info">
                        <h4><i class="fas fa-building"></i> Información del Aliado</h4>
                        <p><strong>Empresa:</strong> {{ $metrics['current_ally_info']->company_name }}</p>
                        <p><strong>Contacto:</strong> {{ $metrics['current_ally_info']->contact_person_name }}</p>
                        <p><strong>RIF:</strong> {{ $metrics['current_ally_info']->company_rif }}</p>
                        <p><strong>Estado:</strong>
                            <span class="status-{{ $metrics['current_ally_info']->status }}">
                                {{ $metrics['current_ally_info']->status === 'active' ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                    </div>
                @elseif($allyId)
                    <div class="badge-role badge-ally">
                        <i class="fas fa-handshake"></i> Vista Aliado
                    </div>
                @else
                    <div class="badge-role badge-user">
                        <i class="fas fa-user"></i> Vista Usuario
                    </div>
                @endif
            </div>

            {{-- Filtros y Acciones --}}
            <div class="section-title">
                <i class="fas fa-sliders-h"></i>
                Filtros y Configuración
            </div>

            <div class="reports-actions-grid">
                <div class="form-group">
                    <label for="startDate">
                        <i class="fas fa-calendar-alt"></i>
                        Fecha Inicial
                    </label>
                    <input type="date" id="startDate" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                </div>

                <div class="form-group">
                    <label for="endDate">
                        <i class="fas fa-calendar-alt"></i>
                        Fecha Final
                    </label>
                    <input type="date" id="endDate" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                </div>

                <div class="form-group">
                    <label for="reportType">
                        <i class="fas fa-chart-bar"></i>
                        Periodo de Análisis
                    </label>
                    <select id="reportType" class="form-select">
                        <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Vista Mensual</option>
                        <option value="weekly" {{ $reportType == 'weekly' ? 'selected' : '' }}>Vista Semanal</option>
                        <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Vista Diaria</option>
                    </select>
                </div>

                <!-- Fila de botones -->
                <div class="buttons-row">
                    <div class="form-group">
                        <button class="modern-button" id="applyFilterButton">
                            <i class="fas fa-filter"></i>
                            Aplicar Filtros
                        </button>
                    </div>
                    <div class="form-group">
                        <button class="modern-button today-report" id="viewTodayReportButton">
                            <i class="fas fa-calendar-day"></i>
                            Ver Hoy
                        </button>
                    </div>
                </div>

                <!-- Resto de botones -->
                <div class="form-group">
                    <button class="modern-button download-pdf" id="downloadPdfButton">
                        <i class="fas fa-file-pdf"></i>
                        Exportar PDF
                    </button>
                </div>
            </div>
        </div>

        {{-- Métricas Principales --}}
        <div class="section-title">
            <i class="fas fa-chart-bar"></i>
            Métricas Principales
        </div>

        <div class="stats-grid-main">
            <div class="stat-card-main">
                <div class="stat-icon-main">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value-main">${{ number_format($stats['total_sales'], 2) }}</div>
                    <div class="stat-label-main">Ventas Totales</div>
                </div>
            </div>
            <div class="stat-card-main">
                <div class="stat-icon-main">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value-main">{{ number_format($stats['total_orders']) }}</div>
                    <div class="stat-label-main">Total Órdenes</div>
                </div>
            </div>
            <div class="stat-card-main">
                <div class="stat-icon-main">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value-main">{{ number_format($stats['growth'], 1) }}%</div>
                    <div class="stat-label-main">Crecimiento</div>
                </div>
            </div>
            <div class="stat-card-main">
                <div class="stat-icon-main">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value-main">{{ number_format($stats['unique_clients']) }}</div>
                    <div class="stat-label-main">Clientes Únicos</div>
                </div>
            </div>
        </div>

        {{-- Gráfico Principal --}}
        <div class="section-title">
            <i class="fas fa-chart-line"></i>
            Tendencias de Ventas
        </div>

        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>

        {{-- Métricas Adicionales --}}
        <div class="section-title">
            <i class="fas fa-info-circle"></i>
            Información Adicional
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h4 class="stat-title">
                    <i class="fas fa-credit-card"></i>
                    Métodos de Pago
                </h4>
                <div class="payment-methods-list">
                    @foreach ($stats['payment_methods'] as $payment)
                        <div class="payment-method">
                            <span class="payment-name">{{ ucfirst($payment->payment_method) }}</span>
                            <span class="payment-count">{{ $payment->count }} órdenes</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="stat-card">
                <h4 class="stat-title">
                    <i class="fas fa-chart-pie"></i>
                    Resumen del Reporte
                </h4>
                <div class="payment-methods-list">
                    <div class="payment-method">
                        <span class="payment-name">Tipo de Vista</span>
                        <span class="payment-count view-type">{{ ucfirst($reportType) }}</span>
                    </div>
                    <div class="payment-method">
                        <span class="payment-name">Período</span>
                        <span class="payment-count date-range">
                            {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}
                        </span>
                    </div>
                    <div class="payment-method">
                        <span class="payment-name">Tasa de Cambio</span>
                        <span class="payment-count">Bs. {{ number_format($exchangeRateVes, 2) }}</span>
                    </div>
                    @if ($metrics['best_day'])
                        <div class="payment-method">
                            <span class="payment-name">Mejor Día</span>
                            <span class="payment-count">
                                {{ \Carbon\Carbon::parse($metrics['best_day']->date)->format('d/m/Y') }} -
                                ${{ number_format($metrics['best_day']->daily_sales, 2) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Información Específica para Admin --}}
        @if (($userRole === 'admin' || $userRole === 'administrador') && $metrics['top_ally'])
            <div class="section-title">
                <i class="fas fa-trophy"></i>
                Top Aliados
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <h4 class="stat-title">
                        <i class="fas fa-medal"></i>
                        Aliado con Más Ventas
                    </h4>
                    <div class="payment-methods-list">
                        <div class="payment-method">
                            <span class="payment-name">Empresa</span>
                            <span class="payment-count">{{ $metrics['top_ally']->ally->company_name ?? 'N/A' }}</span>
                        </div>
                        <div class="payment-method">
                            <span class="payment-name">Ventas Totales</span>
                            <span class="payment-count">${{ number_format($metrics['top_ally']->total_sales, 2) }}</span>
                        </div>
                        <div class="payment-method">
                            <span class="payment-name">Total Órdenes</span>
                            <span class="payment-count">{{ $metrics['top_ally']->total_orders }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Datos iniciales desde PHP
            const initialLabels = @json($chartData['labels']);
            const initialChartData = @json($chartData['data']);
            const initialReportType = "{{ $reportType }}";
            const exchangeRateVes = {{ $exchangeRateVes }};
            const userRole = "{{ $userRole }}";

            // Referencias a elementos del DOM
            const salesChartCanvas = document.getElementById('salesChart');
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const reportTypeSelect = document.getElementById('reportType');
            const applyFilterButton = document.getElementById('applyFilterButton');
            const viewTodayReportButton = document.getElementById('viewTodayReportButton');
            const downloadPdfButton = document.getElementById('downloadPdfButton');
            const loadingOverlay = document.getElementById('loadingOverlay');

            let salesChart;

            // Función para mostrar/ocultar loading
            function showLoading(show) {
                if (loadingOverlay) {
                    loadingOverlay.style.display = show ? 'flex' : 'none';
                }
            }

            // Función para renderizar el gráfico
            function renderChart(labels, data) {
                if (salesChart) {
                    salesChart.destroy();
                }

                const ctx = salesChartCanvas.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(138, 43, 226, 0.8)');
                gradient.addColorStop(1, 'rgba(138, 43, 226, 0.2)');

                salesChart = new Chart(salesChartCanvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ventas (USD)',
                            data: data,
                            backgroundColor: gradient,
                            borderColor: 'rgba(138, 43, 226, 1)',
                            borderWidth: 2,
                            borderRadius: 8,
                            barThickness: 35,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                titleColor: '#1f2937',
                                bodyColor: '#4b5563',
                                borderColor: '#e5e7eb',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return `$${context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString('en-US', {
                                            maximumFractionDigits: 0
                                        });
                                    },
                                    color: '#6b7280'
                                },
                                border: {
                                    dash: [4, 4]
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#6b7280'
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }

            // Renderizar gráfico inicial
            if (salesChartCanvas && initialLabels && initialChartData) {
                renderChart(initialLabels, initialChartData);
            }

            // Manejar filtros
            if (applyFilterButton) {
                applyFilterButton.addEventListener('click', function() {
                    const startDate = startDateInput.value;
                    const endDate = endDateInput.value;
                    const reportType = reportTypeSelect.value;

                    showLoading(true);

                    // Redireccionar con los nuevos parámetros
                    const url = new URL(window.location.href);
                    url.searchParams.set('startDate', startDate);
                    url.searchParams.set('endDate', endDate);
                    url.searchParams.set('reportType', reportType);
                    window.location.href = url.toString();
                });
            }

            // Botón "Hoy"
            if (viewTodayReportButton) {
                viewTodayReportButton.addEventListener('click', function() {
                    const today = new Date().toISOString().split('T')[0];
                    if (startDateInput) startDateInput.value = today;
                    if (endDateInput) endDateInput.value = today;
                    if (reportTypeSelect) reportTypeSelect.value = 'daily';
                    if (applyFilterButton) applyFilterButton.click();
                });
            }

            // Descargar PDF
            if (downloadPdfButton) {
                downloadPdfButton.addEventListener('click', function() {
                    downloadPdfReport();
                });
            }

            function downloadPdfReport() {
                // Obtener los parámetros actuales del filtro
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                const reportType = reportTypeSelect.value;

                // Validar fechas
                if (!startDate || !endDate) {
                    alert('Por favor, selecciona ambas fechas');
                    return;
                }

                if (startDate > endDate) {
                    alert('La fecha inicial no puede ser mayor a la fecha final');
                    return;
                }

                // Mostrar loading
                showLoading(true);

                // Crear URL con parámetros para el endpoint de PDF
                const url = new URL('{{ route('admin.reports.export') }}', window.location.origin);
                url.searchParams.append('startDate', startDate);
                url.searchParams.append('endDate', endDate);
                url.searchParams.append('reportType', reportType);

                console.log('Generando PDF con URL:', url.toString());

                // Crear un enlace temporal para la descarga
                const downloadLink = document.createElement('a');
                downloadLink.href = url.toString();
                downloadLink.target = '_blank';
                downloadLink.style.display = 'none';

                // Agregar al DOM y hacer click
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);

                // Ocultar loading después de un breve delay
                setTimeout(() => {
                    showLoading(false);
                }, 2000);
            }

            // Validaciones de fecha
            if (startDateInput && endDateInput) {
                startDateInput.addEventListener('change', function() {
                    if (endDateInput.value && startDateInput.value > endDateInput.value) {
                        endDateInput.value = startDateInput.value;
                    }
                });

                endDateInput.addEventListener('change', function() {
                    if (startDateInput.value && endDateInput.value < startDateInput.value) {
                        startDateInput.value = endDateInput.value;
                    }
                });
            }

            // Efectos visuales adicionales para botones
            document.querySelectorAll('.modern-button').forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });

                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
@endpush
