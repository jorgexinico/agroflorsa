<?php // views/marcas/form.php
$action = $accion === 'crear' 
    ? '/' . $_ENV['APP_NAME'] . '/marcas/crear' 
    : '/' . $_ENV['APP_NAME'] . '/marcas/editar?id=' . $marca->id; 
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-tag-fill me-2"></i><?= s($titulo) ?></div>
      <div class="card-body">
        <?php include __DIR__ . '/../templates/alertas.php'; ?>
        <form method="POST" action="<?= $action ?>">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control" value="<?= s($marca->nombre) ?>" required>
          </div>
          <div class="d-flex gap-2 justify-content-end mt-4">
            <a href="<?= $base ?>/marcas" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
