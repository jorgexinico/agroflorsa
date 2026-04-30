<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-gray-800"><i class="bi bi-wallet2 me-2"></i><?= $titulo ?></h1>
    <a href="<?= $base ?>/gastos/crear" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Registrar Gasto
    </a>
</div>

<?php if (isset($_GET['ok']) && $_GET['ok'] == '1'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>Gasto registrado correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['ok']) && $_GET['ok'] == '2'): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-2"></i>Gasto anulado correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Sucursal</label>
                <select name="sucursal_id" class="form-select">
                    <option value="0">Todas (Global y sucursales)</option>
                    <?php foreach ($sucursales as $s): ?>
                    <option value="<?= $s->id ?>" <?= $sucursal_id === $s->id ? 'selected' : '' ?>><?= s($s->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
            </div>
            <div class="col-md-3">
                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setFechas('hoy')">Hoy</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setFechas('mes')">Mes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th>Sucursal</th>
                    <th>Registrado por</th>
                    <th>Comprobante</th>
                    <th class="text-end">Monto</th>
                    <th class="text-center">Estado</th>
                    <?php if($_SESSION['usuario_rol'] === 'admin'): ?>
                    <th class="text-center">Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalSuma = 0;
                if (empty($gastos)): 
                ?>
                <tr><td colspan="9" class="text-center py-4 text-muted">No hay gastos registrados en este período.</td></tr>
                <?php else: ?>
                    <?php foreach ($gastos as $g): 
                        if ($g['estado'] !== 'anulado') {
                            $totalSuma += (float)$g['monto'];
                        }
                    ?>
                    <tr class="<?= $g['estado'] === 'anulado' ? 'table-danger text-decoration-line-through text-muted' : '' ?>">
                        <td class="small"><?= date('d/m/Y H:i', strtotime($g['fecha'])) ?></td>
                        <td><span class="badge bg-info text-dark"><?= s($g['categoria_nombre']) ?></span></td>
                        <td><?= s($g['descripcion']) ?></td>
                        <td>
                            <?php if($g['sucursal_nombre']): ?>
                                <span class="badge bg-secondary"><?= s($g['sucursal_nombre']) ?></span>
                            <?php else: ?>
                                <span class="badge bg-dark">Global (Admin)</span>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= s($g['usuario_nombre']) ?></td>
                        <td class="small text-muted"><?= s($g['comprobante'] ?? '—') ?></td>
                        <td class="text-end fw-bold text-danger">Q<?= number_format($g['monto'], 2) ?></td>
                        <td class="text-center">
                            <?php if ($g['estado'] === 'completado'): ?>
                                <span class="badge bg-success">Completado</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Anulado</span>
                            <?php endif; ?>
                        </td>
                        <?php if($_SESSION['usuario_rol'] === 'admin'): ?>
                        <td class="text-center">
                            <?php if ($g['estado'] !== 'anulado'): ?>
                            <form method="POST" action="<?= $base ?>/gastos/eliminar" class="d-inline" onsubmit="return confirm('¿Seguro que deseas anular este gasto?');">
                                <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Anular gasto">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </form>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="6" class="text-end text-uppercase">Total Gastos Operativos:</td>
                    <td class="text-end text-danger fs-5">Q<?= number_format($totalSuma, 2) ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
function setFechas(tipo) {
    const d = new Date();
    const tzOffset = d.getTimezoneOffset() * 60000;
    
    let f1 = new Date(Date.now() - tzOffset);
    let f2 = new Date(Date.now() - tzOffset);
    
    if (tipo === 'mes') {
        f1.setDate(1);
    }
    
    document.querySelector('input[name="fecha_inicio"]').value = f1.toISOString().split('T')[0];
    document.querySelector('input[name="fecha_fin"]').value = f2.toISOString().split('T')[0];
    document.querySelector('form').submit();
}
</script>
