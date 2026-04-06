<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0 text-gray-800">Control de Lotes y Vencimientos</h1>
</div>

<div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body p-2 d-flex flex-wrap gap-2">
        <a href="?estado=todos" class="btn <?= $estado === 'todos' ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">
            <i class="bi bi-list-ul me-1"></i>Todos
        </a>
        <a href="?estado=vigentes" class="btn <?= $estado === 'vigentes' ? 'btn-success' : 'btn-outline-success' ?> btn-sm">
            <i class="bi bi-check-circle me-1"></i>Vigentes
        </a>
        <a href="?estado=proximos" class="btn <?= $estado === 'proximos' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?> btn-sm">
            <i class="bi bi-exclamation-triangle me-1"></i>Próximos a Vencer (90 d)
        </a>
        <a href="?estado=vencidos" class="btn <?= $estado === 'vencidos' ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm">
            <i class="bi bi-x-circle me-1"></i>Vencidos
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-box-seam me-2 text-success"></i>Registros de Lotes</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Código Lote</th>
                    <th>Producto</th>
                    <th>Sucursal (Inventario)</th>
                    <th>Vencimiento</th>
                    <th class="text-end">Stock</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vencimientos)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>No hay lotes en esta categoría.</td></tr>
                <?php else: ?>
                    <?php foreach ($vencimientos as $v): 
                        if ($v['fecha_vencimiento']) {
                            $dias = (strtotime($v['fecha_vencimiento']) - time()) / 86400;
                            $bg = $dias < 0 ? 'danger' : ($dias < 30 ? 'warning' : ($dias < 90 ? 'info' : 'success'));
                            $diasLabel = $dias < 0 ? 'VENCIDO' : ceil($dias) . ' días';
                            $fechaFormato = date('d/m/Y', strtotime($v['fecha_vencimiento']));
                        } else {
                            $dias = 999;
                            $bg = 'secondary';
                            $diasLabel = 'Sin/Venc';
                            $fechaFormato = '—';
                        }
                        
                        $textColor = $bg === 'warning' ? 'text-dark' : 'text-white';
                    ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-primary"><?= s($v['codigo_lote'] ?: 'SIN CÓDIGO') ?></div>
                        </td>
                        <td>
                            <div class="fw-bold"><?= s($v['producto']) ?></div>
                            <div class="small text-muted">SKU: <?= s($v['sku'] ?: '—') ?></div>
                        </td>
                        <td>
                            <?= s($v['sucursal'] ?: 'Almacén General / No asignado') ?>
                        </td>
                        <td class="fw-bold text-<?= $bg === 'warning' || $bg === 'secondary' ? 'dark' : $bg ?>">
                            <?= $fechaFormato ?>
                        </td>
                        <td class="text-end fw-bold">
                            <?= number_format($v['cantidad'], 3) ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $bg ?> <?= $textColor ?>"><?= $diasLabel ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
