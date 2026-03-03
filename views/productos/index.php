<?php // views/productos/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0"><?= count($productos) ?> productos activos</p>
  <div class="d-flex gap-2">
    <a href="/<?= $_ENV['APP_NAME'] ?>/productos/importar" class="btn btn-outline-success btn-sm">
      <i class="bi bi-file-earmark-excel me-1"></i>Importar Excel
    </a>
    <a href="/<?= $_ENV['APP_NAME'] ?>/productos/crear" class="btn btn-success btn-sm">
      <i class="bi bi-plus-circle me-1"></i>Nuevo producto
    </a>
  </div>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>SKU</th><th>Nombre</th><th>Tipo</th><th>Unidad</th><th>Vencim.</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($productos as $p): ?>
        <tr>
          <td class="text-muted small"><?= s($p->sku ?? '—') ?></td>
          <td class="fw-semibold"><?= s($p->nombre) ?></td>
          <td><span class="badge bg-secondary"><?= s($p->tipo) ?></span></td>
          <td><?= s($p->unidad_nombre ?? '') ?> <span class="text-muted">(<?= s($p->unidad_abreviatura ?? '') ?>)</span></td>
          <td><?= $p->maneja_vencimiento ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-dash text-muted"></i>' ?></td>
          <td class="text-end">
            <a href="/<?= $_ENV['APP_NAME'] ?>/productos/editar?id=<?= $p->id ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
            <form method="POST" action="/<?= $_ENV['APP_NAME'] ?>/productos/eliminar" class="d-inline ag-confirm-form">
              <input type="hidden" name="id" value="<?= $p->id ?>">
              <button type="button" class="btn btn-sm btn-outline-danger ag-confirm-btn"
                      data-titulo="¿Desactivar producto?" data-nombre="<?= s($p->nombre) ?>">
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

<script src="<?= asset('build/js/productos.js') ?>"></script>
