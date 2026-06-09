import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    
    // Gráfico de Gastos por Categoría
    const ctxGastos = document.getElementById('chartGastos');
    if (ctxGastos) {
        try {
            const labelsStr = ctxGastos.getAttribute('data-labels');
            const dataStr = ctxGastos.getAttribute('data-montos');
            const labels = JSON.parse(labelsStr);
            const data = JSON.parse(dataStr);

            new Chart(ctxGastos, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#0d6efd', '#6610f2', '#d63384', '#dc3545', 
                            '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        } catch (e) {
            console.error("Error inicializando gráfico de gastos:", e);
        }
    }

    // Gráfico Resumen Ingresos vs Egresos
    const ctxResumen = document.getElementById('chartResumen');
    if (ctxResumen) {
        try {
            const ingresos = parseFloat(ctxResumen.getAttribute('data-ingresos'));
            const costos = parseFloat(ctxResumen.getAttribute('data-costos'));
            const gastos = parseFloat(ctxResumen.getAttribute('data-gastos'));

            new Chart(ctxResumen, {
                type: 'bar',
                data: {
                    labels: ['Ingresos Totales', 'Costo de Ventas', 'Gastos Operativos'],
                    datasets: [{
                        label: 'Monto ($)',
                        data: [ingresos, costos, gastos],
                        backgroundColor: [
                            'rgba(25, 135, 84, 0.8)', // Verde
                            'rgba(255, 193, 7, 0.8)', // Amarillo
                            'rgba(220, 53, 69, 0.8)'  // Rojo
                        ],
                        borderColor: [
                            'rgba(25, 135, 84, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        } catch(e) {
            console.error("Error inicializando gráfico de resumen:", e);
        }
    }

    // Gráfico de Ventas por Sucursal (Dashboard de Productos)
    const ctxSucursales = document.getElementById('chartSucursales');
    if (ctxSucursales) {
        try {
            const labelsStr = ctxSucursales.getAttribute('data-labels');
            const ventasStr = ctxSucursales.getAttribute('data-ventas');
            const labels = JSON.parse(labelsStr);
            const data = JSON.parse(ventasStr);

            new Chart(ctxSucursales, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Ventas (Q)',
                        data: data,
                        backgroundColor: 'rgba(13, 110, 253, 0.8)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        } catch (e) {
            console.error("Error inicializando gráfico de sucursales:", e);
        }
    }
});
