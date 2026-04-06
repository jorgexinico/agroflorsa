<?php // views/clientes/form.php
$action = $accion === 'crear'
    ? '/' . $_ENV['APP_NAME'] . '/clientes/crear'
    : '/' . $_ENV['APP_NAME'] . '/clientes/editar?id=' . $cliente->id;
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-person me-2"></i><?= s($titulo) ?></div>
      <div class="card-body">
        <?php include __DIR__ . '/../templates/alertas.php'; ?>
        <form method="POST" action="<?= $action ?>" id="form-cliente">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control" value="<?= s($cliente->nombre) ?>" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">NIT</label>
              <input type="text" name="nit" class="form-control" value="<?= s($cliente->nit ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Teléfono</label>
              <input type="text" name="telefono" class="form-control" value="<?= s($cliente->telefono ?? '') ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Dirección</label>
            <input type="text" name="direccion" class="form-control" value="<?= s($cliente->direccion ?? '') ?>">
          </div>
          <div class="d-flex gap-2 justify-content-end mt-4">
            <a href="<?= $base ?>/clientes" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/clientes.js') ?>"></script>
