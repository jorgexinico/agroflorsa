<?php // views/inventario/ajuste.php ?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-pencil-square me-2"></i>Ajuste Manual de Inventario</div>
      <div class="card-body">
        <div class="alert alert-info small">
          <i class="bi bi-info-circle me-1"></i>
          Ingresa una cantidad <strong>positiva</strong> para agregar stock, o
          <strong>negativa</strong> para descontar.
        </div>
        <?php include __DIR__ . '/../templates/alertas.php'; ?>
        <form method="POST" action="">
          <div class="mb-3">
            <label class="form-label fw-semibold">Sucursal</label>
            <select name="sucursal_id" class="form-select" required>
              <option value="">Seleccione...</option>
              <?php foreach ($sucursales as $s): ?>
              <option value="<?= $s->id ?>"><?= s($s->nombre) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Producto</label>
            <select name="producto_id" class="form-select" required>
              <option value="">Seleccione...</option>
              <?php foreach ($productos as $p): ?>
              <option value="<?= $p->id ?>"><?= s($p->nombre) ?> (<?= s($p->unidad_abreviatura ?? '') ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Cantidad (positiva o negativa)</label>
            <input type="number" name="cantidad" class="form-control" step="0.001" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Motivo</label>
            <input type="text" name="motivo" class="form-control" placeholder="Ej: Pérdida, corrección de conteo...">
          </div>
          <div class="d-flex gap-2 justify-content-end mt-4">
            <a href="/<?= $_ENV['APP_NAME'] ?>/inventario" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-warning"><i class="bi bi-check-circle me-1"></i>Aplicar ajuste</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
