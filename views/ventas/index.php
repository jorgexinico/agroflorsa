<?php // views/ventas/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <form method="GET" class="d-flex gap-2 align-items-center">
    <label class="form-label mb-0 fw-semibold text-nowrap">Fecha:</label>
    <input type="date" name="fecha" class="form-control form-control-sm" value="<?= s($fecha) ?>" onchange="this.form.submit()">
  </form>
  <?php if (!empty($_SESSION['turno_id'])): ?>
  <a href="/<?= $_ENV['APP_NAME'] ?>/ventas/nueva" class="btn btn-success btn-sm">
    <i class="bi bi-cart-plus me-1"></i>Nueva venta
  </a>
  <?php endif; ?>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Hora</th><th>Cliente</th><th class="d-none-mobile">Tipo pago</th><th class="d-none-mobile">Estado</th><th class="text-end">Total</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($ventas as $v): ?>
        <tr>
          <td><?= $v['id'] ?></td>
          <td class="text-muted small"><?= date('H:i', strtotime($v['fecha'])) ?></td>
          <td>
            <div class="text-truncate" style="max-width: 150px;"><?= s($v['cliente_nombre'] ?? 'Consumidor final') ?></div>
          </td>
          <td class="d-none-mobile"><span class="badge <?= $v['tipo_pago']==='contado'?'bg-success':'bg-warning text-dark' ?>"><?= s($v['tipo_pago']) ?></span></td>
          <td class="d-none-mobile"><span class="badge bg-<?= $v['estado']==='emitida'?'primary':'secondary' ?>"><?= s($v['estado']) ?></span></td>
          <td class="text-end fw-semibold text-nowrap"><?= formatMoney((float)$v['total']) ?></td>
          <td><a href="/<?= $_ENV['APP_NAME'] ?>/ventas/detalle?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-secondary py-0 px-2"><i class="bi bi-chevron-right"></i></a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($ventas)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No hay ventas para esta fecha.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
