<?php
$base = $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '';

$total_capital = 0;
$productos_chart = [];
foreach($productos_estancados as $p) {
    $total_capital += (float)$p['valor_estancado'];
    if ((float)$p['valor_estancado'] > 0) {
        $productos_chart[] = [
            'nombre' => $p['nombre'],
            'valor' => (float)$p['valor_estancado']
        ];
    }
}
// Ordenar para el chart (top 10)
usort($productos_chart, fn($a, $b) => $b['valor'] <=> $a['valor']);
$top_chart = array_slice($productos_chart, 0, 10);
?>

<div class="container-fluid px-4 pb-5 font-sans">
    <div class="flex items-center justify-between mt-6 mb-4">
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">
            <i class="bi bi-box-seam text-yellow-500 mr-2"></i><?= $titulo ?>
        </h1>
        <ol class="flex items-center space-x-2 text-sm text-gray-500 bg-white px-4 py-2 rounded-full shadow-sm">
            <li><a href="<?= $base ?>/dashboard" class="hover:text-blue-600 transition-colors text-decoration-none">Dashboard</a></li>
            <li><span class="mx-2">/</span></li>
            <li>Reportes</li>
            <li><span class="mx-2">/</span></li>
            <li class="font-bold text-gray-800"><?= $titulo ?></li>
        </ol>
    </div>

    <!-- Filtros con Glassmorphism -->
    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl p-6 mb-8 border border-white/40">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Sucursal</label>
                <select name="sucursal_id" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition-all shadow-sm">
                    <option value="0">Todas las Sucursales</option>
                    <?php foreach($sucursales as $s): ?>
                        <option value="<?= $s->id ?>" <?= $s->id == $sucursal_id ? 'selected' : '' ?>><?= s($s->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Días sin rotación</label>
                <select name="dias" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition-all shadow-sm">
                    <option value="30" <?= $dias_estancado == 30 ? 'selected' : '' ?>>Más de 30 días</option>
                    <option value="60" <?= $dias_estancado == 60 ? 'selected' : '' ?>>Más de 60 días</option>
                    <option value="90" <?= $dias_estancado == 90 ? 'selected' : '' ?>>Más de 90 días</option>
                    <option value="120" <?= $dias_estancado == 120 ? 'selected' : '' ?>>Más de 120 días</option>
                    <option value="180" <?= $dias_estancado == 180 ? 'selected' : '' ?>>Más de 6 meses (180 días)</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-lg text-sm px-5 py-3 text-center transition-all shadow-lg hover:shadow-blue-500/50">
                    <i class="bi bi-filter mr-2"></i> Generar Reporte Espectacular
                </button>
            </div>
        </form>
    </div>

    <!-- KPIs Gerenciales -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Tarjeta: Dinero Congelado -->
        <div class="bg-gradient-to-br from-rose-500 to-red-600 rounded-3xl shadow-2xl p-6 text-white relative overflow-hidden group">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            <div class="flex justify-between items-center relative z-10">
                <div>
                    <p class="text-rose-100 uppercase tracking-widest text-xs font-bold mb-1">Capital Congelado (Costo)</p>
                    <h2 class="text-4xl font-black">Q <?= number_format($total_capital, 2) ?></h2>
                </div>
                <div class="bg-white/20 p-4 rounded-2xl backdrop-blur-sm">
                    <i class="bi bi-cash-stack text-4xl"></i>
                </div>
            </div>
            <p class="text-sm text-rose-100 mt-4 relative z-10"><i class="bi bi-info-circle mr-1"></i>Dinero inmovilizado en inventario de lento movimiento.</p>
        </div>

        <!-- Tarjeta: Cantidad de Productos -->
        <div class="bg-gradient-to-br from-amber-400 to-orange-500 rounded-3xl shadow-2xl p-6 text-white relative overflow-hidden group">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            <div class="flex justify-between items-center relative z-10">
                <div>
                    <p class="text-amber-100 uppercase tracking-widest text-xs font-bold mb-1">Productos Estancados</p>
                    <h2 class="text-4xl font-black"><?= count($productos_estancados) ?></h2>
                </div>
                <div class="bg-white/20 p-4 rounded-2xl backdrop-blur-sm">
                    <i class="bi bi-boxes text-4xl"></i>
                </div>
            </div>
            <p class="text-sm text-amber-100 mt-4 relative z-10"><i class="bi bi-info-circle mr-1"></i>Cantidad de referencias que requieren atención urgente.</p>
        </div>
    </div>

    <!-- Visualización de Datos -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
        
        <!-- Gráfica ApexCharts -->
        <div class="xl:col-span-1 bg-white rounded-3xl shadow-xl p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="bi bi-pie-chart-fill text-purple-500 mr-2"></i>Top 10 Capital Estancado</h3>
            <div id="chartEstancado" class="w-full" style="min-height: 350px;"></div>
        </div>

        <!-- Tabla AG Grid -->
        <div class="xl:col-span-2 bg-white rounded-3xl shadow-xl p-6 border border-gray-100 flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800"><i class="bi bi-table text-blue-500 mr-2"></i>Detalle de Productos</h3>
                <button onclick="gridOptions.api.exportDataAsCsv()" class="text-green-600 hover:text-white border border-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all">
                    <i class="bi bi-file-earmark-excel mr-1"></i> CSV
                </button>
            </div>
            <div id="myGrid" class="ag-theme-alpine w-full flex-grow" style="height: 400px; border-radius: 12px; overflow: hidden;"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- DATOS INYECTADOS ---
    const chartData = <?= json_encode($top_chart) ?>;
    const tableData = <?= json_encode($productos_estancados) ?>;

    // --- APEXCHARTS ---
    if(chartData.length > 0) {
        const options = {
            series: chartData.map(d => d.valor),
            labels: chartData.map(d => d.nombre),
            chart: {
                type: 'donut',
                height: 380,
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: { show: true },
                            value: {
                                show: true,
                                formatter: function (val) {
                                    return "Q " + parseFloat(val).toLocaleString('en-US', {minimumFractionDigits:2});
                                }
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Total Top 10',
                                formatter: function (w) {
                                    return "Q " + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('en-US', {minimumFractionDigits:2})
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            stroke: { width: 0 },
            theme: { palette: 'palette1' },
            legend: {
                position: 'bottom',
                fontSize: '12px',
                markers: { radius: 12 }
            }
        };

        const chart = new ApexCharts(document.querySelector("#chartEstancado"), options);
        chart.render();
    } else {
        document.querySelector("#chartEstancado").innerHTML = '<div class="flex items-center justify-center h-full text-gray-400"><p>No hay datos suficientes para graficar.</p></div>';
    }

    // --- AG GRID ---
    const dateFormatter = (params) => {
        if (!params.value) return 'Nunca Vendido';
        const d = new Date(params.value);
        return d.toLocaleDateString('es-ES', { day:'2-digit', month:'2-digit', year:'numeric' });
    };

    const currencyFormatter = (params) => {
        return "Q " + parseFloat(params.value).toLocaleString('en-US', {minimumFractionDigits: 2});
    };

    window.gridOptions = {
        columnDefs: [
            { field: "nombre", headerName: "Producto", flex: 2, minWidth: 200, filter: 'agTextColumnFilter' },
            { field: "sku", headerName: "SKU", flex: 1, filter: 'agTextColumnFilter' },
            { field: "sucursal_nombre", headerName: "Sucursal", flex: 1, filter: 'agSetColumnFilter' },
            { field: "ultima_venta", headerName: "Última Venta", flex: 1, valueFormatter: dateFormatter },
            { field: "stock_total", headerName: "Stock", flex: 1, type: 'numericColumn', cellClass: 'font-bold' },
            { field: "precio_compra", headerName: "Costo U.", flex: 1, type: 'numericColumn', valueFormatter: currencyFormatter },
            { field: "valor_estancado", headerName: "Valor Estancado", flex: 1.5, type: 'numericColumn', valueFormatter: currencyFormatter, cellStyle: {color: '#dc2626', fontWeight: 'bold'} }
        ],
        defaultColDef: {
            sortable: true,
            resizable: true,
            filter: true
        },
        rowData: tableData,
        pagination: true,
        paginationPageSize: 15,
        animateRows: true,
        rowClassRules: {
            // Apply mild red background if totally stuck
            'bg-red-50': params => !params.data.ultima_venta
        }
    };

    const gridDiv = document.querySelector('#myGrid');
    agGrid.createGrid(gridDiv, gridOptions);
});
</script>

