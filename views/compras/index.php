<?php // views/compras/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0"><?= count($compras) ?> compras registradas</p>
  <a href="/<?= $_ENV['APP_NAME'] ?>/compras/crear" class="btn btn-success btn-sm">
    <i class="bi bi-plus-circle me-1"></i>Nueva compra
  </a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Fecha</th><th>Proveedor</th><th>Sucursal</th><th>Estado</th><th class="text-end">Total</th></tr></thead>
      <tbody>
        <?php foreach ($compras as $c): ?>
        <tr>
          <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($c['fecha'])) ?></td>
          <td><?= s($c['proveedor_nombre'] ?? 'Sin proveedor') ?></td>
          <td><?= s($c['sucursal_nombre']) ?></td>
          <td><span class="badge bg-<?= $c['estado']==='recibida'?'success':'secondary' ?>"><?= s($c['estado']) ?></span></td>
          <td class="text-end fw-semibold"><?= formatMoney((float)$c['total']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($compras)): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No hay compras registradas.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
