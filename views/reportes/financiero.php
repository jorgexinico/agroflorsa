<div class="container-fluid px-4 pb-5 font-sans">
    <div class="flex items-center justify-between mt-6 mb-4">
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">
            <i class="bi bi-bar-chart-fill text-blue-500 mr-2"></i>Estado de Resultados
        </h1>
        <p class="text-sm font-semibold text-gray-500">Análisis de rentabilidad e indicadores financieros</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl p-6 mb-8 border border-white/40">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" value="<?= $fecha_inicio ?>">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" value="<?= $fecha_fin ?>">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Sucursal</label>
                <select name="sucursal_id" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                    <option value="0">Todas (Global)</option>
                    <?php foreach ($sucursales as $s): ?>
                        <option value="<?= $s->id ?>" <?= $sucursal_id === $s->id ? 'selected' : '' ?>><?= s($s->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 font-bold rounded-lg text-sm px-5 py-3 text-center shadow-lg transition-transform hover:scale-[1.02]">
                    <i class="bi bi-filter mr-1"></i> Generar
                </button>
            </div>
        </form>
    </div>

    <!-- KPIs Financieros -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Ingresos -->
        <div class="bg-white rounded-3xl shadow-lg p-6 border-l-4 border-green-500 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 bg-green-50 w-24 h-24 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex justify-between items-center relative z-10 mb-4">
                <h6 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Ingresos Totales</h6>
                <div class="bg-green-100 text-green-600 p-2 rounded-xl"><i class="bi bi-cash-coin text-xl"></i></div>
            </div>
            <h3 class="text-3xl font-black text-gray-800 relative z-10 mb-1">Q <?= number_format($total_ingresos, 2) ?></h3>
            <div class="text-xs text-gray-500 font-medium relative z-10">
                <span class="text-green-600">Q <?= number_format($total_ingresos_contado, 2) ?></span> al contado<br>
                <span class="text-blue-500">Q <?= number_format($total_abonos, 2) ?></span> en abonos
            </div>
        </div>

        <!-- Costo de Ventas -->
        <div class="bg-white rounded-3xl shadow-lg p-6 border-l-4 border-yellow-500 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 bg-yellow-50 w-24 h-24 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex justify-between items-center relative z-10 mb-4">
                <h6 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Costo de Ventas</h6>
                <div class="bg-yellow-100 text-yellow-600 p-2 rounded-xl"><i class="bi bi-cart-x text-xl"></i></div>
            </div>
            <h3 class="text-3xl font-black text-gray-800 relative z-10 mb-1">-Q <?= number_format($total_costos, 2) ?></h3>
            <p class="text-xs text-gray-500 font-medium relative z-10">Costo de la mercadería vendida</p>
        </div>

        <!-- Utilidad Bruta -->
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl shadow-lg p-6 text-white relative overflow-hidden group lg:col-span-1 md:col-span-2">
            <div class="absolute -right-6 -top-6 bg-white/10 w-24 h-24 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex justify-between items-center relative z-10 mb-4">
                <h6 class="text-sm font-bold text-blue-100 uppercase tracking-wider">Utilidad Bruta</h6>
                <div class="bg-white/20 text-white p-2 rounded-xl backdrop-blur-sm"><i class="bi bi-graph-up text-xl"></i></div>
            </div>
            <h3 class="text-3xl font-black relative z-10 mb-1">Q <?= number_format($utilidad_bruta, 2) ?></h3>
            <p class="text-xs text-blue-100 font-medium relative z-10">Ingresos menos Costo de Ventas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Gastos Operativos -->
        <div class="bg-white rounded-3xl shadow-lg p-6 border-l-4 border-red-500 relative overflow-hidden group">
            <div class="flex justify-between items-center relative z-10 mb-4">
                <h6 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Gastos Operativos</h6>
                <div class="bg-red-100 text-red-600 p-2 rounded-xl"><i class="bi bi-wallet2 text-xl"></i></div>
            </div>
            <h3 class="text-4xl font-black text-red-600 relative z-10 mb-1">-Q <?= number_format($total_gastos, 2) ?></h3>
            <p class="text-sm text-gray-500 font-medium relative z-10">Egresos administrativos y operativos</p>
        </div>

        <!-- Utilidad Neta -->
        <div class="bg-gradient-to-br <?= $utilidad_neta >= 0 ? 'from-emerald-400 to-emerald-600' : 'from-rose-500 to-red-600' ?> rounded-3xl shadow-2xl p-6 text-white relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 bg-white/20 w-40 h-40 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            <div class="flex justify-between items-center relative z-10 mb-4">
                <h6 class="text-sm font-bold text-white/80 uppercase tracking-widest">UTILIDAD NETA</h6>
                <div class="bg-white/20 text-white p-3 rounded-2xl backdrop-blur-md shadow-inner"><i class="bi bi-currency-dollar text-2xl"></i></div>
            </div>
            <h2 class="text-5xl font-black relative z-10 mb-2">Q <?= number_format($utilidad_neta, 2) ?></h2>
            <p class="text-sm text-white/80 font-medium relative z-10">Ganancia o pérdida real del periodo</p>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <!-- Gráfico Resumen -->
        <div class="bg-white rounded-3xl shadow-xl p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-6"><i class="bi bi-bar-chart-steps text-indigo-500 mr-2"></i>Resumen Financiero</h3>
            <div id="chartResumen" style="min-height: 320px;"></div>
        </div>

        <!-- Gráfico Gastos -->
        <div class="bg-white rounded-3xl shadow-xl p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-6"><i class="bi bi-pie-chart-fill text-red-500 mr-2"></i>Distribución de Gastos</h3>
            <div id="chartGastos" style="min-height: 320px;" class="flex items-center justify-center">
                <?php if ($total_gastos == 0): ?>
                    <div class="text-gray-400 text-center">
                        <i class="bi bi-emoji-smile fs-1 d-block mb-2"></i>
                        <p>No hay gastos operativos registrados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- DATOS PHP ---
    const tIngresos = <?= (float)$total_ingresos ?>;
    const tCostos   = <?= (float)$total_costos ?>;
    const tGastos   = <?= (float)$total_gastos ?>;
    const uBruta    = <?= (float)$utilidad_bruta ?>;
    const uNeta     = <?= (float)$utilidad_neta ?>;

    const catGastos = <?= $categorias_gastos ?>;
    const monGastos = <?= $montos_gastos ?>;

    // --- CHART RESUMEN (Bar) ---
    var optionsResumen = {
        series: [{
            name: 'Monto (Q)',
            data: [tIngresos, tCostos, uBruta, tGastos, uNeta]
        }],
        chart: {
            type: 'bar',
            height: 320,
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        plotOptions: {
            bar: {
                borderRadius: 8,
                columnWidth: '55%',
                distributed: true,
            }
        },
        colors: [
            '#10B981', // Ingresos (Green)
            '#F59E0B', // Costos (Yellow)
            '#3B82F6', // U. Bruta (Blue)
            '#EF4444', // Gastos (Red)
            uNeta >= 0 ? '#059669' : '#DC2626' // U. Neta
        ],
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return "Q " + val.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits:0});
            },
            style: { fontSize: '12px', colors: ["#fff"] },
            offsetY: 20
        },
        xaxis: {
            categories: ['Ingresos', 'Costos', 'U. Bruta', 'Gastos', 'U. Neta'],
            labels: { style: { fontSize: '12px', fontWeight: 600 } }
        },
        yaxis: {
            labels: {
                formatter: function (val) { return "Q " + val.toLocaleString(); }
            }
        },
        legend: { show: false },
        tooltip: {
            y: { formatter: function (val) { return "Q " + val.toLocaleString('en-US', {minimumFractionDigits: 2}); } }
        }
    };
    var chartResumen = new ApexCharts(document.querySelector("#chartResumen"), optionsResumen);
    chartResumen.render();

    // --- CHART GASTOS (Donut) ---
    if(tGastos > 0 && catGastos.length > 0) {
        var optionsGastos = {
            series: monGastos,
            labels: catGastos,
            chart: {
                type: 'donut',
                height: 320,
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
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
                                label: 'Total Gastos',
                                formatter: function (w) {
                                    return "Q " + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('en-US', {minimumFractionDigits:2})
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            theme: { palette: 'palette4' },
            stroke: { width: 0 },
            legend: {
                position: 'right',
                offsetY: 0,
                height: 230,
            }
        };
        var chartGastos = new ApexCharts(document.querySelector("#chartGastos"), optionsGastos);
        chartGastos.render();
    }
});
</script>
