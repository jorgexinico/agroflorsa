<?php // views/sucursales/form.php
$action = $accion === 'crear'
    ? "$base/sucursales/crear"
    : "$base/sucursales/editar?id=" . $sucursal->id;
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-shop-window me-2"></i><?= s($titulo) ?></div>
      <div class="card-body">
        <?php include __DIR__ . '/../templates/alertas.php'; ?>
        <form method="POST" action="<?= $action ?>">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control" value="<?= s($sucursal->nombre) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Tipo</label>
            <select name="tipo" class="form-select">
              <option value="agroservicio" <?= $sucursal->tipo==='agroservicio'?'selected':'' ?>>Agroservicio</option>
              <option value="bodega_central" <?= $sucursal->tipo==='bodega_central'?'selected':'' ?>>Bodega Central</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Dirección</label>
            <input type="text" name="direccion" class="form-control" value="<?= s($sucursal->direccion ?? '') ?>">
          </div>
          <div class="d-flex gap-2 justify-content-end mt-4">
            <a href="<?= $base ?>/sucursales" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
