@extends('layouts.admin')

@section('page_title_toolbar', 'Reportes de Ventas')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/reports.css') }}">
@endpush

@section('content')
    <div class="reports-wrapper">
        {{-- Header con bienvenida --}}
        <div class="reports-header-bar">
            <div class="header-left">
                <a href="{{ route('dashboard') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Dashboard</span>
                </a>
                <div class="page-title">
                    <span class="title-main">Análisis de</span>
                    <span class="title-accent">Ventas</span>
                </div>
            </div>
            <div class="header-actions">
                <div class="user-greeting">
                    <span>Bienvenido,</span>
                    <strong>{{ Auth::user()->name }}</strong>
                </div>
                <div class="avatar-circle">
                    <span>{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
            </div>
        </div>

        {{-- Información del Contexto del Usuario --}}
        <div class="context-bar">
            @if ($userRole === 'admin' || $userRole === 'administrador')
                <div class="context-badge admin">
                    <i class="fas fa-crown"></i>
                    <span>Vista Administrador - Acceso completo a todas las ventas</span>
                </div>
            @elseif($allyId && ($metrics['current_ally_info'] ?? null))
                <div class="context-badge ally">
                    <i class="fas fa-handshake"></i>
                    <span>{{ $metrics['current_ally_info']->company_name }} - Panel de control del aliado</span>
                </div>
            @else
                <div class="context-badge user">
                    <i class="fas fa-user"></i>
                    <span>Vista Usuario - Tus ventas personales</span>
                </div>
            @endif
            
            <div class="real-time-indicator">
                <i class="fas fa-sync-alt"></i>
                <span>Actualizado: {{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        {{-- Panel de Filtros --}}
        <div class="filters-panel">
            <div class="filters-header" id="filtersHeader">
                <div class="filters-title">
                    <i class="fas fa-sliders-h"></i>
                    <h3>Filtros y Configuración</h3>
                </div>
                <button class="filters-toggle" id="filtersToggle">
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
                        <div class="date-range">
                            <div class="date-input">
                                <input type="date" id="startDate" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                            </div>
                            <span class="date-separator">→</span>
                            <div class="date-input">
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
                        <div class="select-wrapper">
                            <select id="reportType" class="form-select">
                                <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Vista Mensual</option>
                                <option value="weekly" {{ $reportType == 'weekly' ? 'selected' : '' }}>Vista Semanal</option>
                                <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Vista Diaria</option>
                            </select>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>

                    {{-- Filtro por Aliado (Solo admin) --}}
                    @if ($userRole === 'admin' || $userRole === 'administrador')
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-handshake"></i>
                            Filtrar por Aliado
                        </label>
                        <div class="select-wrapper">
                            <select id="allyFilter" class="form-select">
                                <option value="">Todos los Aliados</option>
                                @foreach($allies ?? [] as $ally)
                                    <option value="{{ $ally->id }}" 
                                        {{ ($selectedAllyId ?? '') == $ally->id ? 'selected' : '' }}>
                                        {{ $ally->company_name }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    @endif

                    {{-- Filtro por Zona --}}
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Filtrar por Zona
                        </label>
                        <div class="select-wrapper">
                            <select id="zoneFilter" class="form-select">
                                <option value="">Todas las Zonas</option>
                                @foreach($zones ?? [] as $zone)
                                    <option value="{{ $zone->id }}" 
                                        {{ ($selectedZoneId ?? '') == $zone->id ? 'selected' : '' }}>
                                        {{ $zone->name }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                {{-- Acciones rápidas --}}
                <div class="quick-actions">
                    <button class="quick-action-btn" data-days="7">
                        <i class="fas fa-calendar-week"></i>
                        Últimos 7 días
                    </button>
                    <button class="quick-action-btn" data-days="30">
                        <i class="fas fa-calendar-alt"></i>
                        Últimos 30 días
                    </button>
                    <button class="quick-action-btn today" data-days="0">
                        <i class="fas fa-calendar-day"></i>
                        Hoy
                    </button>
                </div>

                {{-- Botones de acción --}}
                <div class="filter-actions">
                    <button class="btn-reset" id="resetFiltersButton">
                        <i class="fas fa-redo-alt"></i>
                        Reiniciar
                    </button>
                    <button class="btn-apply" id="applyFilterButton">
                        <i class="fas fa-search"></i>
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>

        {{-- Métricas Principales --}}
        <div class="metrics-grid">
            <div class="metric-card primary">
                <div class="metric-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="metric-content">
                    <span class="metric-label">Ventas Totales USD</span>
                    <span class="metric-value">${{ number_format($stats['total_sales'] ?? 0, 2) }}</span>
                    <span class="metric-sub">Bs. {{ number_format($stats['total_sales_ves'] ?? 0, 2) }}</span>
                    <div class="metric-trend {{ ($stats['growth'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        <i class="fas fa-arrow-{{ ($stats['growth'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                        {{ number_format(abs($stats['growth'] ?? 0), 1) }}%
                    </div>
                </div>
            </div>

            <div class="metric-card success">
                <div class="metric-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="metric-content">
                    <span class="metric-label">Total Órdenes</span>
                    <span class="metric-value">{{ number_format($stats['total_orders'] ?? 0) }}</span>
                    @if(($stats['average_order_value'] ?? 0) > 0)
                        <span class="metric-sub">Prom. ${{ number_format($stats['average_order_value'], 2) }}</span>
                    @endif
                </div>
            </div>

            <div class="metric-card warning">
                <div class="metric-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="metric-content">
                    <span class="metric-label">Clientes Únicos</span>
                    <span class="metric-value">{{ number_format($stats['unique_clients'] ?? 0) }}</span>
                    @if(($stats['repeat_customers'] ?? 0) > 0)
                        <span class="metric-sub">{{ $stats['repeat_customers'] }} recurrentes</span>
                    @endif
                </div>
            </div>

            <div class="metric-card info">
                <div class="metric-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="metric-content">
                    <span class="metric-label">Tasa BNC</span>
                    <span class="metric-value">{{ number_format($exchangeRateVes, 2) }}</span>
                    <span class="metric-sub">Bs. por USD</span>
                </div>
            </div>
        </div>

        {{-- Filtros Aplicados --}}
        @if(($selectedAllyName ?? null) || ($selectedZoneName ?? null))
        <div class="applied-filters">
            <span class="applied-label">
                <i class="fas fa-filter"></i>
                Filtros aplicados:
            </span>
            @if($selectedAllyName ?? null)
            <div class="filter-tag">
                <span class="tag-label">Aliado:</span>
                <span class="tag-value">{{ $selectedAllyName }}</span>
                <button class="tag-remove" data-filter="ally">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endif
            @if($selectedZoneName ?? null)
            <div class="filter-tag">
                <span class="tag-label">Zona:</span>
                <span class="tag-value">{{ $selectedZoneName }}</span>
                <button class="tag-remove" data-filter="zone">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endif
        </div>
        @endif

        {{-- Gráfico Principal --}}
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">
                    <i class="fas fa-chart-line"></i>
                    Tendencias de Ventas
                </h3>
                <div class="chart-actions">
                    <div class="chart-type-toggle">
                        <button class="type-btn active" id="toggleChartType">
                            <i class="fas fa-chart-bar"></i>
                        </button>
                        <button class="type-btn" id="toggleCurrencyBtn">
                            <i class="fas fa-dollar-sign"></i>
                        </button>
                    </div>
                    <button class="chart-download" id="downloadChart" title="Descargar gráfico">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- Grid de Análisis Detallado --}}
        <div class="analysis-grid">
            {{-- Métodos de Pago --}}
            <div class="analysis-card">
                <h3 class="analysis-title">
                    <i class="fas fa-credit-card"></i>
                    Métodos de Pago
                </h3>
                <div class="payment-list">
                    @forelse (($stats['payment_methods'] ?? []) as $payment)
                        @php
                            $percentage = ($stats['total_orders'] ?? 0) > 0 ? 
                                round(($payment->count / $stats['total_orders']) * 100, 1) : 0;
                        @endphp
                        <div class="payment-item">
                            <div class="payment-info">
                                <span class="payment-name">{{ ucfirst($payment->payment_method) }}</span>
                                <span class="payment-percentage">{{ $percentage }}%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="payment-count">{{ $payment->count }} órdenes</span>
                        </div>
                    @empty
                        <div class="empty-data">
                            <i class="fas fa-receipt"></i>
                            <p>No hay datos de pagos</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Resumen del Reporte --}}
            <div class="analysis-card">
                <h3 class="analysis-title">
                    <i class="fas fa-clipboard-list"></i>
                    Resumen del Reporte
                </h3>
                <div class="summary-list">
                    <div class="summary-item">
                        <i class="fas fa-eye"></i>
                        <span class="summary-label">Tipo de Vista</span>
                        <span class="summary-value view-badge">{{ ucfirst($reportType) }}</span>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-calendar"></i>
                        <span class="summary-label">Período</span>
                        <span class="summary-value">{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</span>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-clock"></i>
                        <span class="summary-label">Días Analizados</span>
                        <span class="summary-value">{{ $startDate->diffInDays($endDate) + 1 }} días</span>
                    </div>
                    
                    @if (($metrics['best_day'] ?? null))
                    <div class="summary-item highlight">
                        <i class="fas fa-trophy"></i>
                        <span class="summary-label">Mejor Día</span>
                        <span class="summary-value">
                            {{ \Carbon\Carbon::parse($metrics['best_day']->date)->format('d/m/Y') }}
                            <small>${{ number_format($metrics['best_day']->daily_sales, 2) }}</small>
                        </span>
                    </div>
                    @endif
                    
                    @if (($metrics['largest_sale'] ?? null))
                    <div class="summary-item highlight">
                        <i class="fas fa-star"></i>
                        <span class="summary-label">Venta Más Grande</span>
                        <span class="summary-value">
                            <small>${{ number_format($metrics['largest_sale']->total_amount, 2) }}</small>
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Comparativa de Períodos --}}
        <div class="comparison-card">
            <h3 class="comparison-title">
                <i class="fas fa-balance-scale"></i>
                Comparativa de Períodos
            </h3>
            
            <div class="comparison-grid">
                <div class="period-card">
                    <div class="period-header">
                        <h4>Período Actual</h4>
                        <span class="period-dates">{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</span>
                    </div>
                    <div class="period-stats">
                        <div class="period-stat">
                            <span class="stat-label">Ventas USD</span>
                            <span class="stat-value">${{ number_format($stats['total_sales'] ?? 0, 2) }}</span>
                            <span class="stat-sub">Bs. {{ number_format($stats['total_sales_ves'] ?? 0, 2) }}</span>
                        </div>
                        <div class="period-stat">
                            <span class="stat-label">Órdenes</span>
                            <span class="stat-value">{{ number_format($stats['total_orders'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="period-card">
                    <div class="period-header">
                        <h4>Período Anterior</h4>
                        <span class="period-dates">
                            @php
                                $previousDays = $endDate->diffInDays($startDate);
                                $previousStart = $startDate->copy()->subDays($previousDays);
                                $previousEnd = $startDate->copy()->subDay();
                            @endphp
                            {{ $previousStart->format('d M Y') }} - {{ $previousEnd->format('d M Y') }}
                        </span>
                    </div>
                    <div class="period-stats">
                        <div class="period-stat">
                            <span class="stat-label">Ventas USD</span>
                            <span class="stat-value">${{ number_format($stats['previous_sales'] ?? 0, 2) }}</span>
                        </div>
                        <div class="growth-indicator {{ ($stats['growth'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                            <i class="fas fa-arrow-{{ ($stats['growth'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                            {{ number_format(abs($stats['growth'] ?? 0), 1) }}% crecimiento
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección Admin - Ranking de Aliados --}}
        @if (($userRole === 'admin' || $userRole === 'administrador') && ($metrics['top_ally'] ?? null))
        <div class="admin-section">
            <h3 class="section-title">
                <i class="fas fa-trophy"></i>
                Aliado Destacado
            </h3>
            
            <div class="top-ally-card">
                <div class="ally-rank">#1</div>
                <div class="ally-info">
                    <div class="ally-avatar">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="ally-details">
                        <h4 class="ally-name">{{ $metrics['top_ally']->ally->company_name ?? 'N/A' }}</h4>
                        <div class="ally-stats">
                            <div class="ally-stat">
                                <span class="stat-label">Ventas USD</span>
                                <span class="stat-value">${{ number_format($metrics['top_ally']->total_sales, 2) }}</span>
                            </div>
                            <div class="ally-stat">
                                <span class="stat-label">Órdenes</span>
                                <span class="stat-value">{{ $metrics['top_ally']->total_orders }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Acciones de Exportación --}}
        <div class="export-section">
            <h3 class="export-title">
                <i class="fas fa-download"></i>
                Exportar Reporte
            </h3>
            <div class="export-buttons">
                <button class="export-btn pdf" id="downloadPdfButton">
                    <i class="fas fa-file-pdf"></i>
                    PDF
                </button>
                <button class="export-btn excel" id="downloadExcelButton">
                    <i class="fas fa-file-excel"></i>
                    Excel
                </button>
                <button class="export-btn print" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    Imprimir
                </button>
            </div>
        </div>
    </div>

    {{-- Loading Overlay --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Generando reporte...</span>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Datos iniciales
            const initialLabels = @json($chartData['labels'] ?? []);
            const initialChartData = @json($chartData['data'] ?? []);
            const exchangeRate = {{ $exchangeRateVes }};
            const initialChartDataVes = initialChartData.map(amount => amount * exchangeRate);

            // Referencias DOM
            const salesChartCanvas = document.getElementById('salesChart');
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const reportTypeSelect = document.getElementById('reportType');
            const allyFilter = document.getElementById('allyFilter');
            const zoneFilter = document.getElementById('zoneFilter');
            const applyFilterButton = document.getElementById('applyFilterButton');
            const resetFiltersButton = document.getElementById('resetFiltersButton');
            const filtersToggle = document.getElementById('filtersToggle');
            const filtersContent = document.getElementById('filtersContent');
            const toggleChartType = document.getElementById('toggleChartType');
            const toggleCurrencyBtn = document.getElementById('toggleCurrencyBtn');
            const downloadChartBtn = document.getElementById('downloadChart');
            const downloadPdfButton = document.getElementById('downloadPdfButton');
            const downloadExcelButton = document.getElementById('downloadExcelButton');
            const loadingOverlay = document.getElementById('loadingOverlay');

            let salesChart;
            let currentChartType = 'bar';
            let showingVes = false;

            // Toggle filtros
            if (filtersToggle && filtersContent) {
                filtersToggle.addEventListener('click', function() {
                    filtersContent.classList.toggle('active');
                    const icon = filtersToggle.querySelector('i');
                    icon.classList.toggle('fa-chevron-down');
                    icon.classList.toggle('fa-chevron-up');
                });
            }

            // Función para renderizar gráfico
            function renderChart(labels, data, dataVes, type = 'bar', showVes = false) {
                if (salesChart) salesChart.destroy();

                const ctx = salesChartCanvas.getContext('2d');
                const currentData = showVes ? dataVes : data;
                const currentLabel = showVes ? 'Ventas (Bs.)' : 'Ventas (USD)';
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                
                if (showVes) {
                    gradient.addColorStop(0, 'rgba(0, 184, 148, 0.8)');
                    gradient.addColorStop(1, 'rgba(0, 184, 148, 0.1)');
                } else {
                    gradient.addColorStop(0, 'rgba(138, 43, 226, 0.8)');
                    gradient.addColorStop(1, 'rgba(138, 43, 226, 0.1)');
                }

                salesChart = new Chart(salesChartCanvas, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: currentLabel,
                            data: currentData,
                            backgroundColor: gradient,
                            borderColor: showVes ? '#00b894' : '#8a2be2',
                            borderWidth: 2,
                            borderRadius: 8,
                            fill: type === 'line' ? false : true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const value = context.parsed.y;
                                        return showVes 
                                            ? `Bs. ${value.toLocaleString('es-VE', {minimumFractionDigits: 2})}`
                                            : `$${value.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) => showVes 
                                        ? 'Bs. ' + value.toLocaleString('es-VE')
                                        : '$' + value.toLocaleString('en-US')
                                }
                            }
                        }
                    }
                });
            }

            // Render inicial
            if (salesChartCanvas && initialLabels.length > 0) {
                renderChart(initialLabels, initialChartData, initialChartDataVes, currentChartType, showingVes);
            }

            // Toggle tipo de gráfico
            if (toggleChartType) {
                toggleChartType.addEventListener('click', function() {
                    currentChartType = currentChartType === 'bar' ? 'line' : 'bar';
                    renderChart(initialLabels, initialChartData, initialChartDataVes, currentChartType, showingVes);
                    this.classList.toggle('active');
                });
            }

            // Toggle moneda
            if (toggleCurrencyBtn) {
                toggleCurrencyBtn.addEventListener('click', function() {
                    showingVes = !showingVes;
                    renderChart(initialLabels, initialChartData, initialChartDataVes, currentChartType, showingVes);
                    this.innerHTML = showingVes ? '<i class="fas fa-bs"></i>' : '<i class="fas fa-dollar-sign"></i>';
                });
            }

            // Descargar gráfico
            if (downloadChartBtn) {
                downloadChartBtn.addEventListener('click', () => {
                    const link = document.createElement('a');
                    link.download = `grafico-ventas-${new Date().toISOString().split('T')[0]}.png`;
                    link.href = salesChartCanvas.toDataURL();
                    link.click();
                });
            }

            // Filtros rápidos
            document.querySelectorAll('.quick-action-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const days = parseInt(this.dataset.days);
                    const end = new Date();
                    const start = new Date();
                    
                    if (days === 0) {
                        // Hoy
                        start.setDate(end.getDate());
                        reportTypeSelect.value = 'daily';
                    } else {
                        start.setDate(end.getDate() - days);
                        if (days <= 7) reportTypeSelect.value = 'daily';
                        else if (days <= 30) reportTypeSelect.value = 'weekly';
                        else reportTypeSelect.value = 'monthly';
                    }
                    
                    startDateInput.value = start.toISOString().split('T')[0];
                    endDateInput.value = end.toISOString().split('T')[0];
                    applyFilters();
                });
            });

            // Aplicar filtros
            function applyFilters() {
                const params = new URLSearchParams({
                    startDate: startDateInput.value,
                    endDate: endDateInput.value,
                    reportType: reportTypeSelect.value,
                    ...(allyFilter?.value && { ally_id: allyFilter.value }),
                    ...(zoneFilter?.value && { zone_id: zoneFilter.value })
                });
                
                loadingOverlay?.classList.add('active');
                window.location.href = window.location.pathname + '?' + params.toString();
            }

            applyFilterButton?.addEventListener('click', applyFilters);

            // Reset filtros
            resetFiltersButton?.addEventListener('click', () => {
                const today = new Date().toISOString().split('T')[0];
                const thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                
                startDateInput.value = thirtyDaysAgo;
                endDateInput.value = today;
                reportTypeSelect.value = 'monthly';
                if (allyFilter) allyFilter.value = '';
                if (zoneFilter) zoneFilter.value = '';
                applyFilters();
            });

            // Remover tags
            document.querySelectorAll('.tag-remove').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (this.dataset.filter === 'ally' && allyFilter) allyFilter.value = '';
                    if (this.dataset.filter === 'zone' && zoneFilter) zoneFilter.value = '';
                    applyFilters();
                });
            });

            // Exportar reportes
            function downloadReport(format) {
                const params = new URLSearchParams({
                    startDate: startDateInput.value,
                    endDate: endDateInput.value,
                    reportType: reportTypeSelect.value,
                    format: format,
                    ...(allyFilter?.value && { ally_id: allyFilter.value }),
                    ...(zoneFilter?.value && { zone_id: zoneFilter.value })
                });

                loadingOverlay?.classList.add('active');
                window.location.href = `{{ route('admin.reports.export') }}?${params.toString()}`;
                
                setTimeout(() => loadingOverlay?.classList.remove('active'), 2000);
            }

            downloadPdfButton?.addEventListener('click', () => downloadReport('pdf'));
            downloadExcelButton?.addEventListener('click', () => downloadReport('excel'));

            // Hover effects
            document.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('mouseenter', function() { this.style.transform = 'translateY(-2px)'; });
                btn.addEventListener('mouseleave', function() { this.style.transform = 'translateY(0)'; });
            });
        });
    </script>
@endpush