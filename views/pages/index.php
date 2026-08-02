<?php
// views/pages/index.php — Dashboard
// Las variables vienen ya resueltas por AppController::index()
?>

<div class="row g-3 mb-4">
  <!-- Ventas hoy -->
  <div class="col-sm-6 col-xl">
    <div class="stat-card h-100">
      <div class="stat-card__icon bg-success bg-opacity-10">
        <i class="bi bi-cash-stack text-success"></i>
      </div>
      <div>
        <div class="stat-card__value text-success"><?= formatMoney((float)$ventasHoy['monto']) ?></div>
        <div class="stat-card__label">
          Ventas hoy (<?= $ventasHoy['cantidad'] ?>)
          <?php if ($esAdmin): ?><span class="badge bg-secondary ms-1" style="font-size:.65rem">global</span><?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Turno activo -->
  <div class="col-sm-6 col-xl">
    <div class="stat-card h-100">
      <div class="stat-card__icon bg-primary bg-opacity-10">
        <i class="bi bi-clock-history text-primary"></i>
      </div>
      <div>
        <div class="stat-card__value" style="font-size:1.1rem">
          <?= $turnoActivo ? '<span class="text-success">Abierto</span>' : '<span class="text-secondary">Sin turno</span>' ?>
        </div>
        <div class="stat-card__label">
          <?= $turnoActivo ? s($turnoActivo['sucursal_nombre']) : ($esAdmin ? 'Vista Admin' : 'No hay turno') ?>
        </div>
      </div>
    </div>
  </div>

  <!-- CxC pendiente -->
  <div class="col-sm-6 col-xl">
    <div class="stat-card h-100">
      <div class="stat-card__icon bg-danger bg-opacity-10">
        <i class="bi bi-file-earmark-text text-danger"></i>
      </div>
      <div>
        <div class="stat-card__value text-danger"><?= formatMoney($cxcPendiente) ?></div>
        <div class="stat-card__label">
          Saldo CxC
          <a href="<?= $base ?>/cuentas-cobrar" class="d-block small text-decoration-none mt-1">Ver cuentas</a>
        </div>
      </div>
    </div>
  </div>

  <!-- CxP pendiente -->
  <div class="col-sm-6 col-xl">
    <div class="stat-card h-100">
      <div class="stat-card__icon bg-warning bg-opacity-10">
        <i class="bi bi-wallet2 text-warning"></i>
      </div>
      <div>
        <div class="stat-card__value text-warning"><?= formatMoney($cxpPendiente) ?></div>
        <div class="stat-card__label">
          Deuda CxP
          <a href="<?= $base ?>/cuentas-pagar" class="d-block small text-decoration-none mt-1 text-warning">Ver cuentas</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Acceso rápido -->
  <div class="col-sm-6 col-xl">
    <div class="stat-card h-100">
      <div class="stat-card__icon bg-info bg-opacity-10">
        <i class="bi bi-lightning-charge text-info"></i>
      </div>
      <div>
        <div class="stat-card__label fw-semibold mb-2">Acceso rápido</div>
        <?php if ($turnoActivo): ?>
          <a href="<?= $base ?>/ventas/nueva" class="btn btn-sm btn-success">
            <i class="bi bi-cart-plus me-1"></i>Vender
          </a>
        <?php else: ?>
          <a href="<?= $base ?>/turnos/abrir" class="btn btn-sm btn-outline-success">
            <i class="bi bi-play-circle me-1"></i><?= $esAdmin ? 'Turnos' : 'Abrir turno' ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Últimas Ventas -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-receipt me-2 text-success"></i>Últimas ventas<?= $esAdmin ? ' (hoy — todas las sucursales)' : '' ?></span>
    <a href="<?= $base ?>/ventas" class="btn btn-sm btn-outline-secondary">Ver todas</a>
  </div>
  <div class="card-body p-0">
    <?php if (empty($ultimasVentas)): ?>
      <p class="text-center text-muted py-4">No hay ventas registradas hoy.</p>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>#</th><th>Cliente</th>
            <?php if ($esAdmin): ?><th>Sucursal</th><?php endif; ?>
            <th>Hora</th><th>Tipo</th><th class="text-end">Total</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ultimasVentas as $v): ?>
          <tr>
            <td><span class="badge bg-light text-dark border"><?= $v['id'] ?></span></td>
            <td><?= s($v['cliente'] ?? 'Consumidor final') ?></td>
            <?php if ($esAdmin): ?><td class="text-muted small"><?= s($v['sucursal_nombre'] ?? '') ?></td><?php endif; ?>
            <td class="text-muted" style="font-size:.82rem"><?= date('d/m H:i', strtotime($v['fecha'])) ?></td>
            <td><span class="badge <?= $v['tipo_pago']==='contado' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= s($v['tipo_pago']) ?></span></td>
            <td class="text-end fw-semibold"><?= formatMoney((float)$v['total']) ?></td>
            <td><a href="<?= $base ?>/ventas/detalle?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-secondary py-0"><i class="bi bi-eye"></i></a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="card mt-4 border-warning shadow-sm">
  <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
    <span class="text-warning-emphasis fw-bold">
      <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Productos Próximos a Agotarse (<= 10)
    </span>
    <a href="<?= $base ?>/reportes/inventario" class="btn btn-sm btn-outline-warning">Ver Inventario</a>
  </div>
  <div class="card-body p-0">
    <?php if (empty($lowStock)): ?>
      <div class="text-center py-4">
        <i class="bi bi-check-circle text-success fs-2 d-block mb-2"></i>
        <p class="text-muted mb-0">No hay productos con bajo inventario.</p>
      </div>
    <?php else: 
      // Agrupar por sucursal
      $lowStockGrouped = [];
      foreach ($lowStock as $ls) {
          $suc = $ls['sucursal_nombre'] ?: 'Desconocida';
          if (!isset($lowStockGrouped[$suc])) {
              $lowStockGrouped[$suc] = [];
          }
          $lowStockGrouped[$suc][] = $ls;
      }
    ?>
    <div class="table-responsive">
      <?php foreach ($lowStockGrouped as $sucursal => $items): ?>
        <?php if ($esAdmin): ?>
        <div class="bg-light px-3 py-2 border-bottom border-top fw-bold text-secondary d-flex align-items-center">
            <i class="bi bi-shop me-2"></i>Sucursal: <?= s($sucursal) ?>
        </div>
        <?php endif; ?>
        
        <table class="table table-hover table-sm mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 25%">SKU</th>
              <th style="width: 45%">Producto</th>
              <th class="text-center" style="width: 15%">Existencia</th>
              <th style="width: 15%">Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $ls): 
              $cant = (float)$ls['cantidad'];
              if ($cant <= 0) {
                  $badgeClass = 'bg-danger';
                  $badgeText = 'Agotado';
              } elseif ($cant <= 5) {
                  $badgeClass = 'bg-warning text-dark';
                  $badgeText = 'Crítico';
              } else {
                  $badgeClass = 'bg-info text-dark';
                  $badgeText = 'Bajo';
              }
            ?>
            <tr>
              <td class="text-muted small"><?= s($ls['sku'] ?: 'N/A') ?></td>
              <td class="fw-semibold text-truncate" style="max-width: 250px;"><?= s($ls['nombre']) ?></td>
              <td class="text-center fw-bold text-danger"><?= $cant ?></td>
              <td><span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
