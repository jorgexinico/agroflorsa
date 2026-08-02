<?php
// Procesar datos para el gráfico de utilidades por día
$chart_fechas = [];
$chart_utilidades = [];
$agrupado = [];
foreach ($utilidades as $u) {
    $fechaStr = date('Y-m-d', strtotime($u['fecha']));
    if(!isset($agrupado[$fechaStr])) $agrupado[$fechaStr] = 0;
    $agrupado[$fechaStr] += (float)$u['utilidad'];
}
ksort($agrupado);
foreach($agrupado as $f => $val) {
    $chart_fechas[] = date('d/m', strtotime($f));
    $chart_utilidades[] = $val;
}
?>

<div class="container-fluid px-4 pb-5 font-sans">
    <div class="flex items-center justify-between mt-6 mb-4">
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">
            <i class="bi bi-graph-up-arrow text-green-500 mr-2"></i>Ganancias x Venta
        </h1>
    </div>

    <!-- Filtros -->
    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl p-6 mb-8 border border-white/40">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block p-2.5" value="<?= $fecha_inicio ?>">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block p-2.5" value="<?= $fecha_fin ?>">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Sucursal</label>
                <select name="sucursal_id" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block p-2.5">
                    <option value="0">Todas las sucursales</option>
                    <?php foreach ($sucursales as $s): ?>
                    <option value="<?= $s->id ?>" <?= $sucursal_id === $s->id ? 'selected' : '' ?>><?= s($s->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="w-full text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 font-bold rounded-lg text-sm px-4 py-2.5 shadow-lg">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
            </div>
        </form>
        <div class="mt-4 flex gap-2">
            <button type="button" class="text-xs px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 transition-colors" onclick="setFechas('hoy')">Hoy</button>
            <button type="button" class="text-xs px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 transition-colors" onclick="setFechas('ayer')">Ayer</button>
            <button type="button" class="text-xs px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 transition-colors" onclick="setFechas('mes')">Este Mes</button>
        </div>
    </div>

    <!-- Gráfica -->
    <div class="bg-white rounded-3xl shadow-xl p-6 border border-gray-100 mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="bi bi-activity text-blue-500 mr-2"></i>Evolución de Utilidades</h3>
        <div id="chartUtilidades" style="min-height: 300px;"></div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h5 class="m-0 font-bold text-gray-800"><i class="bi bi-list-check text-green-500 mr-2"></i>Detalle de Transacciones</h5>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th class="px-6 py-3">Venta</th>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3">Sucursal</th>
                        <th class="px-6 py-3">Cliente</th>
                        <th class="px-6 py-3 text-right">Venta Total</th>
                        <th class="px-6 py-3 text-right">Utilidad</th>
                        <th class="px-6 py-3 text-center">Margen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sumVentas = 0; $sumCostos = 0; $sumUtilidad = 0;
                    if (empty($utilidades)): 
                    ?>
                        <tr><td colspan="7" class="text-center py-8 text-gray-400">No hay registros para este período.</td></tr>
                    <?php else: ?>
                        <?php foreach ($utilidades as $u): 
                            $sumVentas += $u['total_venta'];
                            $sumCostos += $u['total_costo'];
                            $sumUtilidad += $u['utilidad'];
                            $margen = $u['total_venta'] > 0 ? ($u['utilidad'] / $u['total_venta']) * 100 : 0;
                        ?>
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors cursor-pointer" data-venta-id="<?= $u['venta_id'] ?>" onclick="toggleDetalleVenta(<?= $u['venta_id'] ?>, this)">
                            <td class="px-6 py-4 font-bold text-gray-900">#<?= $u['venta_id'] ?></td>
                            <td class="px-6 py-4"><?= date('d/m/Y', strtotime($u['fecha'])) ?></td>
                            <td class="px-6 py-4"><span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded border border-gray-200"><?= s($u['sucursal_nombre'] ?: 'N/A') ?></span></td>
                            <td class="px-6 py-4"><?= s($u['cliente'] ?: 'Consumidor Final') ?></td>
                            <td class="px-6 py-4 text-right font-bold">Q<?= number_format($u['total_venta'], 2) ?></td>
                            <td class="px-6 py-4 text-right font-bold text-green-600">Q<?= number_format($u['utilidad'], 2) ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-<?= $margen > 30 ? 'green' : ($margen > 15 ? 'blue' : 'yellow') ?>-100 text-<?= $margen > 30 ? 'green' : ($margen > 15 ? 'blue' : 'yellow') ?>-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-<?= $margen > 30 ? 'green' : ($margen > 15 ? 'blue' : 'yellow') ?>-200">
                                    <?= number_format($margen, 1) ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-100 font-bold text-gray-900">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-right uppercase">Totales:</td>
                        <td class="px-6 py-4 text-right text-blue-600">Q<?= number_format($sumVentas, 2) ?></td>
                        <td class="px-6 py-4 text-right text-green-600">Q<?= number_format($sumUtilidad, 2) ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-green-500 text-white px-2 py-1 rounded text-xs">
                                <?= $sumVentas > 0 ? number_format(($sumUtilidad / $sumVentas) * 100, 1) : 0 ?>%
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dates = <?= json_encode($chart_fechas) ?>;
    const data = <?= json_encode($chart_utilidades) ?>;

    if(dates.length > 0) {
        var options = {
            series: [{
                name: 'Utilidad',
                data: data
            }],
            chart: {
                type: 'area',
                height: 300,
                toolbar: { show: false },
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            colors: ['#10B981'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.9,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: {
                categories: dates,
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "Q" + value.toLocaleString();
                    }
                }
            },
            theme: { mode: 'light' }
        };

        var chart = new ApexCharts(document.querySelector("#chartUtilidades"), options);
        chart.render();
    } else {
        document.querySelector("#chartUtilidades").innerHTML = '<p class="text-center text-gray-400 py-10">No hay datos para graficar</p>';
    }
});

