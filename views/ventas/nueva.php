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

        <!-- Estilos para la vista móvil híbrida -->
        <style>
          @media (max-width: 768px) {
            .sticky-mobile-bar {
              position: fixed;
              bottom: 0;
              left: 0;
              right: 0;
              background: #fff;
              z-index: 1030;
              box-shadow: 0 -4px 10px rgba(0,0,0,0.15);
              padding: 15px;
            }
            .padding-bottom-mobile {
              padding-bottom: 140px; /* Para que no se oculte detrás del sticky bar */
            }
          }
          .venta-item-row { transition: all 0.2s ease; }
          .venta-item-row:hover { background-color: #f8f9fa; }
        </style>

        <div class="padding-bottom-mobile">
          <!-- Encabezados de PC -->
          <div class="d-none d-md-flex row fw-bold bg-light p-2 border-bottom align-items-center">
            <div class="col-md-5">Producto</div>
            <div class="col-md-2 text-center">Cantidad</div>
            <div class="col-md-2">Precio U.</div>
            <div class="col-md-2 text-end">Subtotal</div>
            <div class="col-md-1"></div>
          </div>

          <!-- Contenedor dinámico -->
          <div id="venta-items" class="d-flex flex-column gap-2 mt-2"></div>
        </div>
      </div>

      <!-- Barra flotante de cobro (sticky en móvil) -->
      <div class="sticky-mobile-bar bg-light p-3 border-top rounded mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
          <div class="w-100 text-center text-md-start">
              <span class="text-muted fw-bold d-block d-md-inline me-md-2">TOTAL ESTIMADO</span>
              <h2 class="mb-0 text-primary fw-bold d-inline" id="venta-total">Q 0.00</h2>
          </div>
          <div class="d-flex w-100 gap-2 justify-content-center justify-content-md-end">
            <button type="button" id="btn-clear-cart-local" class="btn btn-outline-danger d-none flex-grow-1 flex-md-grow-0">
              <i class="bi bi-trash3"></i> Vaciar
            </button>
            <a href="<?= $base ?>/ventas" class="btn btn-outline-secondary flex-grow-1 flex-md-grow-0">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-lg shadow-sm flex-grow-1 flex-md-grow-0 text-nowrap">
              <i class="bi bi-check2-circle me-1"></i>Confirmar Venta
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="<?= asset('build/js/ventas.js') ?>?v=<?= time() ?>"></script>
