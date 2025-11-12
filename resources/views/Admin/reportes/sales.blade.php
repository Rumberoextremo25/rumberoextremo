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
            <span>Generando reporte...</span>
        </div>
    </div>

    <div class="reports-container">
        {{-- Header Moderno --}}
        <div class="reports-header-modern">
            <div class="header-content">
                <div class="breadcrumb">
                    <a href="{{ route('dashboard') }}" class="breadcrumb-link">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current">Reportes de Ventas</span>
                </div>
                <h1 class="page-title">
                    <span class="title-text">Análisis de</span>
                    <span class="title-accent">Ventas</span>
                </h1>
                <p class="page-subtitle">
                    <i class="fas fa-chart-line"></i>
                    Dashboard completo de métricas y tendencias de ventas
                </p>
            </div>
            <div class="header-actions">
                <div class="real-time-indicator">
                    <i class="fas fa-sync-alt"></i>
                    Actualizado: {{ now()->format('d/m/Y H:i') }}
                </div>
                <div class="exchange-rate-badge">
                    <i class="fas fa-dollar-sign"></i>
                    Tasa Bs. {{ number_format($exchangeRateVes, 2) }} / USD
                </div>
            </div>
        </div>

        {{-- Información del Contexto del Usuario --}}
        <div class="user-context-section">
            @if ($userRole === 'admin' || $userRole === 'administrador')
                <div class="context-card admin">
                    <div class="context-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="context-content">
                        <h3 class="context-title">Vista Administrador</h3>
                        <p class="context-description">Acceso completo a todas las ventas del sistema</p>
                    </div>
                </div>
            @elseif($allyId && ($metrics['current_ally_info'] ?? null))
                <div class="context-card ally">
                    <div class="context-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="context-content">
                        <h3 class="context-title">{{ $metrics['current_ally_info']->company_name }}</h3>
                        <p class="context-description">Panel de control de ventas del aliado</p>
                        <div class="ally-details">
                            <div class="detail-item">
                                <i class="fas fa-user-tie"></i>
                                <span>{{ $metrics['current_ally_info']->contact_person_name }}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-id-card"></i>
                                <span>{{ $metrics['current_ally_info']->company_rif }}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-tag"></i>
                                <span class="status-badge status-{{ $metrics['current_ally_info']->status }}">
                                    {{ ucfirst($metrics['current_ally_info']->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="context-card user">
                    <div class="context-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="context-content">
                        <h3 class="context-title">Vista Usuario</h3>
                        <p class="context-description">Reporte de ventas personales</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Panel de Filtros Avanzados --}}
        <div class="filters-panel-modern">
            <div class="panel-header">
                <h3 class="panel-title">
                    <i class="fas fa-sliders-h"></i>
                    Filtros y Configuración
                </h3>
                <button class="panel-toggle" id="filtersToggle">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            
            <div class="filters-content" id="filtersContent">
                <div class="filters-grid">
                    {{-- Filtro de Fechas --}}
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-calendar-alt"></i>
                            Rango de Fechas
                        </label>
                        <div class="date-range-picker">
                            <div class="date-input-group">
                                <input type="date" id="startDate" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                                <span class="date-separator">hasta</span>
                                <input type="date" id="endDate" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Filtro de Tipo de Reporte --}}
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-chart-bar"></i>
                            Periodo de Análisis
                        </label>
                        <select id="reportType" class="form-select">
                            <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Vista Mensual</option>
                            <option value="weekly" {{ $reportType == 'weekly' ? 'selected' : '' }}>Vista Semanal</option>
                            <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Vista Diaria</option>
                        </select>
                    </div>
                </div>

                {{-- Acciones Rápidas --}}
                <div class="filter-actions">
                    <div class="quick-actions">
                        <button class="action-btn quick-filter" data-days="7">
                            <i class="fas fa-calendar-week"></i>
                            Últimos 7 días
                        </button>
                        <button class="action-btn quick-filter" data-days="30">
                            <i class="fas fa-calendar"></i>
                            Últimos 30 días
                        </button>
                        <button class="action-btn quick-filter today" data-days="0">
                            <i class="fas fa-calendar-day"></i>
                            Hoy
                        </button>
                    </div>
                    
                    <div class="main-actions">
                        <button class="modern-button secondary" id="resetFiltersButton">
                            <i class="fas fa-redo"></i>
                            Reiniciar
                        </button>
                        <button class="modern-button primary" id="applyFilterButton">
                            <i class="fas fa-filter"></i>
                            Aplicar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Métricas Principales en Tiempo Real --}}
        <div class="metrics-section">
            <h2 class="section-title">
                <i class="fas fa-chart-bar"></i>
                Métricas Principales
            </h2>
            
            <div class="metrics-grid-main">
                {{-- Ventas Totales --}}
                <div class="metric-card primary">
                    <div class="metric-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">${{ number_format($stats['total_sales'] ?? 0, 2) }}</div>
                        <div class="metric-label">Ventas Totales USD</div>
                        <div class="metric-subtext">
                            Bs. {{ number_format($stats['total_sales_ves'] ?? 0, 2) }}
                        </div>
                        <div class="metric-trend {{ ($stats['growth'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                            <i class="fas fa-arrow-{{ ($stats['growth'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                            {{ number_format(abs($stats['growth'] ?? 0), 1) }}%
                        </div>
                    </div>
                </div>

                {{-- Total Órdenes --}}
                <div class="metric-card success">
                    <div class="metric-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">{{ number_format($stats['total_orders'] ?? 0) }}</div>
                        <div class="metric-label">Total Órdenes</div>
                        <div class="metric-subtext">
                            @if(($stats['average_order_value'] ?? 0) > 0)
                                ${{ number_format($stats['average_order_value'], 2) }} USD
                                <br>
                                Bs. {{ number_format($stats['average_order_value_ves'] ?? 0, 2) }}
                            @else
                                Sin datos
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Clientes Únicos --}}
                <div class="metric-card warning">
                    <div class="metric-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">{{ number_format($stats['unique_clients'] ?? 0) }}</div>
                        <div class="metric-label">Clientes Únicos</div>
                        <div class="metric-subtext">
                            {{ $stats['repeat_customers'] ?? 0 }} clientes recurrentes
                        </div>
                    </div>
                </div>

                {{-- Tasa de Cambio --}}
                <div class="metric-card info">
                    <div class="metric-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">{{ number_format($exchangeRateVes, 2) }}</div>
                        <div class="metric-label">Tasa BNC</div>
                        <div class="metric-subtext">Bs. por USD</div>
                        <div class="rate-source">
                            <i class="fas fa-database"></i>
                            Fuente: Banco Central
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfico Principal Interactivo --}}
        <div class="chart-section">
            <div class="chart-header">
                <h2 class="section-title">
                    <i class="fas fa-chart-line"></i>
                    Tendencias de Ventas
                </h2>
                <div class="chart-actions">
                    <button class="chart-action-btn" id="toggleChartType">
                        <i class="fas fa-exchange-alt"></i>
                        Cambiar Vista
                    </button>
                    <button class="chart-action-btn" id="downloadChart">
                        <i class="fas fa-download"></i>
                        Descargar
                    </button>
                </div>
            </div>
            
            <div class="chart-container-modern">
                <canvas id="salesChart"></canvas>
            </div>
            
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #8a2be2, #4a00e0);"></div>
                    <span class="legend-text">Ventas (USD)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #00b894, #00a085);"></div>
                    <span class="legend-text">Ventas (Bs.)</span>
                </div>
            </div>
        </div>

        {{-- Métricas Detalladas --}}
        <div class="detailed-metrics-section">
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i>
                Análisis Detallado
            </h2>
            
            <div class="detailed-metrics-grid">
                {{-- Métodos de Pago --}}
                <div class="detailed-metric-card">
                    <h3 class="metric-card-title">
                        <i class="fas fa-credit-card"></i>
                        Métodos de Pago
                    </h3>
                    <div class="payment-methods-list">
                        @forelse (($stats['payment_methods'] ?? []) as $payment)
                            <div class="payment-method-item">
                                <div class="payment-info">
                                    <span class="payment-name">{{ ucfirst($payment->payment_method) }}</span>
                                    <span class="payment-percentage">
                                        @php
                                            $percentage = ($stats['total_orders'] ?? 0) > 0 ? 
                                                round(($payment->count / $stats['total_orders']) * 100, 1) : 0;
                                        @endphp
                                        {{ $percentage }}%
                                    </span>
                                </div>
                                <div class="payment-bar">
                                    <div class="payment-progress" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="payment-count">{{ $payment->count }} órdenes</span>
                            </div>
                        @empty
                            <div class="no-data-message">
                                <i class="fas fa-receipt"></i>
                                No hay datos de pagos
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Resumen del Reporte --}}
                <div class="detailed-metric-card">
                    <h3 class="metric-card-title">
                        <i class="fas fa-chart-pie"></i>
                        Resumen del Reporte
                    </h3>
                    <div class="report-summary">
                        <div class="summary-item">
                            <i class="fas fa-eye"></i>
                            <div class="summary-content">
                                <span class="summary-label">Tipo de Vista</span>
                                <span class="summary-value view-type">{{ ucfirst($reportType) }}</span>
                            </div>
                        </div>
                        <div class="summary-item">
                            <i class="fas fa-calendar"></i>
                            <div class="summary-content">
                                <span class="summary-label">Período</span>
                                <span class="summary-value date-range">
                                    {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                        @if (($metrics['best_day'] ?? null))
                        <div class="summary-item highlight">
                            <i class="fas fa-trophy"></i>
                            <div class="summary-content">
                                <span class="summary-label">Mejor Día</span>
                                <span class="summary-value">
                                    {{ \Carbon\Carbon::parse($metrics['best_day']->date)->format('d/m/Y') }}
                                    <br>
                                    <small>
                                        ${{ number_format($metrics['best_day']->daily_sales, 2) }} USD
                                        <br>
                                        Bs. {{ number_format($metrics['best_day_ves'] ?? 0, 2) }}
                                    </small>
                                </span>
                            </div>
                        </div>
                        @endif
                        @if (($metrics['largest_sale'] ?? null))
                        <div class="summary-item highlight">
                            <i class="fas fa-star"></i>
                            <div class="summary-content">
                                <span class="summary-label">Venta Más Grande</span>
                                <span class="summary-value">
                                    <small>
                                        ${{ number_format($metrics['largest_sale']->total_amount, 2) }} USD
                                        <br>
                                        Bs. {{ number_format($metrics['largest_sale_ves'] ?? 0, 2) }}
                                    </small>
                                </span>
                            </div>
                        </div>
                        @endif
                        <div class="summary-item">
                            <i class="fas fa-clock"></i>
                            <div class="summary-content">
                                <span class="summary-label">Días Analizados</span>
                                <span class="summary-value">
                                    {{ $startDate->diffInDays($endDate) + 1 }} días
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Información Exclusiva para Administradores --}}
        @if (($userRole === 'admin' || $userRole === 'administrador') && ($metrics['top_ally'] ?? null))
        <div class="admin-section">
            <h2 class="section-title">
                <i class="fas fa-trophy"></i>
                Ranking de Aliados
            </h2>
            
            <div class="admin-metrics-grid">
                <div class="admin-metric-card">
                    <h3 class="metric-card-title">
                        <i class="fas fa-medal"></i>
                        Aliado Destacado
                    </h3>
                    <div class="top-ally-info">
                        <div class="ally-avatar">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="ally-details">
                            <h4 class="ally-name">{{ $metrics['top_ally']->ally->company_name ?? 'N/A' }}</h4>
                            <div class="ally-stats">
                                <div class="ally-stat">
                                    <span class="stat-value">${{ number_format($metrics['top_ally']->total_sales, 2) }}</span>
                                    <span class="stat-label">Ventas USD</span>
                                    <span class="stat-subtext">
                                        Bs. {{ number_format($metrics['top_ally']->total_sales_ves ?? 0, 2) }}
                                    </span>
                                </div>
                                <div class="ally-stat">
                                    <span class="stat-value">{{ $metrics['top_ally']->total_orders }}</span>
                                    <span class="stat-label">Órdenes</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Comparativa de Períodos --}}
        <div class="comparison-section">
            <h2 class="section-title">
                <i class="fas fa-balance-scale"></i>
                Comparativa de Períodos
            </h2>
            
            <div class="comparison-grid">
                <div class="comparison-card">
                    <div class="comparison-period">
                        <h4>Período Actual</h4>
                        <div class="period-dates">
                            {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                        </div>
                    </div>
                    <div class="comparison-stats">
                        <div class="comparison-stat">
                            <span class="stat-value">${{ number_format($stats['total_sales'] ?? 0, 2) }}</span>
                            <span class="stat-label">Ventas USD</span>
                            <span class="stat-subtext">
                                Bs. {{ number_format($stats['total_sales_ves'] ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="comparison-stat">
                            <span class="stat-value">{{ number_format($stats['total_orders'] ?? 0) }}</span>
                            <span class="stat-label">Órdenes</span>
                        </div>
                    </div>
                </div>

                <div class="comparison-card">
                    <div class="comparison-period">
                        <h4>Período Anterior</h4>
                        <div class="period-dates">
                            @php
                                $previousDays = $endDate->diffInDays($startDate);
                                $previousStart = $startDate->copy()->subDays($previousDays);
                                $previousEnd = $startDate->copy()->subDay();
                            @endphp
                            {{ $previousStart->format('d M Y') }} - {{ $previousEnd->format('d M Y') }}
                        </div>
                    </div>
                    <div class="comparison-stats">
                        <div class="comparison-stat">
                            <span class="stat-value">${{ number_format($stats['previous_sales'] ?? 0, 2) }}</span>
                            <span class="stat-label">Ventas USD</span>
                            <span class="stat-subtext">
                                Bs. {{ number_format($stats['previous_sales_ves'] ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="comparison-growth {{ ($stats['growth'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                            <i class="fas fa-arrow-{{ ($stats['growth'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                            {{ number_format(abs($stats['growth'] ?? 0), 1) }}% crecimiento
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Acciones de Exportación --}}
        <div class="export-section">
            <h2 class="section-title">
                <i class="fas fa-download"></i>
                Exportar Reporte
            </h2>
            
            <div class="export-actions">
                <button class="export-btn pdf" id="downloadPdfButton">
                    <i class="fas fa-file-pdf"></i>
                    Exportar PDF
                </button>
                <button class="export-btn excel" id="downloadExcelButton">
                    <i class="fas fa-file-excel"></i>
                    Exportar Excel
                </button>
                <button class="export-btn print" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    Imprimir
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Datos iniciales desde PHP
            const initialLabels = @json($chartData['labels'] ?? []);
            const initialChartData = @json($chartData['data'] ?? []);
            const initialReportType = "{{ $reportType }}";
            const exchangeRate = {{ $exchangeRateVes }};

            // Calcular datos en VES
            const initialChartDataVes = initialChartData.map(amount => amount * exchangeRate);

            // Referencias a elementos del DOM
            const salesChartCanvas = document.getElementById('salesChart');
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const reportTypeSelect = document.getElementById('reportType');
            const applyFilterButton = document.getElementById('applyFilterButton');
            const resetFiltersButton = document.getElementById('resetFiltersButton');
            const filtersToggle = document.getElementById('filtersToggle');
            const filtersContent = document.getElementById('filtersContent');
            const toggleChartType = document.getElementById('toggleChartType');
            const downloadChartBtn = document.getElementById('downloadChart');
            const downloadPdfButton = document.getElementById('downloadPdfButton');
            const downloadExcelButton = document.getElementById('downloadExcelButton');
            const loadingOverlay = document.getElementById('loadingOverlay');

            let salesChart;
            let currentChartType = 'bar';
            let showingVes = false;

            // Función para mostrar/ocultar loading
            function showLoading(show) {
                if (loadingOverlay) {
                    loadingOverlay.style.display = show ? 'flex' : 'none';
                }
            }

            // Toggle de filtros
            if (filtersToggle && filtersContent) {
                filtersToggle.addEventListener('click', function() {
                    filtersContent.classList.toggle('active');
                    const icon = filtersToggle.querySelector('i');
                    icon.classList.toggle('fa-chevron-down');
                    icon.classList.toggle('fa-chevron-up');
                });
            }

            // Función para renderizar el gráfico
            function renderChart(labels, data, dataVes, type = 'bar', showVes = false) {
                if (salesChart) {
                    salesChart.destroy();
                }

                const ctx = salesChartCanvas.getContext('2d');
                const gradientUSD = ctx.createLinearGradient(0, 0, 0, 400);
                gradientUSD.addColorStop(0, 'rgba(138, 43, 226, 0.8)');
                gradientUSD.addColorStop(1, 'rgba(138, 43, 226, 0.2)');

                const gradientVES = ctx.createLinearGradient(0, 0, 0, 400);
                gradientVES.addColorStop(0, 'rgba(0, 184, 148, 0.8)');
                gradientVES.addColorStop(1, 'rgba(0, 184, 148, 0.2)');

                const isLineChart = type === 'line';
                const currentData = showVes ? dataVes : data;
                const currentLabel = showVes ? 'Ventas (Bs.)' : 'Ventas (USD)';
                const currentGradient = showVes ? gradientVES : gradientUSD;
                const currentBorderColor = showVes ? 'rgba(0, 184, 148, 1)' : 'rgba(138, 43, 226, 1)';
                
                salesChart = new Chart(salesChartCanvas, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: currentLabel,
                            data: currentData,
                            backgroundColor: isLineChart ? 'transparent' : currentGradient,
                            borderColor: currentBorderColor,
                            borderWidth: isLineChart ? 3 : 2,
                            borderRadius: isLineChart ? 0 : 8,
                            barThickness: isLineChart ? 'flex' : 35,
                            borderSkipped: false,
                            fill: isLineChart,
                            tension: isLineChart ? 0.4 : 0,
                            pointBackgroundColor: currentBorderColor,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: isLineChart ? 4 : 0,
                            pointHoverRadius: 6
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
                                        const value = context.parsed.y;
                                        if (showVes) {
                                            return `Bs. ${value.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                        } else {
                                            return `$${value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                        }
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
                                        if (showVes) {
                                            return 'Bs. ' + value.toLocaleString('es-VE', {
                                                maximumFractionDigits: 0
                                            });
                                        } else {
                                            return '$' + value.toLocaleString('en-US', {
                                                maximumFractionDigits: 0
                                            });
                                        }
                                    },
                                    color: '#6b7280'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#6b7280',
                                    maxRotation: 45
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

            // Renderizar gráfico inicial si hay datos
            if (salesChartCanvas && initialLabels.length > 0 && initialChartData.length > 0) {
                renderChart(initialLabels, initialChartData, initialChartDataVes, currentChartType, showingVes);
            } else {
                // Mostrar mensaje si no hay datos
                salesChartCanvas.parentElement.innerHTML = `
                    <div class="no-data-message" style="height: 400px; display: flex; align-items: center; justify-content: center;">
                        <div style="text-align: center;">
                            <i class="fas fa-chart-bar" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                            <p style="color: #6b7280; font-size: 1.1rem;">No hay datos disponibles para el período seleccionado</p>
                        </div>
                    </div>
                `;
            }

            // Toggle tipo de gráfico
            if (toggleChartType) {
                toggleChartType.addEventListener('click', function() {
                    if (initialLabels.length === 0) return;
                    
                    // Alternar entre USD y VES
                    showingVes = !showingVes;
                    currentChartType = currentChartType === 'bar' ? 'line' : 'bar';
                    
                    renderChart(initialLabels, initialChartData, initialChartDataVes, currentChartType, showingVes);
                    
                    // Actualizar texto del botón
                    const currencyText = showingVes ? 'Mostrar USD' : 'Mostrar Bs.';
                    this.innerHTML = `<i class="fas fa-exchange-alt"></i> ${currencyText}`;
                });
            }

            // Descargar gráfico como imagen
            if (downloadChartBtn) {
                downloadChartBtn.addEventListener('click', function() {
                    if (initialLabels.length === 0) {
                        alert('No hay datos para descargar');
                        return;
                    }
                    
                    const currencySuffix = showingVes ? 'ves' : 'usd';
                    const link = document.createElement('a');
                    link.download = `grafico-ventas-${currencySuffix}-${new Date().toISOString().split('T')[0]}.png`;
                    link.href = salesChartCanvas.toDataURL();
                    link.click();
                });
            }

            // El resto del código JavaScript permanece igual...
            // (Filtros rápidos, aplicar filtros, reiniciar filtros, exportar, etc.)

            // Filtros rápidos
            document.querySelectorAll('.quick-filter').forEach(button => {
                button.addEventListener('click', function() {
                    const days = parseInt(this.getAttribute('data-days'));
                    const endDate = new Date();
                    const startDate = new Date();
                    
                    if (days === 0) {
                        // Hoy
                        startDate.setDate(endDate.getDate());
                    } else {
                        startDate.setDate(endDate.getDate() - days);
                    }
                    
                    startDateInput.value = startDate.toISOString().split('T')[0];
                    endDateInput.value = endDate.toISOString().split('T')[0];
                    
                    if (days === 0) {
                        reportTypeSelect.value = 'daily';
                    } else if (days <= 7) {
                        reportTypeSelect.value = 'daily';
                    } else if (days <= 30) {
                        reportTypeSelect.value = 'weekly';
                    } else {
                        reportTypeSelect.value = 'monthly';
                    }
                    
                    applyFilters();
                });
            });

            // Aplicar filtros
            function applyFilters() {
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                const reportType = reportTypeSelect.value;

                if (!startDate || !endDate) {
                    alert('Por favor, selecciona ambas fechas');
                    return;
                }

                if (startDate > endDate) {
                    alert('La fecha inicial no puede ser mayor a la fecha final');
                    return;
                }

                showLoading(true);

                // Construir URL con parámetros
                const url = new URL(window.location.href);
                url.searchParams.set('startDate', startDate);
                url.searchParams.set('endDate', endDate);
                url.searchParams.set('reportType', reportType);

                window.location.href = url.toString();
            }

            if (applyFilterButton) {
                applyFilterButton.addEventListener('click', applyFilters);
            }

            // Reiniciar filtros
            if (resetFiltersButton) {
                resetFiltersButton.addEventListener('click', function() {
                    const today = new Date().toISOString().split('T')[0];
                    const thirtyDaysAgo = new Date();
                    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                    
                    startDateInput.value = thirtyDaysAgo.toISOString().split('T')[0];
                    endDateInput.value = today;
                    reportTypeSelect.value = 'monthly';
                    
                    applyFilters();
                });
            }

            // Exportar PDF
            if (downloadPdfButton) {
                downloadPdfButton.addEventListener('click', function() {
                    downloadReport('pdf');
                });
            }

            // Exportar Excel
            if (downloadExcelButton) {
                downloadExcelButton.addEventListener('click', function() {
                    downloadReport('excel');
                });
            }

            function downloadReport(format) {
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                const reportType = reportTypeSelect.value;

                if (!startDate || !endDate) {
                    alert('Por favor, selecciona ambas fechas');
                    return;
                }

                showLoading(true);

                const url = new URL(`{{ route('admin.reports.export') }}`, window.location.origin);
                url.searchParams.append('startDate', startDate);
                url.searchParams.append('endDate', endDate);
                url.searchParams.append('reportType', reportType);
                url.searchParams.append('format', format);

                const downloadLink = document.createElement('a');
                downloadLink.href = url.toString();
                downloadLink.target = '_blank';
                downloadLink.style.display = 'none';

                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);

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

            // Efectos visuales para botones
            document.querySelectorAll('.modern-button, .action-btn, .export-btn, .chart-action-btn').forEach(button => {
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