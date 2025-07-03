@extends('layouts.admin')

@section('title', 'Reportes de Ventas - Rumbero Extremo')
@section('page_title', 'Reportes y Análisis')

@section('content')
    <div class="reports-section">
        <h2>Reporte de Ventas</h2>

        <div class="reports-actions">
            <div class="date-filter">
                <label for="startDate">Desde:</label>
                <input type="date" id="startDate" value="{{ $startDate }}">
                <label for="endDate">Hasta:</label>
                <input type="date" id="endDate" value="{{ $endDate }}">

                {{-- Nuevo: Selector de tipo de reporte --}}
                <label for="reportType">Ver por:</label>
                <select id="reportType" class="form-select">
                    {{-- La variable $reportType se pasa desde el controlador y se usa para marcar la opción seleccionada --}}
                    <option value="monthly" {{ ($reportType ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Mes</option>
                    <option value="weekly" {{ ($reportType ?? '') == 'weekly' ? 'selected' : '' }}>Semana</option>
                    <option value="daily" {{ ($reportType ?? '') == 'daily' ? 'selected' : '' }}>Día</option>
                </select>

                <button class="filter-button btn btn-primary" id="applyFilterButton">
                    <i class="fas fa-filter"></i> Aplicar Filtro
                </button>
            </div>
            <button class="download-button" id="downloadPdfButton">
                <i class="fas fa-file-pdf"></i> Descargar Reporte (PDF)
            </button>
        </div>

        {{-- Contenedor del gráfico principal --}}
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Asegúrate de que Chart.js esté incluido en tu layout o aquí --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Datos dinámicos pasados desde el controlador a JavaScript
            const initialLabels = @json($labels);
            const initialChartData = @json($chartData);
            // 'initialReportType' ahora sí reflejará el valor exacto que el controlador procesó
            const initialReportType = "{{ $reportType ?? 'monthly' }}";

            let salesChartInstance; // Variable para almacenar la instancia del gráfico

            // Función para renderizar o actualizar el gráfico
            const renderChart = (labels, dataValues, type) => {
                const salesChartCtx = document.getElementById("salesChart");

                if (salesChartInstance) {
                    salesChartInstance.destroy(); // Destruye la instancia existente para evitar duplicados
                }

                // Configuración de la escala X basada en el tipo de reporte
                let xTitleText = "Mes";
                if (type === 'daily') {
                    xTitleText = "Día";
                } else if (type === 'weekly') {
                    xTitleText = "Semana";
                }

                salesChartInstance = new Chart(salesChartCtx.getContext("2d"), {
                    type: "bar",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Ingresos Totales (USD)",
                            data: dataValues,
                            backgroundColor: "rgba(255, 75, 75, 0.7)", // Color de las barras (rojo de Rumbero Extremo)
                            borderColor: "rgba(255, 75, 75, 1)",
                            borderWidth: 1,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Ingresos (USD)",
                                },
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: xTitleText, // Título dinámico
                                },
                            },
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: "top",
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: $${context.parsed.y.toLocaleString("en-US")}`;
                                    },
                                },
                            },
                        },
                    },
                });
            };

            // Renderizar el gráfico inicial al cargar la página
            renderChart(initialLabels, initialChartData, initialReportType);

            // Lógica para el botón "Aplicar Filtro" y el cambio de tipo de reporte
            document.getElementById('applyFilterButton').addEventListener('click', () => {
                applyFilters();
            });

            document.getElementById('reportType').addEventListener('change', () => {
                applyFilters();
            });

            const applyFilters = () => {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const reportType = document.getElementById('reportType').value; // Obtener el tipo de reporte seleccionado

                // Redirigir a la misma ruta con los nuevos parámetros de fecha y tipo de reporte
                // Asegúrate que 'reports.sales' sea el nombre de tu ruta en web.php
                window.location.href = `{{ route('reports.sales') }}?startDate=${startDate}&endDate=${endDate}&reportType=${reportType}`;
            };

            // Lógica para el botón "Descargar Reporte (PDF)"
            const downloadPdfButton = document.getElementById("downloadPdfButton");
            if (downloadPdfButton) {
                downloadPdfButton.addEventListener("click", () => {
                    const startDate = document.getElementById("startDate").value;
                    const endDate = document.getElementById("endDate").value;
                    const reportType = document.getElementById('reportType').value; // Incluir el tipo de reporte en el PDF

                    // Abrir el PDF en una nueva pestaña (el controlador lo streamea)
                    // Asegúrate que 'reports.sales.pdf' sea el nombre de tu ruta en web.php
                    window.open(`{{ route('reports.sales.pdf') }}?startDate=${startDate}&endDate=${endDate}&reportType=${reportType}`, '_blank');
                });
            }
        });
    </script>
@endsection
