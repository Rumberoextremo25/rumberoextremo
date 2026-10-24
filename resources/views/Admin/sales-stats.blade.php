@extends('layouts.admin')

@section('title', 'Estadísticas de Ventas')

@section('page_title_toolbar', 'Estadísticas de Ventas')

@section('content')
    @php
        $periods = [
            'hoy' => 'Hoy',
            '7d' => '7 días',
            '30d' => '30 días',
            'mes' => 'Este mes',
            'anio' => 'Este año',
        ];
    @endphp

    <div class="cm-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Estadísticas de <span class="title-accent">ventas</span></h1>
                <p class="page-subtitle">
                    Rendimiento del periodo, ticket promedio y aliados que más venden.
                </p>
            </div>

            <div class="header-actions">
                <button type="button" class="btn btn-secondary" id="printStats">
                    <i class="fa-solid fa-print"></i> Imprimir
                </button>
                <button type="button" class="btn btn-primary" id="exportStats">
                    <i class="fa-solid fa-chart-simple"></i> Exportar
                </button>
            </div>
        </div>

        {{-- ============================= RANGOS ============================= --}}
        <div class="cm-filters">
            <div class="ss-ranges">
                @foreach ($periods as $key => $label)
                    <a href="{{ route('admin.sales.stats', ['periodo' => $key]) }}"
                        class="cm-chip {{ $periodo === $key ? 'cm-chip--primary is-active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ============================= MÉTRICAS ============================= --}}
        <div class="cm-stats">
            <div class="cm-stat">
                <span class="cm-stat__icon"><i class="fa-solid fa-sack-dollar"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">${{ number_format($summary['total_sales'], 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Ventas del periodo</span>
                    @if ($summary['previous_sales'] > 0)
                        <span class="cm-stat__delta {{ $summary['growth'] >= 0 ? 'is-up' : 'is-down' }}">
                            <i class="fa-solid {{ $summary['growth'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                            {{ $summary['growth'] >= 0 ? '+' : '' }}{{ $summary['growth'] }}% vs. anterior
                        </span>
                    @else
                        <span class="cm-stat__delta"><i class="fa-solid fa-minus"></i> Sin periodo anterior</span>
                    @endif
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--green"><i class="fa-solid fa-receipt"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($summary['total_transactions'], 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Transacciones</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-circle-check"></i> Completadas</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gold"><i class="fa-solid fa-calendar-day"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">${{ number_format($summary['sales_today'], 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Ventas de hoy</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-clock"></i> En curso</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gray"><i class="fa-solid fa-ticket"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">${{ number_format($summary['average_ticket'], 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Ticket promedio</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-divide"></i> Por venta</span>
                </span>
            </div>
        </div>

        {{-- ======================= GRÁFICO + RANKING ======================= --}}
        <div class="db-grid">
            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-chart-line"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Tendencia de ventas</h2>
                        <p>{{ $periodLabel }} · montos en dólares.</p>
                    </div>

                    <div class="ss-toggle" style="margin-inline-start:auto;">
                        <button type="button" class="ss-toggle__btn is-active" data-chart="line" title="Línea">
                            <i class="fa-solid fa-chart-line"></i>
                        </button>
                        <button type="button" class="ss-toggle__btn" data-chart="bar" title="Barras">
                            <i class="fa-solid fa-chart-bar"></i>
                        </button>
                    </div>
                </div>

                <div class="cf-card__body">
                    @if ($salesByDay->isEmpty())
                        <div class="cm-empty" style="border:0;border-radius:0;">
                            <i class="fa-solid fa-chart-line"></i>
                            <h3>Sin ventas en este periodo</h3>
                            <p>Prueba con un rango de fechas más amplio.</p>
                        </div>
                    @else
                        <div class="ss-chart">
                            <canvas id="salesChart"></canvas>
                        </div>
                    @endif
                </div>
            </section>

            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-trophy"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Top aliados</h2>
                        <p>Por ventas del periodo.</p>
                    </div>
                </div>

                @if ($salesByAlly->isEmpty())
                    <div class="cm-empty" style="border:0;border-radius:0;">
                        <i class="fa-solid fa-store"></i>
                        <h3>Sin datos</h3>
                        <p>No hay ventas registradas en este periodo.</p>
                    </div>
                @else
                    <ol class="ss-rank">
                        @foreach ($salesByAlly as $index => $sale)
                            <li class="ss-rank__item">
                                <span class="ss-rank__pos {{ $index < 3 ? 'is-top' : '' }}">{{ $index + 1 }}</span>
                                <span class="ss-rank__info">
                                    <span class="ss-rank__name">{{ $sale->ally->company_name ?? 'Aliado eliminado' }}</span>
                                    <span class="ss-rank__meta">{{ $sale->count }} ventas</span>
                                </span>
                                <span class="ss-rank__value">${{ number_format($sale->total, 0, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </div>

        {{-- =========================== RESUMEN =========================== --}}
        <section class="cf-card">
            <div class="cf-card__head">
                <span class="cf-card__icon"><i class="fa-solid fa-chart-pie"></i></span>
                <div class="cf-card__head-text">
                    <h2>Resumen del periodo</h2>
                    <p>Datos acumulados y comparación con el periodo anterior.</p>
                </div>
            </div>

            <div class="db-facts db-facts--cols">
                <div class="db-fact">
                    <span class="db-fact__icon"><i class="fa-solid fa-calendar"></i></span>
                    <span>
                        <span class="db-fact__label">Ventas de este mes</span>
                        <span class="db-fact__value">${{ number_format($summary['sales_this_month'], 0, ',', '.') }}</span>
                    </span>
                </div>

                <div class="db-fact">
                    <span class="db-fact__icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                    <span>
                        <span class="db-fact__label">Periodo anterior</span>
                        <span class="db-fact__value">${{ number_format($summary['previous_sales'], 0, ',', '.') }}</span>
                    </span>
                </div>

                <div class="db-fact">
                    <span class="db-fact__icon"><i class="fa-solid fa-arrow-trend-up"></i></span>
                    <span>
                        <span class="db-fact__label">Variación</span>
                        <span class="db-fact__value {{ $summary['growth'] >= 0 ? 'cm-stat__delta is-up' : 'cm-stat__delta is-down' }}">
                            {{ $summary['growth'] >= 0 ? '+' : '' }}{{ $summary['growth'] }}%
                        </span>
                    </span>
                </div>

                <div class="db-fact">
                    <span class="db-fact__icon"><i class="fa-solid fa-store"></i></span>
                    <span>
                        <span class="db-fact__label">Aliados con ventas</span>
                        <span class="db-fact__value">{{ $salesByAlly->count() }}</span>
                    </span>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var salesData = @json($salesByDay);
            var currentType = 'line';
            var chart = null;
            var canvas = document.getElementById('salesChart');

            function createChart(type) {
                if (!canvas) return;
                if (chart) chart.destroy();

                chart = new Chart(canvas, {
                    type: type,
                    data: {
                        labels: salesData.map(function(item) {
                            var date = new Date(item.date + 'T00:00:00');
                            return date.toLocaleDateString('es-ES', {
                                day: '2-digit',
                                month: 'short'
                            });
                        }),
                        datasets: [{
                            label: 'Ventas',
                            data: salesData.map(function(item) {
                                return item.total;
                            }),
                            borderColor: '#a601b3',
                            backgroundColor: type === 'line' ? 'rgba(166, 1, 179, 0.12)' : '#a601b3',
                            borderWidth: 3,
                            tension: 0.35,
                            fill: type === 'line',
                            pointBackgroundColor: '#3004e1',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: type === 'line' ? 3 : 0,
                            pointHoverRadius: 5,
                            borderRadius: 7
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
                                    label: function(context) {
                                        return 'Ventas: $' + new Intl.NumberFormat('es-VE').format(context.raw);
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
                                    callback: function(value) {
                                        return '$' + new Intl.NumberFormat('es-VE').format(value);
                                    }
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

            document.querySelectorAll('.ss-toggle__btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    document.querySelectorAll('.ss-toggle__btn').forEach(function(item) {
                        item.classList.remove('is-active');
                    });
                    button.classList.add('is-active');
                    currentType = button.getAttribute('data-chart');
                    if (salesData.length) createChart(currentType);
                });
            });

            if (salesData.length) {
                createChart(currentType);
            }

            var printButton = document.getElementById('printStats');
            if (printButton) {
                printButton.addEventListener('click', function() {
                    window.print();
                });
            }

            var exportButton = document.getElementById('exportStats');
            if (exportButton) {
                exportButton.addEventListener('click', function() {
                    window.location.href = '{{ route('admin.reports.sales.export') }}?formato=excel';
                });
            }
        });
    </script>
@endpush
