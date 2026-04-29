@extends('layouts.admin')

@section('title', 'Estadísticas de Ventas')

@push('styles') {{-- Agregamos el CSS específico de esta vista --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sales-stats.css') }}">
@endpush

@section('content')
<div class="sales-dashboard">
    <!-- Header Moderno -->
    <div class="dashboard-header">
        <div class="header-left">
            <div class="header-icon">
                <i class="fa-solid fa-chart-scatter"></i>
            </div>
            <div class="header-info">
                <h1 class="header-title">Estadísticas de Ventas</h1>
                <p class="header-subtitle">Dashboard de rendimiento y análisis de ventas</p>
            </div>
        </div>
        <div class="header-right">
            <div class="date-range-picker">
                <button class="range-btn" id="rangeBtn">
                    <i class="fa-regular fa-calendar"></i>
                    <span id="selectedRange">Este Mes</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="range-dropdown" id="rangeDropdown">
                    <a href="?periodo=hoy" class="range-item">
                        <i class="fa-regular fa-sun"></i> Hoy
                    </a>
                    <a href="?periodo=7d" class="range-item">
                        <i class="fa-regular fa-calendar-week"></i> Últimos 7 días
                    </a>
                    <a href="?periodo=30d" class="range-item">
                        <i class="fa-regular fa-calendar-alt"></i> Últimos 30 días
                    </a>
                    <a href="?periodo=mes" class="range-item active">
                        <i class="fa-regular fa-calendar"></i> Este Mes
                    </a>
                    <a href="?periodo=anio" class="range-item">
                        <i class="fa-regular fa-calendar-plus"></i> Este Año
                    </a>
                </div>
            </div>
            <button onclick="window.print()" class="action-btn action-btn-secondary">
                <i class="fa-solid fa-print"></i>
                <span>Imprimir</span>
            </button>
            <button onclick="exportarDatos()" class="action-btn action-btn-primary">
                <i class="fa-solid fa-chart-simple"></i>
                <span>Exportar</span>
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card kpi-card-purple">
            <div class="kpi-icon">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Ventas Totales</span>
                <span class="kpi-value">${{ number_format($summary['total_sales'] ?? 0, 0, ',', '.') }}</span>
                <span class="kpi-trend positive">
                    <i class="fa-solid fa-arrow-up"></i> +12.5%
                </span>
            </div>
        </div>
        <div class="kpi-card kpi-card-green">
            <div class="kpi-icon">
                <i class="fa-solid fa-credit-card"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Transacciones</span>
                <span class="kpi-value">{{ number_format($summary['total_transactions'] ?? 0, 0, ',', '.') }}</span>
                <span class="kpi-trend positive">
                    <i class="fa-solid fa-arrow-up"></i> +8.3%
                </span>
            </div>
        </div>
        <div class="kpi-card kpi-card-blue">
            <div class="kpi-icon">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Ventas Hoy</span>
                <span class="kpi-value">${{ number_format($summary['sales_today'] ?? 0, 0, ',', '.') }}</span>
                <span class="kpi-trend">
                    <i class="fa-solid fa-clock"></i> Actualizado
                </span>
            </div>
        </div>
        <div class="kpi-card kpi-card-orange">
            <div class="kpi-icon">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Ticket Promedio</span>
                <span class="kpi-value">${{ number_format($summary['average_ticket'] ?? 0, 0, ',', '.') }}</span>
                <span class="kpi-trend positive">
                    <i class="fa-solid fa-trend-up"></i> +5.2%
                </span>
            </div>
        </div>
    </div>

    <!-- Gráfico y Ranking -->
    <div class="dashboard-grid">
        <div class="dashboard-card chart-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-chart-line"></i>
                    <h3>Tendencia de Ventas</h3>
                </div>
                <div class="chart-controls">
                    <button class="chart-control active" data-chart="line">
                        <i class="fa-solid fa-chart-line"></i>
                    </button>
                    <button class="chart-control" data-chart="bar">
                        <i class="fa-solid fa-chart-bar"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="320"></canvas>
            </div>
        </div>

        <div class="dashboard-card ranking-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-trophy"></i>
                    <h3>Top Aliados</h3>
                </div>
                <span class="card-badge">Por ventas</span>
            </div>
            <div class="card-body p-0">
                <div class="ranking-list">
                    @forelse($salesByAlly ?? [] as $index => $sale)
                        <div class="ranking-item">
                            <div class="ranking-position {{ $index < 3 ? 'top' : '' }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="ranking-info">
                                <div class="ranking-name">{{ $sale->ally->company_name ?? 'N/A' }}</div>
                                <div class="ranking-stats">
                                    <span><i class="fa-regular fa-chart-line"></i> {{ $sale->count }} ventas</span>
                                </div>
                            </div>
                            <div class="ranking-value">${{ number_format($sale->total, 0, ',', '.') }}</div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fa-regular fa-chart-line"></i>
                            <p>No hay datos disponibles</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Métricas -->
    <div class="metrics-section">
        <div class="metrics-header">
            <h3><i class="fa-solid fa-chart-pie"></i> Resumen Detallado</h3>
        </div>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-header">
                    <span class="metric-title">Ventas Totales</span>
                    <span class="metric-value">${{ number_format($summary['total_sales'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="metric-progress">
                    <div class="progress-bar" style="width: {{ min(($summary['total_sales'] ?? 0) / 10000000 * 100, 100) }}%"></div>
                </div>
                <div class="metric-footer">
                    <span>Meta: $10.000.000</span>
                    <span class="metric-percent">{{ round(min(($summary['total_sales'] ?? 0) / 10000000 * 100, 100)) }}%</span>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <span class="metric-title">Ventas Este Mes</span>
                    <span class="metric-value">${{ number_format($summary['sales_this_month'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="metric-progress">
                    <div class="progress-bar success" style="width: {{ min(($summary['sales_this_month'] ?? 0) / 2000000 * 100, 100) }}%"></div>
                </div>
                <div class="metric-footer">
                    <span>Meta: $2.000.000</span>
                    <span class="metric-percent">{{ round(min(($summary['sales_this_month'] ?? 0) / 2000000 * 100, 100)) }}%</span>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <span class="metric-title">Ticket Promedio</span>
                    <span class="metric-value">${{ number_format($summary['average_ticket'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="metric-progress">
                    <div class="progress-bar info" style="width: {{ min(($summary['average_ticket'] ?? 0) / 500000 * 100, 100) }}%"></div>
                </div>
                <div class="metric-footer">
                    <span>Meta: $500.000</span>
                    <span class="metric-percent">{{ round(min(($summary['average_ticket'] ?? 0) / 500000 * 100, 100)) }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let salesChart = null;
    let currentChartType = 'line';
    
    document.addEventListener('DOMContentLoaded', function() {
        const salesData = @json($salesByDay ?? []);
        
        // Dropdown de rango
        const rangeBtn = document.getElementById('rangeBtn');
        const rangeDropdown = document.getElementById('rangeDropdown');
        
        if (rangeBtn && rangeDropdown) {
            rangeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                rangeDropdown.classList.toggle('show');
            });
            
            document.addEventListener('click', function() {
                rangeDropdown.classList.remove('show');
            });
        }
        
        // Controles del gráfico
        document.querySelectorAll('.chart-control').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.getAttribute('data-chart');
                document.querySelectorAll('.chart-control').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                cambiarGrafico(type, salesData);
            });
        });
        
        if (salesData.length > 0) {
            crearGrafico(salesData, 'line');
        } else {
            const chartContainer = document.querySelector('.chart-card .card-body');
            if (chartContainer) {
                chartContainer.innerHTML = '<div class="empty-state"><i class="fa-regular fa-chart-line"></i><p>No hay datos de ventas disponibles</p></div>';
            }
        }
    });
    
    function crearGrafico(salesData, tipo) {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;
        
        if (salesChart) salesChart.destroy();
        
        salesChart = new Chart(ctx, {
            type: tipo,
            data: {
                labels: salesData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
                }),
                datasets: [{
                    label: 'Ventas ($)',
                    data: salesData.map(item => item.total),
                    borderColor: '#764ba2',
                    backgroundColor: tipo === 'line' ? 'rgba(118, 75, 162, 0.1)' : '#764ba2',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: tipo === 'line',
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: tipo === 'line' ? 4 : 0,
                    pointHoverRadius: 6,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 10 } },
                    tooltip: { callbacks: { label: function(ctx) { return 'Ventas: $' + new Intl.NumberFormat('es-CO').format(ctx.raw); } } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#e2e8f0' }, ticks: { callback: function(v) { return '$' + new Intl.NumberFormat('es-CO').format(v); } } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
    
    function cambiarGrafico(tipo, salesData) {
        currentChartType = tipo;
        if (salesData.length > 0) crearGrafico(salesData, tipo);
    }
    
    function exportarDatos() {
        window.location.href = '{{ route("admin.reports.sales.export") }}?formato=excel';
    }
</script>
@endsection