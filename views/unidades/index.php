<?php // views/unidades/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0"><?= count($unidades) ?> unidades</p>
  <a href="/<?= $_ENV['APP_NAME'] ?>/unidades/crear" class="btn btn-success btn-sm">
    <i class="bi bi-plus-circle me-1"></i>Nueva unidad
  </a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Nombre</th><th>Abreviatura</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($unidades as $u): ?>
        <tr>
          <td class="fw-semibold"><?= s($u->nombre) ?></td>
          <td><span class="badge bg-secondary"><?= s($u->abreviatura) ?></span></td>
          <td class="text-end">
            <a href="/<?= $_ENV['APP_NAME'] ?>/unidades/editar?id=<?= $u->id ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