function setFechas(tipo) {
    const d = new Date();
    const tzOffset = d.getTimezoneOffset() * 60000;
    let f1 = new Date(Date.now() - tzOffset);
    let f2 = new Date(Date.now() - tzOffset);
    
    if (tipo === 'ayer') {
        f1.setDate(f1.getDate() - 1);
        f2.setDate(f2.getDate() - 1);
    } else if (tipo === 'mes') {
        f1.setDate(1);
    }
    document.querySelector('input[name="fecha_inicio"]').value = f1.toISOString().split('T')[0];
    document.querySelector('input[name="fecha_fin"]').value = f2.toISOString().split('T')[0];
    document.querySelector('form').submit();
}
</script>

<style>
.tr-clickable { cursor: pointer; transition: background-color 0.2s; }
.tr-clickable:hover { background-color: var(--bs-gray-100); }
.tr-expanded { background-color: var(--bs-success-bg-subtle) !important; }
.detalle-row td { padding: 0; border-bottom: none; }
.detalle-container { 
    padding: 1.5rem; 
    background-color: #fcfcfc; 
    border-top: 2px solid var(--bs-success);
    box-shadow: inset 0 3px 10px rgba(0,0,0,0.05);
    animation: slideDown 0.3s ease-out;
}
@keyframes slideDown { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
async function toggleDetalleVenta(id, row) {
    const detailsId = `detalle-v-${id}`;
    let detailsRow = document.getElementById(detailsId);

    if (detailsRow) {
        detailsRow.remove();
        row.classList.remove('tr-expanded');
        return;
    }

    // Cerrar otros
    document.querySelectorAll('.detalle-row').forEach(r => r.remove());
    document.querySelectorAll('.tr-expanded').forEach(r => r.classList.remove('tr-expanded'));

    row.classList.add('tr-expanded');
    detailsRow = document.createElement('tr');
    detailsRow.id = detailsId;
    detailsRow.className = 'detalle-row';
    detailsRow.innerHTML = `<td colspan="8"><div class="detalle-container text-center"><span class="spinner-border spinner-border-sm text-success me-2"></span>Cargando detalle...</div></td>`;
    row.insertAdjacentElement('afterend', detailsRow);

    try {
        const res = await fetch(`<?= $base ?>/reportes/detalle-venta-ajax?id=${id}`);
        const data = await res.json();

        if (!data.ok) throw new Error(data.error);

        const v = data.venta;
        const items = data.detalle;

        let html = `
            <div class="row g-4">
                <div class="col-md-4">
                    <h6 class="text-uppercase text-muted small fw-bold mb-3 border-bottom pb-1">Información de Venta</h6>
                    <div class="mb-1 small"><strong>Cliente:</strong> ${v.cliente_nombre || 'Consumidor Final'}</div>
                    <div class="mb-1 small"><strong>Sucursal:</strong> ${v.sucursal_nombre}</div>
                    <div class="mb-1 small"><strong>Usuario:</strong> ${v.usuario_nombre}</div>
                    <div class="mb-1 small"><strong>Estado:</strong> <span class="badge bg-success">${v.estado.toUpperCase()}</span></div>
                </div>
                <div class="col-md-8">
                    <h6 class="text-uppercase text-muted small fw-bold mb-3 border-bottom pb-1">Productos Vendidos</h6>
                    <table class="table table-sm table-bordered bg-white shadow-sm">
                        <thead class="table-light small">
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio U.</th>
                                <th class="text-end">Costo U.</th>
                                <th class="text-end">Utilidad</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            ${items.map(i => `
                                <tr>
                                    <td>${i.producto_nombre}</td>
                                    <td class="text-end">${parseFloat(i.cantidad).toFixed(2)} ${i.unidad}</td>
                                    <td class="text-end text-muted">Q${parseFloat(i.precio_unitario).toFixed(2)}</td>
                                    <td class="text-end text-muted">Q${parseFloat(i.costo_unitario).toFixed(2)}</td>
                                    <td class="text-end fw-bold text-success">Q${( (i.precio_unitario - i.costo_unitario) * i.cantidad ).toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="4" class="text-end">TOTAL UTILIDAD VENTA:</td>
                                <td class="text-end text-success">Q${(v.total - items.reduce((acc, i) => acc + (i.costo_unitario * i.cantidad), 0)).toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `;
        detailsRow.querySelector('.detalle-container').innerHTML = html;

    } catch (err) {
        console.error(err);
        detailsRow.querySelector('.detalle-container').innerHTML = `<div class="alert alert-danger mb-0">Error al cargar datos: ${err.message}</div>`;
    }
}
</script>
