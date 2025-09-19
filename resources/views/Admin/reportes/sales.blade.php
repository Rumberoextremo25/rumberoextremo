@extends('layouts.admin')

@section('page_title_toolbar', 'Reportes de Ventas')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
    </div>

    <div class="reports-container">
        <div class="reports-card">
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 md:mb-8">
                <span class="text-gray-900">Análisis de</span>
                <span style="color: #8a2be2;">Ventas</span>
            </h1>
            
            <h3 class="text-xl font-semibold text-gray-700 mb-4">
                <i class="fas fa-chart-line"></i> Visión General de Ventas
            </h3>

            <div class="reports-actions-grid">
                <div class="form-group">
                    <label for="startDate">Desde:</label>
                    <input type="date" id="startDate" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label for="endDate">Hasta:</label>
                    <input type="date" id="endDate" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="form-group select-wrapper">
                    <label for="reportType">Ver por:</label>
                    <select id="reportType" class="form-select">
                        <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Mensual</option>
                        <option value="weekly" {{ $reportType == 'weekly' ? 'selected' : '' }}>Semanal</option>
                        <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Diario</option>
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

            {{-- Información Adicional --}}
            <div class="additional-info mt-8">
                <h4 class="text-lg font-semibold mb-4">Métodos de Pago Más Populares</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($stats['payment_methods'] as $payment)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">{{ ucfirst($payment->payment_method) }}</span>
                            <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm">
                                {{ $payment->count }} órdenes
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
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
                loadingOverlay.style.display = show ? 'flex' : 'none';
            }

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
                            backgroundColor: 'rgba(138, 43, 226, 0.6)',
                            borderColor: 'rgba(138, 43, 226, 1)',
                            borderWidth: 2,
                            borderRadius: 8,
                            barThickness: 30
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
                                        return `$${context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString('en-US', {maximumFractionDigits: 0});
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

            // Renderizar gráfico inicial
            renderChart(initialLabels, initialChartData);

            // Manejar filtros
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

            // Botón "Hoy"
            viewTodayReportButton.addEventListener('click', function() {
                const today = new Date().toISOString().split('T')[0];
                startDateInput.value = today;
                endDateInput.value = today;
                reportTypeSelect.value = 'daily';
                applyFilterButton.click();
            });

            // Descargar PDF
            downloadPdfButton.addEventListener('click', async function() {
                showLoading(true);
                
                try {
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF();
                    
                    const card = document.querySelector('.reports-card');
                    
                    const canvas = await html2canvas(card, {
                        scale: 2,
                        useCORS: true,
                        logging: false
                    });
                    
                    const imgData = canvas.toDataURL('image/png');
                    const imgProps = doc.getImageProperties(imgData);
                    const pdfWidth = doc.internal.pageSize.getWidth();
                    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                    
                    doc.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                    doc.save('reporte_ventas_{{ now()->format("Y-m-d") }}.pdf');
                } catch (error) {
                    console.error('Error al generar PDF:', error);
                    alert('Error al generar el PDF');
                } finally {
                    showLoading(false);
                }
            });

            // Validar que la fecha final no sea menor que la inicial
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
        });
    </script>
@endpush