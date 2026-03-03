<?php // views/sucursales/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0"><?= count($sucursales) ?> sucursales registradas</p>
  <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
  <a href="/<?= $_ENV['APP_NAME'] ?>/sucursales/crear" class="btn btn-success btn-sm">
    <i class="bi bi-plus-circle me-1"></i>Nueva sucursal
  </a>
  <?php endif; ?>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Nombre</th><th>Tipo</th><th>Dirección</th><th>Estado</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($sucursales as $s): ?>
        <tr>
          <td><?= $s->id ?></td>
          <td class="fw-semibold"><?= s($s->nombre) ?></td>
          <td><span class="badge <?= $s->tipo==='agroservicio'?'bg-success':'bg-info' ?>"><?= s($s->tipo) ?></span></td>
          <td class="text-muted"><?= s($s->direccion ?? '—') ?></td>
          <td><?= $s->activa ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-secondary">Inactiva</span>' ?></td>
          <td class="text-end">
            <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
            <a href="/<?= $_ENV['APP_NAME'] ?>/sucursales/editar?id=<?= $s->id ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
            <form method="POST" action="/<?= $_ENV['APP_NAME'] ?>/sucursales/eliminar" class="d-inline ag-confirm-form">
              <input type="hidden" name="id" value="<?= $s->id ?>">
              <button type="button" class="btn btn-sm btn-outline-danger ag-confirm-btn"
                      data-titulo="¿Desactivar sucursal?" data-nombre="<?= s($s->nombre) ?>">
                <i class="bi bi-power"></i>
              </button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
