<div class="alert alert-warning border-warning bg-warning bg-opacity-10 d-flex align-items-center mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
    <div>
        <strong>Control de Caducidad:</strong> A continuación se muestran los lotes de productos que vencen en los próximos 90 días y tienen inventario disponible.
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-calendar-event me-2 text-danger"></i>Lotes Próximos a Vencer</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Vencimiento</th>
                    <th>Producto</th>
                    <th>Sucursal</th>
                    <th>Código Lote</th>
                    <th class="text-end">Stock Actual</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vencimientos)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No hay vencimientos reportados en los próximos 90 días.</td></tr>
                <?php else: ?>
                    <?php foreach ($vencimientos as $v): 
                        $dias = (strtotime($v['fecha_vencimiento']) - time()) / 86400;
                        $bg = $dias < 15 ? 'danger' : ($dias < 45 ? 'warning' : 'info');
                    ?>
                    <tr>
                        <td class="fw-bold text-<?= $bg ?>"><?= date('d/m/Y', strtotime($v['fecha_vencimiento'])) ?></td>
                        <td>
                            <div class="fw-bold"><?= s($v['producto']) ?></div>
                            <div class="small text-muted">SKU: <?= s($v['sku'] ?: '—') ?></div>
                        </td>
                        <td><?= s($v['sucursal']) ?></td>
                        <td><code class="text-dark"><?= s($v['codigo_lote'] ?: 'SIN CÓDIGO') ?></code></td>
                        <td class="text-end fw-bold"><?= number_format($v['cantidad'], 3) ?></td>
                        <td class="text-center">
                            <?php if ($dias < 0): ?>
                                <span class="badge bg-dark">VENCIDO</span>
                            <?php else: ?>
                                <span class="badge bg-<?= $bg ?> text-white"><?= ceil($dias) ?> días</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
