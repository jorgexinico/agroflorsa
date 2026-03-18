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
          <a href="/<?= $_ENV['APP_NAME'] ?>/cuentas-cobrar" class="d-block small text-decoration-none mt-1">Ver cuentas</a>
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
          <a href="/<?= $_ENV['APP_NAME'] ?>/cuentas-pagar" class="d-block small text-decoration-none mt-1 text-warning">Ver cuentas</a>
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
          <a href="/<?= $_ENV['APP_NAME'] ?>/ventas/nueva" class="btn btn-sm btn-success">
            <i class="bi bi-cart-plus me-1"></i>Vender
          </a>
        <?php else: ?>
          <a href="/<?= $_ENV['APP_NAME'] ?>/turnos/abrir" class="btn btn-sm btn-outline-success">
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
    <a href="/<?= $_ENV['APP_NAME'] ?>/ventas" class="btn btn-sm btn-outline-secondary">Ver todas</a>
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
            <td><a href="/<?= $_ENV['APP_NAME'] ?>/ventas/detalle?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-secondary py-0"><i class="bi bi-eye"></i></a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>