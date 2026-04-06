<?php // views/turnos/reporte.php
$diferencia = (float)($turno['diferencia'] ?? 0);
?>
<div class="row justify-content-center">
  <div class="col-lg-8">

    <!-- Header del reporte -->
    <div class="card mb-4">
      <div class="card-body text-center py-4">
        <i class="bi bi-flower3 text-success fs-2 d-block mb-2"></i>
        <h4 class="fw-bold mb-1">Reporte de Cierre de Turno #<?= $turno['id'] ?></h4>
        <p class="text-muted mb-0"><?= s($turno['sucursal_nombre']) ?> — <?= s($turno['usuario_nombre']) ?></p>
        <small class="text-muted">
          <?= date('d/m/Y H:i', strtotime($turno['abierto_en'])) ?>
          → <?= $turno['cerrado_en'] ? date('d/m/Y H:i', strtotime($turno['cerrado_en'])) : 'Activo' ?>
        </small>
      </div>
    </div>

    <!-- Totales -->
    <div class="row g-3 mb-4">
      <div class="col-md-3 col-6">
        <div class="card text-center p-3">
          <div class="text-success fw-bold fs-5"><?= formatMoney($totales['total_ventas']) ?></div>
          <div class="text-muted small">Total ventas</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card text-center p-3">
          <div class="fw-bold fs-5"><?= $totales['num_ventas'] ?></div>
          <div class="text-muted small">Facturas</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card text-center p-3">
          <div class="fw-bold fs-5"><?= formatMoney((float)($turno['monto_esperado'] ?? 0)) ?></div>
          <div class="text-muted small">Monto esperado</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card text-center p-3 <?= $diferencia >= 0 ? 'border-success' : 'border-danger' ?>">
          <div class="fw-bold fs-5 <?= $diferencia >= 0 ? 'text-success' : 'text-danger' ?>">
            <?= ($diferencia >= 0 ? '+' : '') . formatMoney($diferencia) ?>
          </div>
          <div class="text-muted small">Diferencia</div>
        </div>
      </div>
    </div>

    <!-- Ventas del turno -->
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-receipt me-2"></i>Detalle de ventas</span>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-printer me-1"></i>Imprimir
        </button>
      </div>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead><tr><th>#</th><th>Hora</th><th>Cliente</th><th>Tipo pago</th><th class="text-end">Total</th></tr></thead>
          <tbody>
            <?php foreach ($ventas as $v): ?>
            <tr>
              <td><?= $v['id'] ?></td>
              <td><?= date('H:i', strtotime($v['fecha'])) ?></td>
              <td><?= s($v['cliente_nombre'] ?? 'Consumidor final') ?></td>
              <td><span class="badge <?= $v['tipo_pago']==='contado'?'bg-success':'bg-warning text-dark' ?>"><?= s($v['tipo_pago']) ?></span></td>
              <td class="text-end fw-semibold"><?= formatMoney((float)$v['total']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot class="table-light">
            <tr>
              <td colspan="4" class="text-end fw-bold">TOTAL</td>
              <td class="text-end fw-bold text-success"><?= formatMoney($totales['total_ventas']) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="text-center mt-4">
      <a href="<?= $base ?>/turnos" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver a turnos
      </a>
    </div>
  </div>
</div>
