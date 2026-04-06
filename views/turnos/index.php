<?php // views/turnos/index.php
$errMap = ['ya_abierto' => 'Ya tienes un turno abierto.'];
?>
<?php if (!empty($_GET['err']) && isset($errMap[$_GET['err']])): ?>
<div class="alert alert-warning alert-dismissible fade show">
  <i class="bi bi-exclamation-triangle me-2"></i><?= $errMap[$_GET['err']] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($turnoActivo): ?>
<div class="alert alert-success d-flex justify-content-between align-items-center">
  <div>
    <i class="bi bi-circle-fill blink me-2" style="font-size:.6rem"></i>
    <strong>Turno #<?= $turnoActivo['id'] ?> activo</strong> —
    <?= s($turnoActivo['sucursal_nombre']) ?>
    · Abierto: <?= date('d/m H:i', strtotime($turnoActivo['abierto_en'])) ?>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= $base ?>/ventas/nueva" class="btn btn-sm btn-success">
      <i class="bi bi-cart-plus me-1"></i>Nueva venta
    </a>
    <a href="<?= $base ?>/turnos/detalle?id=<?= $turnoActivo['id'] ?>" class="btn btn-sm btn-outline-success">Ver detalle</a>
    <a href="<?= $base ?>/turnos/cerrar?id=<?= $turnoActivo['id'] ?>" class="btn btn-sm btn-outline-danger">
      <i class="bi bi-stop-circle me-1"></i>Cerrar turno
    </a>
  </div>
</div>
<?php else: ?>
<div class="mb-3">
  <a href="<?= $base ?>/turnos/abrir" class="btn btn-success">
    <i class="bi bi-play-circle me-1"></i>Abrir nuevo turno
  </a>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-clock-history me-2"></i>Historial de turnos</span>
    <?php if (!empty($esAdmin)): ?>
      <span class="badge bg-secondary">Vista global</span>
    <?php endif; ?>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Turno</th>
          <?php if (!empty($esAdmin)): ?><th>Usuario</th><?php endif; ?>
          <th>Sucursal</th>
          <th>Apertura</th>
          <th>Cierre</th>
          <th>Estado</th>
          <th class="text-end">Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($turnos as $t): ?>
        <tr>
          <td>#<?= $t['id'] ?></td>
          <?php if (!empty($esAdmin)): ?>
          <td class="small"><?= s($t['usuario_nombre']) ?></td>
          <?php endif; ?>
          <td><?= s($t['sucursal_nombre']) ?></td>
          <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($t['abierto_en'])) ?></td>
          <td class="text-muted small"><?= $t['cerrado_en'] ? date('d/m/Y H:i', strtotime($t['cerrado_en'])) : '—' ?></td>
          <td><span class="badge <?= $t['estado']==='abierto'?'bg-success':'bg-secondary' ?>"><?= s($t['estado']) ?></span></td>
          <td class="text-end">
            <a href="<?= $base ?>/turnos/detalle?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
            <?php if ($t['estado']==='cerrado'): ?>
            <a href="<?= $base ?>/turnos/reporte?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-info ms-1"><i class="bi bi-file-text"></i></a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($turnos)): ?>
        <tr><td colspan="<?= !empty($esAdmin) ? 7 : 6 ?>" class="text-center text-muted py-4">No hay turnos registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
