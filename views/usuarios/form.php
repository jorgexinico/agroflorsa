<?php // views/usuarios/form.php ?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-person-badge me-2 text-primary"></i>
        <?= s($titulo) ?>
      </div>
      <div class="card-body">
        <?php include __DIR__ . '/../templates/alertas.php'; ?>

        <form method="POST" action="">
          <?php if ($accion === 'editar'): ?>
          <input type="hidden" name="id" value="<?= $usuario->id ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control"
                   value="<?= s($usuario->nombre) ?>" placeholder="Ej: Juan Pérez" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Nombre de usuario <span class="text-danger">*</span></label>
            <input type="text" name="usuario" class="form-control"
                   value="<?= s($usuario->usuario) ?>" placeholder="Ej: jperez"
                   autocomplete="off" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
            <select name="rol" class="form-select">
              <option value="vendedor"  <?= $usuario->rol === 'vendedor'  ? 'selected' : '' ?>>Vendedor</option>
              <option value="bodeguero" <?= $usuario->rol === 'bodeguero' ? 'selected' : '' ?>>Bodeguero</option>
              <option value="admin"     <?= $usuario->rol === 'admin'     ? 'selected' : '' ?>>Administrador</option>
            </select>
          </div>

          <hr class="my-3">
          <p class="text-muted small mb-2">
            <?= $accion === 'editar' ? 'Deja la contraseña en blanco para no cambiarla.' : 'Contraseña para el nuevo usuario.' ?>
          </p>

          <div class="mb-3">
            <label class="form-label fw-semibold">
              Contraseña <?= $accion === 'crear' ? '<span class="text-danger">*</span>' : '' ?>
            </label>
            <input type="password" name="password" class="form-control"
                   autocomplete="new-password"
                   <?= $accion === 'crear' ? 'required' : '' ?>>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold">
              Confirmar contraseña <?= $accion === 'crear' ? '<span class="text-danger">*</span>' : '' ?>
            </label>
            <input type="password" name="password_confirm" class="form-control"
                   autocomplete="new-password"
                   <?= $accion === 'crear' ? 'required' : '' ?>>
          </div>

          <div class="d-flex gap-2 justify-content-end">
            <a href="/<?= $_ENV['APP_NAME'] ?>/usuarios" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary px-4">
              <i class="bi bi-check-circle me-1"></i>
              <?= $accion === 'crear' ? 'Crear usuario' : 'Guardar cambios' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
