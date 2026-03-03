<?php // views/ventas/detalle.php ?>
<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-info-circle me-2"></i>Información de Venta #<?= $venta['id'] ?></div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><th>Sucursal</th><td><?= s($venta['sucursal_nombre']) ?></td></tr>
          <tr><th>Cliente</th><td><?= s($venta['cliente_nombre'] ?? 'Consumidor final') ?></td></tr>
          <tr><th>Fecha</th><td><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></td></tr>
          <tr><th>Tipo pago</th><td><span class="badge <?= $venta['tipo_pago']==='contado'?'bg-success':'bg-warning text-dark' ?>"><?= s($venta['tipo_pago']) ?></span></td></tr>
          <tr><th>Estado</th><td><span class="badge bg-primary"><?= s($venta['estado']) ?></span></td></tr>
          <tr><th class="text-success fs-5">Total</th><td class="fw-bold fs-5 text-success"><?= formatMoney((float)$venta['total']) ?></td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-credit-card me-2"></i>Pagos</div>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead><tr><th>Método</th><th>Fecha</th><th class="text-end">Monto</th></tr></thead>
          <tbody>
            <?php foreach ($pagos as $p): ?>
            <tr>
              <td class="text-capitalize"><?= s($p['metodo']) ?></td>
              <td class="text-muted small"><?= date('d/m H:i', strtotime($p['fecha'])) ?></td>
              <td class="text-end fw-semibold"><?= formatMoney((float)$p['monto']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($pagos)): ?>
            <tr><td colspan="3" class="text-muted text-center small py-2">Sin pagos registrados.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Detalle de productos -->
<div class="card">
  <div class="card-header d-flex justify-content-between">
    <span><i class="bi bi-list-ul me-2"></i>Detalle de productos</span>
    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-printer me-1"></i>Imprimir
    </button>
  </div>
  <div class="table-responsive">
    <table class="table table-sm mb-0">
      <thead><tr><th>Producto</th><th>Unidad</th><th class="text-end">Cant.</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr></thead>
      <tbody>
        <?php foreach ($detalle as $d): ?>
        <tr>
          <td><?= s($d['producto_nombre']) ?></td>
          <td class="text-muted"><?= s($d['unidad_abreviatura']) ?></td>
          <td class="text-end"><?= number_format((float)$d['cantidad'], 3) ?></td>
          <td class="text-end"><?= formatMoney((float)$d['precio_unitario']) ?></td>
          <td class="text-end fw-semibold"><?= formatMoney((float)$d['subtotal']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr>
          <td colspan="4" class="text-end fw-bold">TOTAL</td>
          <td class="text-end fw-bold text-success"><?= formatMoney((float)$venta['total']) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<div class="mt-3">
  <a href="/<?= $_ENV['APP_NAME'] ?>/ventas" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Volver
  </a>
</div>
