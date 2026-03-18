<?php // views/ventas/nueva.php — Formulario de nueva venta con tabla dinámica ?>
<?php if (empty($_SESSION['turno_id'])): ?>
<div class="alert alert-danger">
  <i class="bi bi-exclamation-triangle me-2"></i>No hay turno abierto.
  <a href="/<?= $_ENV['APP_NAME'] ?>/turnos/abrir" class="alert-link">Abre un turno primero.</a>
</div>
<?php return; endif; ?>

<div class="card">
  <div class="card-header"><i class="bi bi-cart-plus me-2 text-success"></i>Nueva Venta</div>
  <div class="card-body">
    <?php include __DIR__ . '/../templates/alertas.php'; ?>

    <!-- Hidden select template para JS -->
    <select id="productos-template" class="d-none">
      <?php foreach ($productos as $p): ?>
      <option value="<?= $p->id ?>"
              data-precio-publico="<?= (float)$p->precio_publico ?>"
              data-precio-mayorista="<?= (float)$p->precio_mayorista ?>"
              data-sku="<?= s($p->sku ?? '') ?>"
              data-maneja-vencimiento="<?= (int)$p->maneja_vencimiento ?>">
        <?= s($p->nombre) ?> (<?= s($p->unidad_abreviatura ?? '') ?>)
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
        <div class="card-footer text-end bg-white">
          <div class="d-flex justify-content-between align-items-center mb-1 d-sm-none">
            <span class="text-muted small">Desliza la tabla para ver más →</span>
          </div>
          <strong class="me-2 fs-5">Total:</strong>
          <span id="venta-total" class="text-success fw-bold fs-3">Q 0.00</span>
        </div>
      </div>

      <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-4">
        <a href="/<?= $_ENV['APP_NAME'] ?>/ventas" class="btn btn-outline-secondary btn-lg py-2 fs-6">Cancelar</a>
        <button type="submit" class="btn btn-success btn-lg py-2 fs-6 px-sm-5">
          <i class="bi bi-check-circle me-2"></i>Confirmar venta
        </button>
      </div>
    </form>
  </div>
</div>

<script src="<?= asset('build/js/ventas.js') ?>"></script>
