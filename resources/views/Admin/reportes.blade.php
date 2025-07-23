@extends('layouts.admin')

@section('title', 'Reportes de Ventas - Rumbero Extremo')
@section('page_title_toolbar', 'Análisis de Ventas') {{-- Usando page_title_toolbar si tu layout lo soporta --}}

@push('styles') {{-- Agregamos el CSS específico de esta vista --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    {{-- Enlazamos al nuevo archivo CSS para los reportes --}}
    <link rel="stylesheet" href="{{ asset('css/admin/reports.css') }}">
@endpush

@section('content')
    <div class="reports-container">
        <h1>Análisis de Ventas</h1>

        <div class="reports-card">
            <h3><i class="fas fa-chart-line"></i> Visión General de Ventas</h3>

            <div class="reports-actions-grid">
                <div class="form-group">
                    <label for="startDate">Desde:</label>
                    <input type="date" id="startDate" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="form-group">
                    <label for="endDate">Hasta:</label>
                    <input type="date" id="endDate" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="form-group select-wrapper">
                    <label for="reportType">Ver por:</label>
                    <select id="reportType" class="form-select">
                        <option value="monthly" {{ ($reportType ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Mensual</option>
                        <option value="weekly" {{ ($reportType ?? '') == 'weekly' ? 'selected' : '' }}>Semanal</option>
                        <option value="daily" {{ ($reportType ?? '') == 'daily' ? 'selected' : '' }}>Diario</option>
                    </select>
                </div>
                <div class="form-group">
                    <button class="modern-button" id="applyFilterButton">
                        <i class="fas fa-filter"></i> Aplicar
                    </button>
                </div>
                <div class="form-group">
                    <button class="modern-button today-report" id="viewTodayReportButton">
                        <i class="fas fa-calendar-day"></i> Hoy
                    </button>
                </div>
                <div class="form-group">
                    <button class="modern-button download-pdf" id="downloadPdfButton">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
            </div>

            {{-- Contenedor del gráfico principal --}}
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
            {{-- La sección de la tasa de cambio ha sido eliminada de aquí --}}
        </div>
    </div>
@endsection

@push('scripts') {{-- Agregamos el JS específico de esta vista --}}
    {{-- Asegúrate de que Chart.js esté incluido en tu layout o aquí --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- PASO CRUCIAL: Pasa las variables de PHP a JavaScript GLOBALMENTE --}}
    <script>
        window.initialLabels = @json($labels);
        window.initialChartData = @json($chartData);
        window.initialReportType = "{{ $reportType ?? 'monthly' }}";
        window.exchangeRateVes = {{ $exchangeRateVes ?? 1 }}; // Usar un valor por defecto si no está definido por alguna razón
    </script>
    <script src="{{ asset('js/admin/reports.js') }}"></script>
@endpush

