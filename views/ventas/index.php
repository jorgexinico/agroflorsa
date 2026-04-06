<?php // views/ventas/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <form method="GET" class="d-flex gap-2 align-items-center">
    <label class="form-label mb-0 fw-semibold text-nowrap">Fecha:</label>
    <input type="date" name="fecha" class="form-control form-control-sm" value="<?= s($fecha) ?>" onchange="this.form.submit()">
  </form>
  <?php if (!empty($_SESSION['turno_id'])): ?>
  <a href="<?= $base ?>/ventas/nueva" class="btn btn-success btn-sm">
    <i class="bi bi-cart-plus me-1"></i>Nueva venta
  </a>
  <?php endif; ?>
</div>

<div class="accordion" id="accordionVentas">
  <?php foreach ($ventas as $v): ?>
  <div class="accordion-item shadow-sm border-0 mb-3 rounded-3 overflow-hidden">
    <h2 class="accordion-header" id="heading<?= $v['id'] ?>">
      <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" 
              data-bs-target="#collapse<?= $v['id'] ?>" aria-expanded="false" aria-controls="collapse<?= $v['id'] ?>">
        <div class="w-100 d-flex justify-content-between align-items-center me-3">
           <div class="d-flex align-items-center gap-3">
              <span class="badge bg-secondary">#<?= $v['id'] ?></span>
              <span class="text-muted small fw-normal"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($v['fecha'])) ?></span>
              <span class="badge bg-<?= $v['estado']==='emitida'?'primary':'secondary' ?> d-none d-md-inline-block"><?= s($v['estado']) ?></span>
           </div>
           <div class="d-flex align-items-center gap-4">
              <div class="d-none d-md-block text-end">
                <span class="text-muted small d-block">Cliente</span>
                <span class="fw-semibold small"><?= s($v['cliente_nombre'] ?? 'Consumidor final') ?></span>
              </div>
              <div class="text-end">
                <span class="text-muted small d-block">Total</span>
                <span class="fw-bold text-success fs-5"><?= formatMoney((float)$v['total']) ?></span>
              </div>
           </div>
        </div>
      </button>
    </h2>
    <div id="collapse<?= $v['id'] ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $v['id'] ?>" data-bs-parent="#accordionVentas">
      <div class="accordion-body bg-white">
        <div class="table-responsive">
          <table class="table table-sm table-borderless align-middle mb-0">
            <thead class="text-muted small uppercase">
              <tr>
                <th class="ps-3">Producto</th>
                <th class="text-center">Cant.</th>
                <th class="text-end">Precio</th>
                <th class="text-end pe-3">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $items = $detallesPorVenta[$v['id']] ?? [];
              foreach ($items as $item): 
              ?>
              <tr class="border-top border-light">
                <td class="ps-3 py-2 fw-medium text-dark"><?= s($item['producto_nombre']) ?></td>
                <td class="text-center"><?= (float)$item['cantidad'] ?> <small class="text-muted"><?= s($item['unidad']) ?></small></td>
                <td class="text-end text-muted"><?= formatMoney((float)$item['precio_unitario']) ?></td>
                <td class="text-end pe-3 fw-bold"><?= formatMoney((float)$item['subtotal']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mt-3 p-3 bg-light rounded d-flex justify-content-between align-items-center">
          <div>
            <span class="badge <?= $v['tipo_pago']==='contado'?'bg-success':'bg-warning text-dark' ?> me-2">Pago: <?= ucfirst(s($v['tipo_pago'])) ?></span>
            <?php if ($v['sucursal_nombre'] ?? false): ?>
            <span class="small text-muted"><i class="bi bi-shop me-1"></i><?= s($v['sucursal_nombre']) ?></span>
            <?php endif; ?>
          </div>
          <a href="<?= $base ?>/ventas/detalle?id=<?= $v['id'] ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-eye me-1"></i>Detalles / Factura
          </a>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if (empty($ventas)): ?>
  <div class="text-center text-muted py-5 bg-white rounded shadow-sm">
    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
    No hay ventas registradas para esta fecha.
  </div>
  <?php endif; ?>
</div>
