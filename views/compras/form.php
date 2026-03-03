<?php // views/compras/form.php — Nueva compra con tabla dinámica ?>
<div class="card">
  <div class="card-header"><i class="bi bi-truck me-2"></i>Nueva Compra</div>
  <div class="card-body">
    <?php include __DIR__ . '/../templates/alertas.php'; ?>

    <!-- Hidden template para JS -->
    <select id="productos-template" class="d-none">
      <?php foreach ($productos as $p): ?>
      <option value="<?= $p->id ?>" data-sku="<?= s($p->sku ?? '') ?>">
        <?= s($p->nombre) ?> (<?= s($p->unidad_abreviatura ?? '') ?>)
      </option>
      <?php endforeach; ?>
    </select>

    <form method="POST" action="">
      <div class="row g-3 mb-4">
        <div class="col-md-5">
          <label class="form-label fw-semibold">Sucursal de destino <span class="text-danger">*</span></label>
          <select name="sucursal_id" class="form-select" required>
            <option value="">Seleccione...</option>
            <?php foreach ($sucursales as $s): ?>
            <option value="<?= $s->id ?>"><?= s($s->nombre) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-5">
          <label class="form-label fw-semibold">Proveedor</label>
          <select name="proveedor_id" class="form-select">
            <option value="">Sin proveedor</option>
            <?php foreach ($proveedores as $p): ?>
            <option value="<?= $p->id ?>"><?= s($p->nombre) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-12">
          <label class="form-label fw-semibold">Observación</label>
          <input type="text" name="observacion" class="form-control" placeholder="Opcional">
        </div>
      </div>

      <!-- Tabla de products -->
      <div class="card border mb-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <span class="fw-semibold">Detalle de la compra</span>
          <button type="button" id="btn-add-compra" class="btn btn-sm btn-outline-success">
            <i class="bi bi-plus-circle me-1"></i>Agregar producto
          </button>
        </div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th style="min-width:200px">Producto</th>
                <th style="width:110px">Cantidad</th>
                <th style="width:130px">Costo unit.</th>
                <th class="text-end" style="width:110px">Subtotal</th>
                <th style="width:50px"></th>
              </tr>
            </thead>
            <tbody id="compra-items"></tbody>
          </table>
        </div>
        <div class="card-footer text-end">
          <strong>Total: <span id="compra-total" class="text-success fs-5">Q 0.00</span></strong>
        </div>
      </div>

      <div class="d-flex gap-2 justify-content-end">
        <a href="/<?= $_ENV['APP_NAME'] ?>/compras" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Registrar compra</button>
      </div>
    </form>
  </div>
</div>
