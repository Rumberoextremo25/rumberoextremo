@extends('layouts.admin')

@section('title', 'Estadísticas de Ventas')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sales-stats.css') }}">
@endpush

@section('content')
<div class="sales-stats-container">
    <!-- Header -->
    <div class="stats-header">
        <div class="stats-header-left">
            <h1 class="stats-title">
                <i class="fa-solid fa-chart-line"></i> Estadísticas de Ventas
            </h1>
            <p class="stats-subtitle">Dashboard de ventas y rendimiento de aliados</p>
        </div>
        <div class="stats-header-right">
            <div class="stats-actions">
                <div class="stats-dropdown">
                    <button class="stats-btn stats-btn-outline dropdown-toggle" id="periodoBtn" data-dropdown="periodo-menu">
                        <i class="fa-solid fa-calendar-alt"></i> 
                        <span id="periodoSeleccionado">{{ request('periodo', 'Este Mes') }}</span>
                    </button>
                    <div class="stats-dropdown-menu" id="periodo-menu">
                        <a href="?periodo=hoy" class="stats-dropdown-item">Hoy</a>
                        <a href="?periodo=7d" class="stats-dropdown-item">Últimos 7 días</a>
                        <a href="?periodo=30d" class="stats-dropdown-item">Últimos 30 días</a>
                        <a href="?periodo=mes" class="stats-dropdown-item">Este Mes</a>
                        <a href="?periodo=anio" class="stats-dropdown-item">Este Año</a>
                    </div>
                </div>
                <button onclick="window.print()" class="stats-btn stats-btn-primary">
                    <i class="fa-solid fa-print"></i> Imprimir
                </button>
                <button onclick="exportarDatos()" class="stats-btn stats-btn-success">
                    <i class="fa-solid fa-file-excel"></i> Exportar
                </button>
            </div>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-grid">
        <div class="stats-card stats-card-primary">
            <div class="stats-card-content">
                <div class="stats-card-info">
                    <span class="stats-card-label">Ventas Totales</span>
                    <span class="stats-card-value">${{ number_format($summary['total_sales'] ?? 0, 0, ',', '.') }}</span>
                    <span class="stats-card-trend trend-up">
                        <i class="fa-solid fa-arrow-up"></i> +12.5%
                    </span>
                </div>
                <div class="stats-card-icon">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
        </div>
        
        <div class="stats-card stats-card-success">
            <div class="stats-card-content">
                <div class="stats-card-info">
                    <span class="stats-card-label">Transacciones</span>
                    <span class="stats-card-value">{{ number_format($summary['total_transactions'] ?? 0, 0, ',', '.') }}</span>
                    <span class="stats-card-trend trend-up">
                        <i class="fa-solid fa-arrow-up"></i> +8.3%
                    </span>
                </div>
                <div class="stats-card-icon">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
            </div>
        </div>
        
        <div class="stats-card stats-card-info">
            <div class="stats-card-content">
                <div class="stats-card-info">
                    <span class="stats-card-label">Ventas Hoy</span>
                    <span class="stats-card-value">${{ number_format($summary['sales_today'] ?? 0, 0, ',', '.') }}</span>
                    <span class="stats-card-trend">
                        <i class="fa-solid fa-clock"></i> Actualizado
                    </span>
                </div>
                <div class="stats-card-icon">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>
        </div>
        
        <div class="stats-card stats-card-warning">
            <div class="stats-card-content">
                <div class="stats-card-info">
                    <span class="stats-card-label">Ticket Promedio</span>
                    <span class="stats-card-value">${{ number_format($summary['average_ticket'] ?? 0, 0, ',', '.') }}</span>
                    <span class="stats-card-trend trend-up">
                        <i class="fa-solid fa-trend-up"></i> +5.2%
                    </span>
                </div>
                <div class="stats-card-icon">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico principal -->
    <div class="stats-row">
        <div class="stats-col stats-col-8">
            <div class="stats-widget">
                <div class="stats-widget-header">
                    <h3 class="stats-widget-title">
                        <i class="fa-solid fa-chart-line"></i> Tendencia de Ventas
                    </h3>
                    <div class="stats-chart-actions">
                        <button class="stats-chart-btn active" data-chart="line">
                            <i class="fa-solid fa-chart-line"></i>
                        </button>
                        <button class="stats-chart-btn" data-chart="bar">
                            <i class="fa-solid fa-chart-bar"></i>
                        </button>
                    </div>
                </div>
                <div class="stats-widget-body">
                    <canvas id="salesChart" height="320"></canvas>
                </div>
            </div>
        </div>
        
        <div class="stats-col stats-col-4">
            <div class="stats-widget">
                <div class="stats-widget-header">
                    <h3 class="stats-widget-title">
                        <i class="fa-solid fa-trophy"></i> Top Aliados
                    </h3>
                </div>
                <div class="stats-widget-body p-0">
                    <div class="stats-ranking-list">
                        @forelse($salesByAlly ?? [] as $index => $sale)
                            <div class="stats-ranking-item">
                                <div class="stats-ranking-number stats-ranking-number-{{ $index + 1 }}">
                                    {{ $index + 1 }}
                                </div>
                                <div class="stats-ranking-info">
                                    <strong>{{ $sale->ally->company_name ?? 'N/A' }}</strong>
                                    <span>{{ $sale->count }} ventas</span>
                                </div>
                                <div class="stats-ranking-amount">
                                    ${{ number_format($sale->total, 0, ',', '.') }}
                                </div>
                            </div>
                        @empty
                            <div class="stats-empty">
                                <i class="fa-solid fa-chart-simple"></i>
                                <p>No hay datos disponibles</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen detallado -->
    <div class="stats-row">
        <div class="stats-col stats-col-12">
            <div class="stats-widget">
                <div class="stats-widget-header">
                    <h3 class="stats-widget-title">
                        <i class="fa-solid fa-chart-pie"></i> Resumen Detallado
                    </h3>
                </div>
                <div class="stats-widget-body">
                    <div class="stats-metrics-grid">
                        <div class="stats-metric-card">
                            <div class="stats-metric-title">Ventas Totales</div>
                            <div class="stats-metric-value">${{ number_format($summary['total_sales'] ?? 0, 0, ',', '.') }}</div>
                            <div class="stats-progress">
                                <div class="stats-progress-bar" style="width: {{ min(($summary['total_sales'] ?? 0) / 10000000 * 100, 100) }}%"></div>
                            </div>
                            <div class="stats-metric-target">Meta: $10.000.000</div>
                        </div>
                        <div class="stats-metric-card">
                            <div class="stats-metric-title">Ventas Este Mes</div>
                            <div class="stats-metric-value">${{ number_format($summary['sales_this_month'] ?? 0, 0, ',', '.') }}</div>
                            <div class="stats-progress">
                                <div class="stats-progress-bar stats-progress-bar-success" style="width: {{ min(($summary['sales_this_month'] ?? 0) / 2000000 * 100, 100) }}%"></div>
                            </div>
                            <div class="stats-metric-target">Meta: $2.000.000</div>
                        </div>
                        <div class="stats-metric-card">
                            <div class="stats-metric-title">Ticket Promedio</div>
                            <div class="stats-metric-value">${{ number_format($summary['average_ticket'] ?? 0, 0, ',', '.') }}</div>
                            <div class="stats-progress">
                                <div class="stats-progress-bar stats-progress-bar-info" style="width: {{ min(($summary['average_ticket'] ?? 0) / 500000 * 100, 100) }}%"></div>
                            </div>
                            <div class="stats-metric-target">Meta: $500.000</div>
                        </div>
                    </div>
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
        
        // Dropdown functionality
        document.querySelectorAll('[data-dropdown]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const menuId = this.getAttribute('data-dropdown');
                const menu = document.getElementById(menuId);
                document.querySelectorAll('.stats-dropdown-menu').forEach(m => {
                    if (m !== menu) m.classList.remove('show');
                });
                menu.classList.toggle('show');
            });
        });
        
        document.addEventListener('click', function() {
            document.querySelectorAll('.stats-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        });
        
        // Chart buttons
        document.querySelectorAll('.stats-chart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.getAttribute('data-chart');
                document.querySelectorAll('.stats-chart-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                cambiarGrafico(type, salesData);
            });
        });
        
        if (salesData.length > 0) {
            crearGrafico(salesData, 'line');
        } else {
            document.getElementById('salesChart').parentElement.innerHTML = '<div class="stats-empty"><i class="fa-solid fa-chart-line"></i><p>No hay datos de ventas disponibles</p></div>';
        }
    });
    
    function crearGrafico(salesData, tipo) {
        const ctx = document.getElementById('salesChart').getContext('2d');
        
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