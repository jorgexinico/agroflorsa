<?php // views/inventario/ajuste.php ?>
<div class="row justify-content-center">
  <div class="col-md-11">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <h5 class="mb-0 fw-bold text-dark">
          <i class="bi bi-pencil-square me-2 text-warning"></i>Ajuste / Carga Masiva de Productos
        </h5>
        <button type="button" id="btn-load-all" class="btn btn-outline-primary btn-sm rounded-pill px-3">
          <i class="bi bi-box-seam me-1"></i>Cargar todos los productos activos
        </button>
      </div>
      <div class="card-body p-4">
        <div class="alert alert-light border border-info mb-4" style="border-left-width: 4px !important;">
          <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
            <div>
              <p class="mb-0 small fw-medium">Usa cantidades <strong>positivas</strong> para ingresar existencias y <strong>negativas</strong> para mermas o fugas.</p>
              <p class="mb-0 small text-muted">También puedes actualizar el costo de compra y los precios de venta (público/mayorista) para cada producto.</p>
            </div>
          </div>
        </div>

        <?php include __DIR__ . '/../templates/alertas.php'; ?>

        <!-- Hidden select template para JS -->
        <select id="productos-template" class="d-none">
          <option value="">Seleccione...</option>
          <?php foreach ($productos as $p): ?>
          <option value="<?= $p->id ?>"
                  data-precio-publico="<?= (float)$p->precio_publico ?>"
                  data-precio-mayorista="<?= (float)$p->precio_mayorista ?>"
                  data-maneja-vencimiento="<?= $p->maneja_vencimiento ?? '0' ?>">
            <?= s($p->nombre) ?> (<?= s($p->unidad_abreviatura ?? '') ?>)
          </option>
          <?php endforeach; ?>
        </select>

        <form method="POST" action="" id="form-ajuste">
          <div class="row g-3 mb-4">
            <div class="col-md-5">
              <label class="form-label fw-bold text-secondary small text-uppercase">Sucursal a Cargar</label>
              <select name="sucursal_id" class="form-select border-2" required>
                <option value="">Seleccione sucursal destino...</option>
                <?php foreach ($sucursales as $s): ?>
                <option value="<?= $s->id ?>"><?= s($s->nombre) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-7">
              <label class="form-label fw-bold text-secondary small text-uppercase">Motivo del Movimiento</label>
              <input type="text" name="motivo" class="form-control border-2" placeholder="Ej: Carga inicial de inventario, Auditoría anual..." required>
            </div>
          </div>

          <div class="bg-light p-3 rounded-3 mb-3 border">
            <div class="row align-items-end g-3">
              <div class="col-md-9 col-lg-10">
                <label class="form-label fw-bold text-secondary small text-uppercase">Buscar Producto</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                  <input type="text" id="search-producto" class="form-control border-start-0 ps-0" placeholder="Escribe el nombre del producto para filtrar o encontrar...">
                </div>
              </div>
              <div class="col-md-3 col-lg-2">
                <button type="button" id="btn-add-item" class="btn btn-dark w-100">
                  <i class="bi bi-plus-lg me-1"></i>Agregar Fila
                </button>
              </div>
            </div>
          </div>

          <div class="table-responsive rounded-3 border">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-dark">
                <tr>
                  <th style="min-width: 300px;">Producto</th>
                  <th style="width: 140px;" class="text-center">Cantidad</th>
                  <th style="width: 140px;" class="text-center">Vencimiento</th>
                  <th style="width: 140px;" class="text-center">Costo (Compra)</th>
                  <th style="width: 140px;" class="text-center">Pr. Público</th>
                  <th style="width: 140px;" class="text-center">Pr. Mayorista</th>
                  <th style="width: 50px;"></th>
                </tr>
              </thead>
              <tbody id="ajuste-items">
                <!-- JS se encarga de llenar esto -->
              </tbody>
            </table>
          </div>

          <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-5">
            <a href="<?= $base ?>/inventario" class="btn btn-outline-secondary px-4 py-2">
              <i class="bi bi-x-circle me-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-warning px-5 py-2 fw-bold shadow-sm">
              <i class="bi bi-check-all fs-5 me-1"></i>PROCESAR AJUSTE MASIVO
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/inventario.js') ?>"></script>
