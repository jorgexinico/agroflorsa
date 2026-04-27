<?php // views/producciones/form.php ?>
<div class="card border-warning border-opacity-50">
  <div class="card-header bg-warning bg-opacity-10 text-dark fw-bold"><i class="bi bi-box-seam me-2"></i>Nueva Producción</div>
  <div class="card-body">
    <?php include __DIR__ . '/../templates/alertas.php'; ?>

    <select id="productos-template" class="d-none">
      <?php foreach ($productos as $p): ?>
      <option value="<?= $p->id ?>"
              data-sku="<?= s($p->sku ?? '') ?>"
              data-maneja-vencimiento="<?= (int)$p->maneja_vencimiento ?>"
              data-costo="<?= (float)$p->precio_compra ?>"
              data-pub="<?= (float)$p->precio_publico ?>"
              data-may="<?= (float)$p->precio_mayorista ?>">
        <?= s($p->nombre) ?> (<?= s($p->unidad_abreviatura ?? '') ?>)
      </option>
      <?php endforeach; ?>
    </select>

    <form method="POST" action="">
      <div class="row g-3 mb-4">
        <div class="col-12 col-md-5">
          <label class="form-label fw-semibold">Sucursal de destino (Bodega) <span class="text-danger">*</span></label>
          <select name="sucursal_id" class="form-select" required>
            <option value="">Seleccione...</option>
            <?php foreach ($sucursales as $s): ?>
            <option value="<?= $s->id ?>"><?= s($s->nombre) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Aquí sumará el inventario.</div>
        </div>
        <div class="col-12 col-md-7">
          <label class="form-label fw-semibold">Observación / Nota de Producción</label>
          <input type="text" name="observacion" class="form-control" placeholder="Ej. Lote de producción matutino, encargado: Juan">
        </div>
      </div>

      <div class="card border mb-3">
        <div class="card-header bg-light d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
          <span class="fw-semibold">Productos Fabricados</span>
          <button type="button" id="btn-add-produccion" class="btn btn-sm btn-outline-warning text-dark fw-bold w-100 w-sm-auto">
            <i class="bi bi-plus-circle me-1"></i>Agregar producto
          </button>
        </div>
        <div class="mb-4 bg-light p-3 rounded border mx-3 mt-3">
          <label class="form-label fw-bold text-warning text-darken-2"><i class="bi bi-search me-1"></i>Buscador de Productos Terminados</label>
          <input type="text" id="buscar-producto-produccion" class="form-control form-control-lg border-warning border-2" 
                 placeholder="Escribe el nombre o escanea el SKU del producto fabricado..." list="lista-productos-produccion" autofocus>
          <datalist id="lista-productos-produccion">
            <?php foreach ($productos as $p): ?>
            <option value="<?= s($p->nombre) ?> [SKU: <?= s($p->sku ?? 'S/S') ?>]" data-id="<?= $p->id ?>" data-sku="<?= s($p->sku ?? '') ?>"></option>
            <?php endforeach; ?>
          </datalist>
          <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Añade productos para registrar su ingreso a bodega.</div>
        </div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th style="min-width:200px">Producto Terminado</th>
                <th style="min-width:110px">Cantidad</th>
                <th style="min-width:130px">Costo Prod. U.</th>
                <th style="min-width:110px">Pr. Público</th>
                <th style="min-width:110px">Pr. Mayorista</th>
                <th style="min-width:145px">Fecha venc.</th>
                <th class="text-end" style="min-width:110px">Subtotal</th>
                <th style="width:40px"></th>
              </tr>
            </thead>
            <tbody id="produccion-items"></tbody>
          </table>
        </div>
        <div class="card-footer text-end bg-white">
          <div class="d-flex justify-content-between align-items-center mb-1 d-sm-none">
            <span class="text-muted small">Desliza la tabla para ver más →</span>
          </div>
          <strong class="me-2 fs-5">Costo Total:</strong>
          <span id="produccion-total" class="text-success fw-bold fs-3">Q 0.00</span>
        </div>
      </div>

      <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-4">
        <a href="<?= $base ?>/producciones" class="btn btn-outline-secondary btn-lg py-2 fs-6">Cancelar</a>
        <button type="submit" class="btn btn-warning text-dark fw-bold btn-lg py-2 fs-6 px-sm-5">
          <i class="bi bi-box-seam me-2"></i>Registrar Producción
        </button>
      </div>
    </form>
  </div>
</div>

<script src="<?= asset('build/js/producciones.js') ?>?v=<?= time() ?>"></script>
