<?php // views/inventario/kardex.php ?>
<div class="row mb-3">
    <div class="col-auto">
        <a href="/<?= $_ENV['APP_NAME'] ?>/inventario?sucursal_id=<?= $sucursal->id ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Regresar al Inventario
        </a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-1 text-primary"><?= s($producto->nombre) ?></h5>
                <p class="text-muted small mb-0">SKU: <?= s($producto->sku ?: '—') ?> | Sucursal: <?= s($sucursal->nombre) ?></p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <span class="text-muted small">Costo Ref. Actual:</span>
                <span class="fs-5 fw-bold text-success">Q<?= number_format($producto->precio_publico, 2) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <i class="bi bi-clock-history me-2"></i>Historial de Movimientos (Entradas y Salidas)
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Referencia</th>
                    <th class="text-center">Signo</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Costo Unit.</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted small">No hay movimientos registrados para este producto.</td></tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $m): ?>
                    <tr>
                        <td class="small"><?= date('d/m/Y H:i', strtotime($m['creado_en'])) ?></td>
                        <td>
                            <span class="badge bg-<?= ($m['signo'] > 0) ? 'success' : 'danger' ?> bg-opacity-10 text-<?= ($m['signo'] > 0) ? 'success' : 'danger' ?> border border-<?= ($m['signo'] > 0) ? 'success' : 'danger' ?> border-opacity-25 px-2">
                                <?= strtoupper(str_replace('_', ' ', $m['tipo'])) ?>
                            </span>
                        </td>
                        <td class="small text-muted">
                            <?php if ($m['referencia_tipo']): ?>
                                <?= s($m['referencia_tipo']) ?> #<?= $m['referencia_id'] ?>
                                <?php if (isset($m['usuario_nombre'])): ?>
                                    <br><i class="bi bi-person me-1"></i><?= s($m['usuario_nombre']) ?>
                                <?php endif; ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <i class="bi bi-<?= ($m['signo'] > 0) ? 'plus-circle-fill text-success' : 'dash-circle-fill text-danger' ?>"></i>
                        </td>
                        <td class="text-end fw-bold"><?= number_format($m['cantidad'], 3) ?></td>
                        <td class="text-end small text-muted">
                            <?= $m['costo_unitario'] ? 'Q' . number_format($m['costo_unitario'], 2) : '—' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
