<?php // views/usuarios/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="mb-0 text-muted">Gestión de usuarios del sistema.</p>
  <a href="<?= $base ?>/usuarios/crear" class="btn btn-primary btn-sm">
    <i class="bi bi-person-plus me-1"></i>Nuevo Usuario
  </a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>Nombre</th>
          <th>Usuario</th>
          <th>Rol</th>
          <th>Estado</th>
          <th>Creado</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
        <tr class="<?= $u->activo ? '' : 'text-muted' ?>">
          <td><?= $u->id ?></td>
          <td><?= s($u->nombre) ?></td>
          <td><code><?= s($u->usuario) ?></code></td>
          <td>
            <span class="badge bg-<?= $u->rol === 'admin' ? 'danger' : ($u->rol === 'bodeguero' ? 'info text-dark' : 'secondary') ?>">
              <?= s(ucfirst($u->rol)) ?>
            </span>
          </td>
          <td>
            <span class="badge bg-<?= $u->activo ? 'success' : 'secondary' ?>">
              <?= $u->activo ? 'Activo' : 'Inactivo' ?>
            </span>
          </td>
          <td class="small text-muted"><?= $u->creado_en ? date('d/m/Y', strtotime($u->creado_en)) : '—' ?></td>
          <td class="text-end">
            <a href="<?= $base ?>/usuarios/editar?id=<?= $u->id ?>"
               class="btn btn-sm btn-outline-secondary py-0 me-1" title="Editar">
              <i class="bi bi-pencil"></i>
            </a>
            <form method="POST" action="<?= $base ?>/usuarios/toggle" class="d-inline"
                  onsubmit="return confirm('¿<?= $u->activo ? 'Desactivar' : 'Activar' ?> al usuario <?= s($u->nombre) ?>?')">
              <input type="hidden" name="id" value="<?= $u->id ?>">
              <button type="submit" class="btn btn-sm py-0 <?= $u->activo ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                      title="<?= $u->activo ? 'Desactivar' : 'Activar' ?>">
                <i class="bi bi-<?= $u->activo ? 'person-dash' : 'person-check' ?>"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($usuarios)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No hay usuarios registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
