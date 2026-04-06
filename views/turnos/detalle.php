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
          <a href="<?= $base ?>/ventas/nueva" class="btn btn-sm btn-success flex-fill">
            <i class="bi bi-cart-plus me-1"></i>Nueva venta
          </a>
          <a href="<?= $base ?>/turnos/cerrar?id=<?= $turno['id'] ?>" class="btn btn-sm btn-outline-danger flex-fill">
            <i class="bi bi-stop-circle me-1"></i>Cerrar turno
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Ventas del turno -->
<div class="card bg-light border-0">
  <div class="card-header bg-transparent border-0 px-0 mb-2">
    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-receipt me-2"></i>Historial de Ventas</h5>
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
              <thead class="text-muted">
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
            <span class="badge <?= $v['tipo_pago']==='contado'?'bg-success':'bg-warning text-dark' ?>">Pago: <?= ucfirst(s($v['tipo_pago'])) ?></span>
            <a href="<?= $base ?>/ventas/detalle?id=<?= $v['id'] ?>" class="btn btn-sm btn-primary">
              <i class="bi bi-eye me-1"></i>Ver Factura Completa
            </a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($ventas)): ?>
    <div class="text-center text-muted py-5 bg-white rounded shadow-sm">
      <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
      Sin ventas registradas en este turno.
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="mt-3">
  <a href="<?= $base ?>/turnos" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Volver a Turnos
  </a>
</div>
