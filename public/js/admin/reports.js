// resources/js/admin/reports/sales.js

document.addEventListener('DOMContentLoaded', () => {
    console.log('Sales reports scripts loaded.');

    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const reportTypeSelect = document.getElementById('reportType');
    const applyFilterButton = document.getElementById('applyFilterButton');
    const viewTodayReportButton = document.getElementById('viewTodayReportButton');
    const downloadPdfButton = document.getElementById('downloadPdfButton');
    const salesChartCanvas = document.getElementById('salesChart');

    let salesChart; // Variable para almacenar la instancia del gráfico

    // Función para inicializar o actualizar el gráfico
    function initializeChart(labels, data, type) {
        if (salesChart) {
            salesChart.destroy(); // Destruye la instancia anterior del gráfico si existe
        }

        salesChart = new Chart(salesChartCanvas, {
            type: 'bar', // Puedes cambiar a 'line' o 'doughnut' según el tipo de reporte
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ventas ($)',
                    data: data,
                    backgroundColor: 'rgba(83, 12, 191, 0.7)', // var(--primary-color)
                    borderColor: 'rgba(83, 12, 191, 1)',
                    borderWidth: 1,
                    borderRadius: 5, // Bordes redondeados para las barras
                    hoverBackgroundColor: 'rgba(118, 40, 167, 0.8)', // var(--secondary-color)
                    hoverBorderColor: 'rgba(118, 40, 167, 1)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Permite que el gráfico se ajuste al contenedor
                plugins: {
                    title: {
                        display: true,
                        text: `Ventas ${type === 'monthly' ? 'Mensuales' : (type === 'weekly' ? 'Semanales' : 'Diarias')}`,
                        font: {
                            size: 18,
                            weight: 'bold'
                        },
                        color: 'var(--heading-color)'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false // Oculta las líneas de la cuadrícula en el eje X
                        },
                        ticks: {
                            color: 'var(--text-color)'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'var(--border-color)' // Color de las líneas de la cuadrícula en el eje Y
                        },
                        ticks: {
                            color: 'var(--text-color)',
                            callback: function(value, index, ticks) {
                                return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
                            }
                        }
                    }
                }
            }
        });
    }

    // Inicializa el gráfico con los datos pasados desde PHP
    initializeChart(window.initialLabels, window.initialChartData, window.initialReportType);

    // Función para obtener los datos del reporte (simulada por ahora)
    // En un entorno real, harías una llamada AJAX aquí para obtener los datos del servidor
    async function fetchReportData(start, end, type) {
        // Simulación de datos
        // En un entorno real, esto sería una llamada a tu API de Laravel
        // const response = await fetch(`/admin/reports/sales-data?start_date=${start}&end_date=${end}&report_type=${type}`);
        // const data = await response.json();
        // return data;

        // Datos de ejemplo para demostración
        const dummyLabels = [];
        const dummyData = [];
        let numDays = 0;

        const startDate = new Date(start);
        const endDate = new Date(end);

        if (type === 'daily') {
            for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                dummyLabels.push(d.toISOString().split('T')[0]);
                dummyData.push(Math.floor(Math.random() * 500) + 100); // Ventas aleatorias
            }
        } else if (type === 'weekly') {
            // Simplificado: agrupa por semanas arbitrarias
            let currentWeek = 1;
            for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 7)) {
                dummyLabels.push(`Semana ${currentWeek++} (${d.toISOString().split('T')[0]})`);
                dummyData.push(Math.floor(Math.random() * 2000) + 500);
            }
        } else if (type === 'monthly') {
            // Simplificado: agrupa por meses
            let currentMonth = startDate.getMonth();
            let currentYear = startDate.getFullYear();
            while (new Date(currentYear, currentMonth) <= endDate) {
                dummyLabels.push(`${new Date(currentYear, currentMonth).toLocaleString('es-ES', { month: 'long', year: 'numeric' })}`);
                dummyData.push(Math.floor(Math.random() * 5000) + 1000);
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
            }
        }

        return { labels: dummyLabels, chartData: dummyData };
    }

    // Evento para aplicar filtros
    applyFilterButton.addEventListener('click', async () => {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const reportType = reportTypeSelect.value;

        if (!startDate || !endDate) {
            alert('Por favor, selecciona ambas fechas para aplicar el filtro.');
            return;
        }

        // Redirige o haz una llamada AJAX para obtener los datos actualizados
        // Para este ejemplo, simularemos la obtención de datos y actualizaremos el gráfico
        const data = await fetchReportData(startDate, endDate, reportType);
        initializeChart(data.labels, data.chartData, reportType);

        // En un entorno real, podrías redirigir para que Laravel maneje la lógica del controlador:
        // window.location.href = `${window.location.pathname}?start_date=${startDate}&end_date=${endDate}&report_type=${reportType}`;
    });

    // Evento para ver el reporte de hoy
    viewTodayReportButton.addEventListener('click', async () => {
        const today = new Date().toISOString().split('T')[0];
        startDateInput.value = today;
        endDateInput.value = today;
        reportTypeSelect.value = 'daily'; // Por defecto a diario para "Hoy"

        const data = await fetchReportData(today, today, 'daily');
        initializeChart(data.labels, data.chartData, 'daily');

        // window.location.href = `${window.location.pathname}?start_date=${today}&end_date=${today}&report_type=daily`;
    });

    // Evento para descargar PDF (simulado)
    downloadPdfButton.addEventListener('click', () => {
        alert('Funcionalidad de descarga de PDF en desarrollo.');
        // En un entorno real, harías una llamada a una ruta de Laravel que genere el PDF
        // window.location.href = `/admin/reports/download-pdf?start_date=${startDateInput.value}&end_date=${endDateInput.value}&report_type=${reportTypeSelect.value}`;
    });

    // Lógica para la barra lateral (si es que los reportes tienen una entrada en ella)
    const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
    sidebarLinks.forEach(link => {
        link.classList.remove('active');
        if (window.location.pathname.includes('/admin/reports')) {
            if (link.getAttribute('href') && link.getAttribute('href').includes('/admin/reports')) {
                link.classList.add('active');
            }
        }
    });
});