<?php // views/turnos/detalle.php ?>
<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-info-circle me-2"></i>Información del Turno</div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><th>Sucursal</th><td><?= s($turno['sucursal_nombre']) ?></td></tr>
          <tr><th>Usuario</th><td><?= s($turno['usuario_nombre']) ?></td></tr>
          <tr><th>Apertura</th><td><?= date('d/m/Y H:i', strtotime($turno['abierto_en'])) ?></td></tr>
          <tr><th>Monto inicial</th><td><?= formatMoney((float)$turno['monto_inicial']) ?></td></tr>
          <tr><th>Estado</th><td><span class="badge <?= $turno['estado']==='abierto'?'bg-success':'bg-secondary' ?>"><?= s($turno['estado']) ?></span></td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-graph-up me-2"></i>Resumen</div>
      <div class="card-body">
        <div class="row g-3 text-center">
          <div class="col-6">
            <div class="text-success fw-bold fs-4"><?= formatMoney($totales['total_ventas']) ?></div>
            <div class="text-muted small">Total ventas</div>
          </div>
          <div class="col-6">
            <div class="fw-bold fs-4"><?= $totales['num_ventas'] ?></div>
            <div class="text-muted small">Facturas</div>
          </div>
        </div>
        <?php if ($turno['estado'] === 'abierto'): ?>
        <div class="d-flex gap-2 mt-3">
          <a href="/<?= $_ENV['APP_NAME'] ?>/ventas/nueva" class="btn btn-sm btn-success flex-fill">
            <i class="bi bi-cart-plus me-1"></i>Nueva venta
          </a>
          <a href="/<?= $_ENV['APP_NAME'] ?>/turnos/cerrar?id=<?= $turno['id'] ?>" class="btn btn-sm btn-outline-danger flex-fill">
            <i class="bi bi-stop-circle me-1"></i>Cerrar turno
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Ventas del turno -->
<div class="card">
  <div class="card-header"><i class="bi bi-receipt me-2"></i>Ventas del turno</div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Tipo pago</th><th class="text-end">Total</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($ventas as $v): ?>
        <tr>
          <td><?= $v['id'] ?></td>
          <td class="text-muted small"><?= date('H:i', strtotime($v['fecha'])) ?></td>
          <td><?= s($v['cliente_nombre'] ?? 'Consumidor final') ?></td>
          <td><span class="badge <?= $v['tipo_pago']==='contado'?'bg-success':'bg-warning text-dark' ?>"><?= s($v['tipo_pago']) ?></span></td>
          <td class="text-end fw-semibold"><?= formatMoney((float)$v['total']) ?></td>
          <td><a href="/<?= $_ENV['APP_NAME'] ?>/ventas/detalle?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-secondary py-0"><i class="bi bi-eye"></i></a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($ventas)): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">Sin ventas en este turno.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
