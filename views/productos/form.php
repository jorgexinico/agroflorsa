<?php // views/productos/form.php
$action = $accion === 'crear'
    ? '/' . $_ENV['APP_NAME'] . '/productos/crear'
    : '/' . $_ENV['APP_NAME'] . '/productos/editar?id=' . $producto->id;
?>
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header"><i class="bi bi-box-seam me-2"></i><?= s($titulo) ?></div>
      <div class="card-body">
        <?php include __DIR__ . '/../templates/alertas.php'; ?>
        <form method="POST" action="<?= $action ?>">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
              <input type="text" name="nombre" class="form-control" value="<?= s($producto->nombre) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">SKU</label>
              <input type="text" name="sku" class="form-control" value="<?= s($producto->sku ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Unidad de Medida <span class="text-danger">*</span></label>
              <select name="unidad_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach ($unidades as $u): ?>
                <option value="<?= $u->id ?>" <?= $producto->unidad_id == $u->id ? 'selected' : '' ?>>
                  <?= s($u->nombre) ?> (<?= s($u->abreviatura) ?>)
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tipo</label>
              <select name="tipo" class="form-select">
                <option value="mercaderia" <?= $producto->tipo==='mercaderia'?'selected':'' ?>>Mercadería</option>
                <option value="materia_prima" <?= $producto->tipo==='materia_prima'?'selected':'' ?>>Materia Prima</option>
                <option value="terminado" <?= $producto->tipo==='terminado'?'selected':'' ?>>Terminado</option>
              </select>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="maneja_vencimiento"
                       id="maneja_vencimiento" value="1"
                       <?= $producto->maneja_vencimiento ? 'checked' : '' ?>>
                <label class="form-check-label" for="maneja_vencimiento">
                  Maneja fecha de vencimiento (FEFO)
                </label>
              </div>
            </div>
          </div>
          <div class="d-flex gap-2 justify-content-end mt-4">
            <a href="/<?= $_ENV['APP_NAME'] ?>/productos" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
