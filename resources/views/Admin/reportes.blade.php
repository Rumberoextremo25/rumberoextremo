@extends('layouts.admin')

{{-- Define el título de la página en la toolbar --}}
@section('page_title_toolbar', 'Gestión de Reportes')

{{-- Agrega los estilos CSS específicos de esta vista --}}
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

{{-- Contenido principal de la página --}}
@section('content')
    <div class="reports-container p-6 md:p-10 max-w-7xl mx-auto">
        <div class="reports-card">
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 md:mb-8">
                <span class="text-gray-900">Análisis de</span>
                <span style="color: #8a2be2;">Ventas</span>
            </h1>
            
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
        </div>
    </div>
@endsection

@push('scripts') {{-- Agregamos el JS específico de esta vista --}}
    {{-- Asegúrate de que Chart.js esté incluido en tu layout o aquí --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    {{-- PASO CRUCIAL: Pasa las variables de PHP a JavaScript GLOBALMENTE --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Asegúrate de que las variables de PHP estén disponibles.
            const initialLabels = @json($labels);
            const initialChartData = @json($chartData);
            const initialReportType = "{{ $reportType ?? 'monthly' }}";
            const exchangeRateVes = {{ $exchangeRateVes ?? 1 }};

            // Referencias a los elementos del DOM
            const salesChartCanvas = document.getElementById('salesChart');
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const reportTypeSelect = document.getElementById('reportType');
            const applyFilterButton = document.getElementById('applyFilterButton');
            const viewTodayReportButton = document.getElementById('viewTodayReportButton');
            const downloadPdfButton = document.getElementById('downloadPdfButton');

            let salesChart;

            // Función para renderizar el gráfico
            function renderChart(labels, data) {
                if (salesChart) {
                    salesChart.destroy();
                }

                salesChart = new Chart(salesChartCanvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ventas (USD)',
                            data: data,
                            backgroundColor: 'rgba(93, 86, 249, 0.6)',
                            borderColor: '#5d56f9',
                            borderWidth: 1,
                            borderRadius: 8,
                            barThickness: 20
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
                                    title: function(context) {
                                        return context[0].label;
                                    },
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('es-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#e5e7eb',
                                    borderDash: [5, 5]
                                },
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('es-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value);
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

            // Renderizar el gráfico inicial
            renderChart(initialLabels, initialChartData);

            // Manejar la acción del botón "Aplicar"
            applyFilterButton.addEventListener('click', function() {
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                const reportType = reportTypeSelect.value;
                
                // Redireccionar o cargar los datos (simulación)
                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set('startDate', startDate);
                newUrl.searchParams.set('endDate', endDate);
                newUrl.searchParams.set('reportType', reportType);
                window.location.href = newUrl.toString();
            });

            // Manejar la acción del botón "Hoy"
            viewTodayReportButton.addEventListener('click', function() {
                const today = new Date().toISOString().split('T')[0];
                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set('startDate', today);
                newUrl.searchParams.set('endDate', today);
                newUrl.searchParams.set('reportType', 'daily');
                window.location.href = newUrl.toString();
            });

            // Manejar la acción del botón "Descargar PDF"
            downloadPdfButton.addEventListener('click', async function() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                const card = document.querySelector('.reports-card');
                
                // Usa html2canvas para capturar la tarjeta
                const canvas = await html2canvas(card, {
                    scale: 2,
                    useCORS: true
                });
                
                const imgData = canvas.toDataURL('image/png');
                const imgProps = doc.getImageProperties(imgData);
                const pdfWidth = doc.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                
                doc.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                doc.save('reporte_de_ventas.pdf');
            });
        });
    </script>
@endpush