<?php // views/clientes/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0"><?= count($clientes) ?> clientes</p>
  <a href="/<?= $_ENV['APP_NAME'] ?>/clientes/crear" class="btn btn-success btn-sm">
    <i class="bi bi-plus-circle me-1"></i>Nuevo cliente
  </a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Nombre</th><th>NIT</th><th>Teléfono</th><th>Estado</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($clientes as $c): ?>
        <tr>
          <td class="fw-semibold"><?= s($c->nombre) ?></td>
          <td class="text-muted"><?= s($c->nit ?? '—') ?></td>
          <td><?= s($c->telefono ?? '—') ?></td>
          <td><?= $c->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?></td>
          <td class="text-end">
            <a href="/<?= $_ENV['APP_NAME'] ?>/clientes/editar?id=<?= $c->id ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
            <form method="POST" action="/<?= $_ENV['APP_NAME'] ?>/clientes/eliminar" class="d-inline ag-confirm-form">
              <input type="hidden" name="id" value="<?= $c->id ?>">
              <button type="button" class="btn btn-sm btn-outline-danger ag-confirm-btn"
                      data-titulo="¿Desactivar cliente?" data-nombre="<?= s($c->nombre) ?>">
                <i class="bi bi-power"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
