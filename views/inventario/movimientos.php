<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= $filtros['fecha_inicio'] ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= $filtros['fecha_fin'] ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Sucursal</label>
                <select name="sucursal_id" class="form-select">
                    <option value="">Todas las sucursales</option>
                    <?php foreach ($sucursales as $s): ?>
                        <option value="<?= $s->id ?>" <?= $filtros['sucursal_id'] == $s->id ? 'selected' : '' ?>><?= s($s->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Tipo de Movimiento</label>
                <select name="tipo" class="form-select">
                    <option value="">Cualquier tipo</option>
                    <option value="venta" <?= $filtros['tipo'] === 'venta' ? 'selected' : '' ?>>Venta</option>
                    <option value="compra" <?= $filtros['tipo'] === 'compra' ? 'selected' : '' ?>>Compra</option>
                    <option value="ajuste" <?= $filtros['tipo'] === 'ajuste' ? 'selected' : '' ?>>Ajuste</option>
                    <option value="traslado_salida" <?= $filtros['tipo'] === 'traslado_salida' ? 'selected' : '' ?>>Traslado (Salida)</option>
                    <option value="traslado_entrada" <?= $filtros['tipo'] === 'traslado_entrada' ? 'selected' : '' ?>>Traslado (Entrada)</option>
                </select>
            </div>
            <div class="col-md-9">
                <label class="form-label small fw-bold">Filtrar por Producto</label>
                <select name="producto_id" class="form-select select2">
                    <option value="">Todos los productos</option>
                    <?php foreach ($productos as $p): ?>
                        <option value="<?= $p->id ?>" <?= $filtros['producto_id'] == $p->id ? 'selected' : '' ?>><?= s($p->nombre) ?> [<?= s($p->sku ?: 'S/SKU') ?>]</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i> Aplicar Filtros
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Historial General de Movimientos</h5>
        <button class="btn btn-sm btn-outline-success" onclick="exportarExcelMovs()">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="tablaMovimientos">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Sucursal</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Costo Ref.</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron movimientos con los filtros aplicados.</td></tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $m): 
                        $badge = 'bg-secondary';
                        $tipo_nombre = $m['tipo'];
                        switch($m['tipo']) {
                            case 'venta': $badge = 'bg-danger'; break;
                            case 'compra': $badge = 'bg-success'; $tipo_nombre = 'Compra'; break;
                            case 'ajuste': $badge = 'bg-info text-dark'; break;
                            case 'traslado_salida': $badge = 'bg-warning text-dark'; $tipo_nombre = 'Envío Traslado'; break;
                            case 'traslado_entrada': $badge = 'bg-primary'; $tipo_nombre = 'Recibo Traslado'; break;
                        }
                    ?>
                    <tr>
                        <td class="small"><?= date('d/m/Y H:i', strtotime($m['creado_en'])) ?></td>
                        <td><span class="small text-muted"><?= s($m['sucursal_nombre']) ?></span></td>
                        <td>
                            <div class="fw-bold small"><?= s($m['producto_nombre']) ?></div>
                            <code class="x-small"><?= s($m['sku'] ?: '—') ?></code>
                        </td>
                        <td><span class="badge <?= $badge ?> rounded-pill" style="font-size: 0.7rem;"><?= strtoupper($tipo_nombre) ?></span></td>
                        <td class="text-end fw-bold <?= $m['signo'] > 0 ? 'text-success' : 'text-danger' ?>">
                            <?= $m['signo'] > 0 ? '+' : '-' ?><?= number_format($m['cantidad'], 2) ?> 
                            <span class="small fw-normal text-muted"><?= s($m['unidad']) ?></span>
                        </td>
                        <td class="text-end small text-muted">Q<?= number_format($m['costo_unitario'] ?: 0, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<script>
function exportarExcelMovs() {
    const tabla = document.getElementById('tablaMovimientos');
    const wb = XLSX.utils.table_to_book(tabla, {sheet: "Movimientos"});
    const fecha = new Date().toISOString().slice(0, 10);
    XLSX.writeFile(wb, `movimientos_inventario_${fecha}.xlsx`);
}
</script>
