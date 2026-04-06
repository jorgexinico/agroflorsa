<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2 text-success"></i>Detalle de Ganancias por Venta <small class="text-muted fw-normal ms-2">(Clic en cada fila para ver detalle)</small></h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th class="text-end">Venta Total</th>
                    <th class="text-end">Costo Total</th>
                    <th class="text-end">Utilidad</th>
                    <th class="text-end">Margen (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sumVentas = 0; $sumCostos = 0; $sumUtilidad = 0;
                if (empty($utilidades)): 
                ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No hay registros para este período.</td></tr>
                <?php else: ?>
                    <?php foreach ($utilidades as $u): 
                        $sumVentas += $u['total_venta'];
                        $sumCostos += $u['total_costo'];
                        $sumUtilidad += $u['utilidad'];
                        $margen = $u['total_venta'] > 0 ? ($u['utilidad'] / $u['total_venta']) * 100 : 0;
                    ?>
                    <tr class="tr-clickable" data-venta-id="<?= $u['venta_id'] ?>" onclick="toggleDetalleVenta(<?= $u['venta_id'] ?>, this)">
                        <td>#<?= $u['venta_id'] ?></td>
                        <td class="small"><?= date('d/m/Y', strtotime($u['fecha'])) ?></td>
                        <td><?= s($u['cliente'] ?: 'Consumidor Final') ?></td>
                        <td class="text-end fw-bold">Q<?= number_format($u['total_venta'], 2) ?></td>
                        <td class="text-end text-muted">Q<?= number_format($u['total_costo'], 2) ?></td>
                        <td class="text-end text-success fw-bold">Q<?= number_format($u['utilidad'], 2) ?></td>
                        <td class="text-end">
                            <span class="badge bg-<?= $margen > 30 ? 'success' : ($margen > 15 ? 'info' : 'warning') ?> bg-opacity-10 text-<?= $margen > 30 ? 'success' : ($margen > 15 ? 'info' : 'warning') ?> border px-2">
                                <?= number_format($margen, 1) ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">TOTALES:</td>
                    <td class="text-end text-primary">Q<?= number_format($sumVentas, 2) ?></td>
                    <td class="text-end text-muted">Q<?= number_format($sumCostos, 2) ?></td>
                    <td class="text-end text-success">Q<?= number_format($sumUtilidad, 2) ?></td>
                    <td class="text-end">
                        <span class="badge bg-success px-2">
                            <?= $sumVentas > 0 ? number_format(($sumUtilidad / $sumVentas) * 100, 1) : 0 ?>%
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

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
    detailsRow.innerHTML = `<td colspan="7"><div class="detalle-container text-center"><span class="spinner-border spinner-border-sm text-success me-2"></span>Cargando detalle...</div></td>`;
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
