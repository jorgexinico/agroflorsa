<?php // views/ventas/nueva.php — Formulario de nueva venta con tabla dinámica ?>
<?php if (empty($_SESSION['turno_id'])): ?>
<div class="alert alert-danger">
  <i class="bi bi-exclamation-triangle me-2"></i>No hay turno abierto.
  <a href="<?= $base ?>/turnos/abrir" class="alert-link">Abre un turno primero.</a>
</div>
<?php return; endif; ?>

<div class="card">
  <div class="card-header"><i class="bi bi-cart-plus me-2 text-success"></i>Nueva Venta</div>
  <div class="card-body">
    <?php include __DIR__ . '/../templates/alertas.php'; ?>

    <div class="row align-items-center mb-3">
      <div class="col-md-6">
        <h1 class="h3 mb-0">Nueva Venta</h1>
      </div>
      <?php if (!empty($sucursales)): ?>
      <div class="col-md-6 text-md-end">
        <div class="d-inline-flex align-items-center gap-2">
          <label class="small fw-bold text-muted text-nowrap mb-0">Vender desde:</label>
          <select class="form-select form-select-sm" style="width: 200px;" onchange="window.location.href='?sucursal_id='+this.value">
            <?php foreach ($sucursales as $suc): ?>
            <option value="<?= $suc->id ?>" <?= $suc->id == $sucursal_id ? 'selected' : '' ?>>
              <?= s($suc->nombre) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <template id="productos-data" data-productos='<?= json_encode($productos) ?>'></template>

    <!-- Hidden select template para JS -->
    <select id="productos-template" class="d-none">
      <?php foreach ($productos as $p): ?>
      <option value="<?= $p['id'] ?>"
              data-precio-publico="<?= (float)$p['precio_publico'] ?>"
              data-precio-mayorista="<?= (float)$p['precio_mayorista'] ?>"
              data-sku="<?= s($p['sku'] ?? '') ?>"
              data-stock="<?= (float)$p['stock'] ?>"
              data-maneja-vencimiento="<?= (int)$p['maneja_vencimiento'] ?>">
        <?= s($p['nombre']) ?> (<?= s($p['unidad_abreviatura'] ?? '') ?>) — Stock: <?= (float)$p['stock'] ?>
      </option>
      <?php endforeach; ?>
    </select>

    <form method="POST" action="">
      <div class="row g-3 mb-4">
        <div class="col-12 col-md-5">
          <label class="form-label fw-semibold">Cliente</label>
          <select name="cliente_id" class="form-select">
            <option value="">Consumidor final</option>
            <?php foreach ($clientes as $c): ?>
            <option value="<?= $c->id ?>"><?= s($c->nombre) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Radios / Select para Tipo de Precio -->
        <div class="col-12 col-md-3">
          <label class="form-label fw-semibold">Tipo de Precio</label>
          <select id="tipo-precio" class="form-select text-primary fw-bold">
            <option value="publico">Precio Público</option>
            <option value="mayorista">Precio Mayorista</option>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label fw-semibold">Tipo pago</label>
          <select name="tipo_pago" id="tipo-pago" class="form-select">
            <option value="contado">Contado</option>
            <option value="credito">Crédito</option>
          </select>
        </div>
        <div class="col-12 col-md-2" id="metodo-pago-wrap">
          <label class="form-label fw-semibold">Método de pago</label>
          <select name="metodo_pago" class="form-select">
            <option value="efectivo">Efectivo</option>
            <option value="transferencia">Transferencia</option>
            <option value="tarjeta">Tarjeta</option>
            <option value="otro">Otro</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Observación</label>
          <input type="text" name="observacion" class="form-control" placeholder="Opcional">
        </div>
      </div>

      <!-- Tabla de productos -->
      <div class="card border mb-3">
        <div class="card-header bg-light d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
          <span class="fw-semibold">Productos</span>
          <button type="button" id="btn-add-item" class="btn btn-sm btn-outline-success w-100 w-sm-auto">
            <i class="bi bi-plus-circle me-1"></i>Agregar producto
          </button>
        </div>
        <div class="mb-4 bg-light p-3 rounded border">
          <label class="form-label fw-bold text-success"><i class="bi bi-search me-1"></i>Buscador de Productos (Nombre o SKU)</label>
          <input type="text" id="buscar-producto-venta" class="form-control form-control-lg border-success border-2" 
                 placeholder="Escribe el nombre o escanea el SKU del producto..." list="lista-productos-venta" autofocus>
          <datalist id="lista-productos-venta">
            <?php foreach ($productos as $p): ?>
            <option value="<?= s($p['nombre']) ?> [SKU: <?= s($p['sku'] ?? 'S/S') ?>]" data-id="<?= $p['id'] ?>" data-sku="<?= s($p['sku'] ?? '') ?>"></option>
            <?php endforeach; ?>
          </datalist>
          <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Al seleccionar un producto, se añadirá automáticamente a la lista de abajo.</div>
        </div>

        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th style="min-width:200px">Producto</th>
                <th style="min-width:100px">Cantidad</th>
                <th style="min-width:110px">Precio U.</th>
                <th class="text-end" style="min-width:110px">Subtotal</th>
                <th style="width:40px"></th>
              </tr>
            </thead>
            <tbody id="venta-items"></tbody>
          </table>
        </div>
    <tfoot>
      <tr class="bg-light fw-bold">
        <td colspan="3" class="text-end py-3">TOTAL ESTIMADO</td>
        <td class="text-end py-3 text-primary fs-4" id="venta-total">Q 0.00</td>
        <td></td>
      </tr>
    </tfoot>
  </table>
</div>

<div class="row mt-4">
  <div class="col-12 text-end d-flex justify-content-between align-items-center">
    <button type="button" id="btn-clear-cart-local" class="btn btn-outline-danger d-none">
      <i class="bi bi-trash3 me-1"></i>Vaciar Carrito
    </button>
    <div class="ms-auto">
      <a href="<?= $base ?>/ventas" class="btn btn-outline-secondary me-2">Cancelar</a>
      <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
        <i class="bi bi-check2-circle me-1"></i>Confirmar Venta
      </button>
    </div>
  </div>
</div>
    </form>
  </div>
</div>

<script src="<?= asset('build/js/ventas.js') ?>?v=<?= time() ?>"></script>
