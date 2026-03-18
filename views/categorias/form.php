<?php // views/categorias/form.php
$action = $accion === 'crear' 
    ? '/' . $_ENV['APP_NAME'] . '/categorias/crear' 
    : '/' . $_ENV['APP_NAME'] . '/categorias/editar?id=' . $categoria->id; 
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-tags-fill me-2"></i><?= s($titulo) ?></div>
      <div class="card-body">
        <?php include __DIR__ . '/../templates/alertas.php'; ?>
        <form method="POST" action="<?= $action ?>">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control" value="<?= s($categoria->nombre) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"><?= s($categoria->descripcion ?? '') ?></textarea>
          </div>
          <div class="d-flex gap-2 justify-content-end mt-4">
            <a href="/<?= $_ENV['APP_NAME'] ?>/categorias" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
