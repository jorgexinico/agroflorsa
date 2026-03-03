<?php // views/inventario/index.php ?>
<div class="row mb-3">
  <div class="col-md-5">
    <form method="GET" action="" class="d-flex gap-2">
      <select name="sucursal_id" class="form-select">
        <option value="">Seleccionar sucursal...</option>
        <?php foreach ($sucursales as $s): ?>
        <option value="<?= $s->id ?>" <?= $sucursalId == $s->id ? 'selected' : '' ?>><?= s($s->nombre) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-outline-success">Ver</button>
    </form>
  </div>
  <div class="col-auto ms-auto">
    <a href="/<?= $_ENV['APP_NAME'] ?>/inventario/ajuste" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-pencil-square me-1"></i>Ajuste manual
    </a>
    <a href="/<?= $_ENV['APP_NAME'] ?>/compras/crear" class="btn btn-success btn-sm ms-2">
      <i class="bi bi-truck me-1"></i>Registrar compra
    </a>
  </div>
</div>

<?php if ($sucursalId && !empty($stock)): ?>
<div class="card">
  <div class="card-header d-flex justify-content-between">
    <span><i class="bi bi-boxes me-2"></i>Stock de sucursal</span>
    <span class="text-muted small"><?= count($stock) ?> productos</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>SKU</th><th>Producto</th><th>Unidad</th><th class="text-end">Cantidad</th></tr></thead>
      <tbody>
        <?php foreach ($stock as $item): ?>
        <tr class="<?= (float)$item['cantidad'] <= 0 ? 'table-danger' : '' ?>">
          <td class="text-muted small"><?= s($item['sku'] ?? '—') ?></td>
          <td class="fw-semibold"><?= s($item['producto_nombre']) ?></td>
          <td><?= s($item['unidad_abreviatura']) ?></td>
          <td class="text-end fw-bold <?= (float)$item['cantidad'] <= 0 ? 'text-danger' : 'text-success' ?>">
            <?= number_format((float)$item['cantidad'], 3) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php elseif ($sucursalId): ?>
<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No hay stock registrado para esta sucursal.</div>
<?php else: ?>
<div class="alert alert-secondary text-center py-5">
  <i class="bi bi-boxes fs-1 d-block mb-2"></i>
  Selecciona una sucursal para ver el inventario.
</div>
<?php endif; ?>
