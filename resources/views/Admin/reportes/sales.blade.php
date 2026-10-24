@extends('layouts.admin')

@section('title', 'Reportes de Ventas')

@section('page_title_toolbar', 'Reportes de Ventas')

@section('content')
    @php
        $isAdmin = in_array($userRole, ['admin', 'administrador'], true);
        $growth = (float) ($stats['growth'] ?? 0);
        $previousDays = max(1, (int) $startDate->diffInDays($endDate) + 1);
        $previousStart = $startDate->copy()->subDays($previousDays);
        $previousEnd = $startDate->copy()->subSecond();
        $paymentLabels = [
            'pago_movil' => 'Pago móvil',
            'transferencia' => 'Transferencia',
            'transferencia_bancaria' => 'Transferencia',
            'transfer' => 'Transferencia',
            'card' => 'Tarjeta',
            'tarjeta' => 'Tarjeta',
            'cash' => 'Efectivo',
            'efectivo' => 'Efectivo',
        ];
        $viewLabels = ['monthly' => 'Mensual', 'weekly' => 'Semanal', 'daily' => 'Diario'];
        $hasChartData = !empty($chartData['labels'] ?? []) && collect($chartData['data'] ?? [])->sum() > 0;
    @endphp

    <div class="cm-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Reportes de <span class="title-accent">ventas</span></h1>
                <p class="page-subtitle">
                    Analiza el rendimiento por periodo, aliado y método de pago.
                </p>
            </div>

            <div class="header-actions">
                <button type="button" class="btn btn-secondary" id="downloadPdfButton">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </button>
                <button type="button" class="btn btn-secondary" id="downloadExcelButton">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> Imprimir
                </button>
            </div>
        </div>

        {{-- ============================ CONTEXTO ============================ --}}
        <div class="cm-filters">
            <span class="cm-chip {{ $isAdmin ? 'cm-chip--primary' : '' }}">
                <i class="fa-solid {{ $isAdmin ? 'fa-crown' : 'fa-handshake' }}"></i>
                {{ $isAdmin ? 'Vista administrador' : ($selectedAllyName ?: 'Vista aliado') }}
            </span>

            <span class="cm-filters__count">
                <i class="fa-regular fa-clock"></i> Actualizado {{ now()->format('d/m/Y H:i') }}
            </span>
        </div>

        {{-- ============================= FILTROS ============================= --}}
        <section class="cf-card">
            <div class="cf-card__head">
                <span class="cf-card__icon"><i class="fa-solid fa-sliders"></i></span>
                <div class="cf-card__head-text">
                    <h2>Filtros del reporte</h2>
                    <p>Rango de fechas, agrupación y segmentos.</p>
                </div>
            </div>

            <div class="cf-card__body">
                <div class="cf-grid">
                    <div class="form-group">
                        <label for="startDate">Desde</label>
                        <input type="date" id="startDate" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label for="endDate">Hasta</label>
                        <input type="date" id="endDate" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label for="reportType">Agrupar por</label>
                        <select id="reportType" class="form-control">
                            <option value="monthly" @selected($reportType === 'monthly')>Mensual</option>
                            <option value="weekly" @selected($reportType === 'weekly')>Semanal</option>
                            <option value="daily" @selected($reportType === 'daily')>Diario</option>
                        </select>
                    </div>

                    @if ($isAdmin)
                        <div class="form-group">
                            <label for="allyFilter">Aliado</label>
                            <select id="allyFilter" class="form-control">
                                <option value="">Todos los aliados</option>
                                @foreach ($allies ?? [] as $ally)
                                    <option value="{{ $ally->id }}" @selected((string) $selectedAllyId === (string) $ally->id)>
                                        {{ $ally->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="zoneFilter">Zona</label>
                        <select id="zoneFilter" class="form-control">
                            <option value="">Todas las zonas</option>
                            @foreach ($zones ?? [] as $zone)
                                <option value="{{ $zone->id }}" @selected((string) $selectedZone === (string) $zone->id)>
                                    {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="cf-actions" style="justify-content:space-between;">
                    <div class="db-quick" style="padding:0;grid-template-columns:repeat(3,max-content);">
                        <button type="button" class="btn btn-secondary btn-sm quick-action-btn" data-days="7">
                            <i class="fa-solid fa-calendar-week"></i> 7 días
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm quick-action-btn" data-days="30">
                            <i class="fa-solid fa-calendar-alt"></i> 30 días
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm quick-action-btn" data-days="0">
                            <i class="fa-solid fa-calendar-day"></i> Hoy
                        </button>
                    </div>

                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        <button type="button" class="btn btn-secondary" id="resetFiltersButton">
                            <i class="fa-solid fa-rotate-left"></i> Reiniciar
                        </button>
                        <button type="button" class="btn btn-primary" id="applyFilterButton">
                            <i class="fa-solid fa-magnifying-glass"></i> Aplicar
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================ MÉTRICAS ============================ --}}
        <div class="cm-stats">
            <div class="cm-stat">
                <span class="cm-stat__icon"><i class="fa-solid fa-sack-dollar"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">${{ number_format($stats['total_sales'] ?? 0, 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Ventas totales</span>
                    <span class="cm-stat__delta {{ $growth >= 0 ? 'is-up' : 'is-down' }}">
                        <i class="fa-solid {{ $growth >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                        {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}% vs. anterior
                    </span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--green"><i class="fa-solid fa-cart-shopping"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($stats['total_orders'] ?? 0) }}</span>
                    <span class="cm-stat__label">Órdenes</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-receipt"></i> Completadas</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gold"><i class="fa-solid fa-users"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($stats['unique_clients'] ?? 0) }}</span>
                    <span class="cm-stat__label">Clientes únicos</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-user-group"></i> En el periodo</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gray"><i class="fa-solid fa-ticket"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">${{ number_format($stats['average_order_value'] ?? 0, 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Promedio por orden</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-divide"></i> Ticket medio</span>
                </span>
            </div>
        </div>

        {{-- Filtros aplicados --}}
        @if (($selectedAllyName ?? null) || ($selectedZoneName ?? null))
            <div class="cm-filters">
                <span class="cm-chip"><i class="fa-solid fa-filter"></i> Filtros aplicados</span>
                @if ($selectedAllyName)
                    <span class="cm-chip">
                        {{ $selectedAllyName }}
                        <button type="button" class="tag-remove" data-filter="ally" aria-label="Quitar filtro de aliado">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </span>
                @endif
                @if ($selectedZoneName)
                    <span class="cm-chip">
                        {{ $selectedZoneName }}
                        <button type="button" class="tag-remove" data-filter="zone" aria-label="Quitar filtro de zona">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </span>
                @endif
            </div>
        @endif

        {{-- ============================ GRÁFICO ============================ --}}
        <section class="cf-card">
            <div class="cf-card__head">
                <span class="cf-card__icon"><i class="fa-solid fa-chart-line"></i></span>
                <div class="cf-card__head-text">
                    <h2>Tendencias de ventas</h2>
                    <p>{{ $startDate->format('d/m/Y') }} – {{ $endDate->format('d/m/Y') }}</p>
                </div>

                <div class="ss-toggle" style="margin-inline-start:auto;">
                    <button type="button" class="ss-toggle__btn is-active" id="toggleChartType" title="Cambiar gráfico">
                        <i class="fa-solid fa-chart-bar"></i>
                    </button>
                    <button type="button" class="ss-toggle__btn" id="downloadChart" title="Descargar gráfico">
                        <i class="fa-solid fa-download"></i>
                    </button>
                </div>
            </div>

            <div class="cf-card__body">
                @if ($hasChartData)
                    <div class="ss-chart">
                        <canvas id="salesChart"></canvas>
                    </div>
                @else
                    <div class="cm-empty" style="border:0;border-radius:0;">
                        <i class="fa-solid fa-chart-line"></i>
                        <h3>Sin ventas en este periodo</h3>
                        <p>Prueba con un rango de fechas más amplio o quita los filtros.</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- =========================== ANÁLISIS =========================== --}}
        <div class="db-grid">
            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-credit-card"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Métodos de pago</h2>
                        <p>Participación sobre las órdenes del periodo.</p>
                    </div>
                </div>

                <div class="db-dist">
                    @forelse (($stats['payment_methods'] ?? []) as $payment)
                        @php
                            $percentage = ($stats['total_orders'] ?? 0) > 0
                                ? round(($payment->count / $stats['total_orders']) * 100, 1)
                                : 0;
                            $paymentLabel = $paymentLabels[$payment->payment_method] ?? ucfirst((string) $payment->payment_method);
                        @endphp
                        <div class="db-dist__row">
                            <div class="db-dist__top">
                                <span class="db-dist__name">{{ $paymentLabel ?: 'Sin método' }}</span>
                                <span class="db-dist__count">{{ $payment->count }} · {{ $percentage }}%</span>
                            </div>
                            <div class="db-bar">
                                <span style="width: {{ $percentage }}%"></span>
                            </div>
                        </div>
                    @empty
                        <div class="cm-empty" style="border:0;border-radius:0;">
                            <i class="fa-solid fa-receipt"></i>
                            <h3>Sin datos de pagos</h3>
                            <p>No hay órdenes registradas en este periodo.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-clipboard-list"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Resumen del reporte</h2>
                    </div>
                </div>

                <div class="pf-rows">
                    <div class="pf-row">
                        <span class="pf-row__label">Tipo de vista</span>
                        <span class="pf-row__value">{{ $viewLabels[$reportType] ?? ucfirst($reportType) }}</span>
                    </div>
                    <div class="pf-row">
                        <span class="pf-row__label">Periodo</span>
                        <span class="pf-row__value">
                            {{ $startDate->format('d/m/Y') }} – {{ $endDate->format('d/m/Y') }}
                        </span>
                    </div>
                    <div class="pf-row">
                        <span class="pf-row__label">Días analizados</span>
                        <span class="pf-row__value">{{ $previousDays }} días</span>
                    </div>

                    @if ($metrics['best_day'] ?? null)
                        <div class="pf-row">
                            <span class="pf-row__label">Mejor día</span>
                            <span class="pf-row__value">
                                {{ \Carbon\Carbon::parse($metrics['best_day']->date)->format('d/m/Y') }}
                                · ${{ number_format($metrics['best_day']->daily_sales, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    @if ($metrics['largest_sale'] ?? null)
                        <div class="pf-row">
                            <span class="pf-row__label">Venta más grande</span>
                            <span class="pf-row__value">${{ number_format($metrics['largest_sale']->total_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        {{-- ========================== COMPARATIVA ========================== --}}
        <div class="db-grid">
            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-calendar"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Periodo actual</h2>
                        <p>{{ $startDate->format('d/m/Y') }} – {{ $endDate->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="db-facts">
                    <div class="db-fact">
                        <span class="db-fact__icon"><i class="fa-solid fa-sack-dollar"></i></span>
                        <span>
                            <span class="db-fact__label">Ventas</span>
                            <span class="db-fact__value">${{ number_format($stats['total_sales'] ?? 0, 0, ',', '.') }}</span>
                        </span>
                    </div>
                    <div class="db-fact">
                        <span class="db-fact__icon"><i class="fa-solid fa-cart-shopping"></i></span>
                        <span>
                            <span class="db-fact__label">Órdenes</span>
                            <span class="db-fact__value">{{ number_format($stats['total_orders'] ?? 0) }}</span>
                        </span>
                    </div>
                </div>
            </section>

            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Periodo anterior</h2>
                        <p>{{ $previousStart->format('d/m/Y') }} – {{ $previousEnd->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="db-facts">
                    <div class="db-fact">
                        <span class="db-fact__icon"><i class="fa-solid fa-sack-dollar"></i></span>
                        <span>
                            <span class="db-fact__label">Ventas</span>
                            <span class="db-fact__value">${{ number_format($stats['previous_sales'] ?? 0, 0, ',', '.') }}</span>
                        </span>
                    </div>
                    <div class="db-fact">
                        <span class="db-fact__icon"><i class="fa-solid fa-arrow-trend-up"></i></span>
                        <span>
                            <span class="db-fact__label">Variación</span>
                            <span class="db-fact__value {{ $growth >= 0 ? 'cm-stat__delta is-up' : 'cm-stat__delta is-down' }}">
                                {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                            </span>
                        </span>
                    </div>
                </div>
            </section>
        </div>

        {{-- ========================= ALIADO DESTACADO ========================= --}}
        @if ($isAdmin && ($metrics['top_ally'] ?? null))
            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-trophy"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Aliado destacado</h2>
                        <p>Mayor volumen de ventas en el periodo.</p>
                    </div>
                </div>

                <div class="db-sat">
                    <span class="ss-rank__pos is-top">#1</span>
                    <span class="db-sat__label">
                        <span class="ss-rank__name">{{ $metrics['top_ally']->ally?->company_name ?? 'Aliado eliminado' }}</span>
                        <span>{{ $metrics['top_ally']->total_orders }} órdenes</span>
                    </span>
                    <span class="ss-rank__value" style="margin-inline-start:auto;">
                        ${{ number_format($metrics['top_ally']->total_sales, 0, ',', '.') }}
                    </span>
                </div>
            </section>
        @endif
    </div>

    {{-- Loading Overlay --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <span>Generando reporte…</span>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const initialLabels = @json($chartData['labels'] ?? []);
            const initialChartData = @json($chartData['data'] ?? []);

            const salesChartCanvas = document.getElementById('salesChart');
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const reportTypeSelect = document.getElementById('reportType');
            const allyFilter = document.getElementById('allyFilter');
            const zoneFilter = document.getElementById('zoneFilter');
            const applyFilterButton = document.getElementById('applyFilterButton');
            const resetFiltersButton = document.getElementById('resetFiltersButton');
            const toggleChartType = document.getElementById('toggleChartType');
            const downloadChartBtn = document.getElementById('downloadChart');
            const downloadPdfButton = document.getElementById('downloadPdfButton');
            const downloadExcelButton = document.getElementById('downloadExcelButton');
            const loadingOverlay = document.getElementById('loadingOverlay');

            let salesChart;
            let currentChartType = 'bar';

            function renderChart(labels, data, type = 'bar') {
                if (!salesChartCanvas) return;
                if (salesChart) salesChart.destroy();

                const ctx = salesChartCanvas.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(166, 1, 179, 0.75)');
                gradient.addColorStop(1, 'rgba(48, 4, 225, 0.08)');

                salesChart = new Chart(salesChartCanvas, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ventas',
                            data: data,
                            backgroundColor: gradient,
                            borderColor: '#a601b3',
                            borderWidth: 2,
                            borderRadius: 8,
                            fill: type === 'line',
                            tension: 0.35
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
                                callbacks: {
                                    label: (context) => {
                                        const value = context.parsed.y ?? 0;
                                        return '$' + new Intl.NumberFormat('es-VE', {
                                            minimumFractionDigits: 2
                                        }).format(value);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.25)'
                                },
                                ticks: {
                                    callback: (value) => '$' + new Intl.NumberFormat('es-VE').format(value)
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            if (salesChartCanvas && initialLabels.length > 0) {
                renderChart(initialLabels, initialChartData, currentChartType);
            }

            if (toggleChartType) {
                toggleChartType.addEventListener('click', function() {
                    currentChartType = currentChartType === 'bar' ? 'line' : 'bar';
                    renderChart(initialLabels, initialChartData, currentChartType);
                    this.classList.toggle('active');
                });
            }

            if (downloadChartBtn && salesChartCanvas) {
                downloadChartBtn.addEventListener('click', () => {
                    const link = document.createElement('a');
                    link.download = `grafico-ventas-${new Date().toISOString().split('T')[0]}.png`;
                    link.href = salesChartCanvas.toDataURL();
                    link.click();
                });
            }

            document.querySelectorAll('.quick-action-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const days = parseInt(this.dataset.days, 10);
                    const end = new Date();
                    const start = new Date();

                    if (days === 0) {
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

            document.querySelectorAll('.tag-remove').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (this.dataset.filter === 'ally' && allyFilter) allyFilter.value = '';
                    if (this.dataset.filter === 'zone' && zoneFilter) zoneFilter.value = '';
                    applyFilters();
                });
            });

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
                window.location.href = `{{ route('admin.reports.sales.export') }}?${params.toString()}`;

                setTimeout(() => loadingOverlay?.classList.remove('active'), 2000);
            }

            downloadPdfButton?.addEventListener('click', () => downloadReport('pdf'));
            downloadExcelButton?.addEventListener('click', () => downloadReport('excel'));
        });
    </script>
@endpush
