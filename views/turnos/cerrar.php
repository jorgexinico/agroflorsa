<?php // views/turnos/cerrar.php
$monto_esperado = (float)$turno->monto_inicial + $totales['total_efectivo'];
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header text-danger"><i class="bi bi-stop-circle me-2"></i>Cerrar Turno #<?= $turno->id ?></div>
      <div class="card-body">
        <div class="bg-light rounded p-3 mb-4">
          <div class="row text-center g-3">
            <div class="col-6">
              <div class="text-success fw-bold fs-5"><?= formatMoney($totales['total_ventas']) ?></div>
              <div class="text-muted small">Total vendido</div>
            </div>
            <div class="col-6">
              <div class="fw-bold fs-5"><?= formatMoney($totales['total_efectivo']) ?></div>
              <div class="text-muted small">Efectivo recibido</div>
            </div>
            <div class="col-6">
              <div class="fw-bold fs-5"><?= formatMoney((float)$turno->monto_inicial) ?></div>
              <div class="text-muted small">Monto inicial</div>
            </div>
            <div class="col-6">
              <div class="text-primary fw-bold fs-5"><?= formatMoney($monto_esperado) ?></div>
              <div class="text-muted small">Monto esperado en caja</div>
            </div>
          </div>
        </div>

        <form method="POST" action="<?= $base ?>/turnos/cerrar">
          <input type="hidden" name="id" value="<?= $turno->id ?>">
          <div class="mb-3">
            <label class="form-label fw-semibold">Monto entregado (Q) <span class="text-danger">*</span></label>
            <input type="number" name="monto_entregado" class="form-control form-control-lg"
                   step="0.01" min="0" value="<?= number_format($monto_esperado, 2, '.', '') ?>" required>
            <div class="form-text">¿Cuánto efectivo entregas al cerrar?</div>
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">Nota / Observación</label>
            <input type="text" name="nota" class="form-control" placeholder="Opcional">
          </div>
          <button type="button" class="btn btn-danger w-100 py-2 fw-semibold" id="ag-cerrar-turno-btn">
            <i class="bi bi-stop-circle me-2"></i>Confirmar cierre de turno
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/turnos.js') ?>"></script>
