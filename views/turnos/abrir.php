<?php // views/turnos/abrir.php ?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card">
      <div class="card-header"><i class="bi bi-play-circle me-2 text-success"></i>Abrir Turno</div>
      <div class="card-body">
        <?php include __DIR__ . '/../templates/alertas.php'; ?>
        <form method="POST" action="">
          <div class="mb-3">
            <label class="form-label fw-semibold">Sucursal <span class="text-danger">*</span></label>
            <select name="sucursal_id" class="form-select" required>
              <option value="">Seleccione sucursal...</option>
              <?php foreach ($sucursales as $s): ?>
              <option value="<?= $s->id ?>"><?= s($s->nombre) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">Monto inicial de caja (Q)</label>
            <input type="number" name="monto_inicial" class="form-control" step="0.01" min="0" value="0">
            <div class="form-text">Efectivo con el que inicia la caja.</div>
          </div>
          <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
            <i class="bi bi-play-fill me-2"></i>Abrir turno
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
