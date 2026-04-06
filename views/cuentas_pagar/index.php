<?php // views/cuentas_pagar/index.php ?>
<div class="row align-items-center mb-3">
    <div class="col-md-6">
        <p class="text-muted mb-0"><?= count($cuentas) ?> cuentas por pagar pendientes</p>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <form method="GET" action="<?= $base ?>/cuentas-pagar" class="d-inline-block">
            <div class="input-group input-group-sm">
                <select name="proveedor_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Todos los Proveedores --</option>
                    <?php foreach ($proveedores as $p): ?>
                        <option value="<?= $p->id ?>" <?= $proveedor_id == $p->id ? 'selected' : '' ?>>
                            <?= s($p->nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($proveedor_id > 0): ?>
                <a href="<?= $base ?>/cuentas-pagar" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Fecha Compra</th>
                    <th>Proveedor</th>
                    <th>Sucursal</th>
                    <th>Total Orig.</th>
                    <th>Pagado</th>
                    <th>Saldo</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cuentas)): ?>
                <tr><td colspan="8" class="text-center py-4">No hay cuentas pendientes.</td></tr>
                <?php endif; ?>
                <?php foreach ($cuentas as $c): ?>
                <tr>
                    <td><?= s(substr($c['compra_fecha'], 0, 10)) ?></td>
                    <td class="fw-semibold text-primary"><?= s($c['proveedor_nombre'] ?? 'Proveedor Eliminado') ?></td>
                    <td><span class="badge bg-light text-dark"><i class="bi bi-shop me-1"></i><?= s($c['sucursal_nombre']) ?></span></td>
                    <td>Q <?= number_format($c['total'], 2) ?></td>
                    <td class="text-success">Q <?= number_format($c['pagado'], 2) ?></td>
                    <td class="fw-bold text-danger">Q <?= number_format($c['saldo'], 2) ?></td>
                    <td>
                        <?php if ($c['estado'] === 'pendiente'): ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pendiente</span>
                        <?php else: ?>
                            <span class="badge bg-info text-dark"><i class="bi bi-circle-half me-1"></i>Parcial</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a href="<?= $base ?>/cuentas-pagar/detalle?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                            <i class="bi bi-cash-coin me-1"></i>Abonar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
