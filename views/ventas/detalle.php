<?php // views/ventas/detalle.php ?>

<?php if (!empty($_GET['ok']) && $_GET['ok'] === 'anulada'): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
  <i class="bi bi-exclamation-triangle me-2"></i><strong>Venta anulada.</strong> El stock ha sido restaurado.
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($_GET['err']) && $_GET['err'] === 'db'): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="bi bi-x-circle me-2"></i>Error al procesar la operación. Intenta de nuevo.
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

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
          <tr>
            <th>Estado</th>
            <td>
              <?php
              $badgeCls = match($venta['estado']) {
                'anulada' => 'bg-danger',
                'emitida' => 'bg-primary',
                default   => 'bg-secondary',
              };
              ?>
              <span class="badge <?= $badgeCls ?>"><?= s($venta['estado']) ?></span>
            </td>
          </tr>
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
            <tr><td colspan="3" class="text-muted text-center small py-2">Sin pagos de contado registrados.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php if ($venta['tipo_pago'] === 'credito' && isset($cxc) && $cxc): ?>
      <div class="card-footer">
        <?php
        $cxcBadge = match($cxc['estado']) {
          'pagada'  => 'bg-success',
          'parcial' => 'bg-warning text-dark',
          'anulada' => 'bg-secondary',
          default   => 'bg-danger',
        };
        ?>
        <div class="d-flex align-items-center gap-2">
          <span class="badge <?= $cxcBadge ?>"><?= ucfirst($cxc['estado']) ?></span>
          <span class="small text-muted">
            Saldo pendiente: <strong><?= formatMoney((float)$cxc['saldo']) ?></strong>
          </span>
          <?php if (in_array($cxc['estado'], ['pendiente','parcial'])): ?>
          <a href="/<?= $_ENV['APP_NAME'] ?>/cuentas-cobrar/detalle?id=<?= $cxc['id'] ?>"
             class="btn btn-sm btn-outline-warning ms-auto">
            <i class="bi bi-cash-coin me-1"></i>Registrar Abono
          </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
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

<div class="mt-3 d-flex gap-2">
  <a href="/<?= $_ENV['APP_NAME'] ?>/ventas" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Volver
  </a>

  <?php if ($venta['estado'] !== 'anulada'): ?>
  <button type="button" class="btn btn-sm btn-outline-danger ms-auto"
          data-bs-toggle="modal" data-bs-target="#modalAnular">
    <i class="bi bi-x-circle me-1"></i>Anular Venta
  </button>
  <?php endif; ?>
</div>

<!-- Modal confirmación anulación -->
<?php if ($venta['estado'] !== 'anulada'): ?>
<div class="modal fade" id="modalAnular" tabindex="-1" aria-labelledby="modalAnularLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalAnularLabel">
          <i class="bi bi-exclamation-triangle me-2"></i>Anular Venta #<?= $venta['id'] ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro de que deseas <strong>anular</strong> esta venta?</p>
        <ul class="small text-muted">
          <li>El stock de todos los productos será <strong>restaurado</strong>.</li>
          <li>La cuenta por cobrar (si existe) será marcada como <strong>anulada</strong>.</li>
          <li>Esta acción <strong>no se puede deshacer</strong>.</li>
        </ul>
        <div class="alert alert-warning py-2 mb-0">
          <strong>Total a revertir: <?= formatMoney((float)$venta['total']) ?></strong>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <form method="POST" action="/<?= $_ENV['APP_NAME'] ?>/ventas/anular">
          <input type="hidden" name="id" value="<?= $venta['id'] ?>">
          <button type="submit" class="btn btn-danger">
            <i class="bi bi-x-circle me-1"></i>Confirmar Anulación
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
