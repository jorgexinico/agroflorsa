<?php
// views/pages/index.php — Dashboard
use Models\Venta;
use Models\Turno;
use Models\Inventario;
use Model\ActiveRecord;

$sucursal_id = $_SESSION['sucursal_id'] ?? 0;
$hoy         = date('Y-m-d');

// Estadísticas del día
$ventasHoy = ActiveRecord::fetchFirstRaw(
    "SELECT COALESCE(SUM(total),0) AS monto, COUNT(id) AS cantidad
     FROM ventas WHERE DATE(fecha) = :hoy AND estado = 'emitida' AND sucursal_id = :suc",
    [':hoy' => $hoy, ':suc' => $sucursal_id]
) ?? ['monto' => 0, 'cantidad' => 0];

$turnoActivo = $sucursal_id ? Turno::fetchFirstRaw(
    "SELECT t.*, s.nombre AS sucursal_nombre
     FROM turnos t JOIN sucursales s ON s.id = t.sucursal_id
     WHERE t.usuario_id = :uid AND t.estado = 'abierto' LIMIT 1",
    [':uid' => $_SESSION['usuario_id']]
) : null;

$totalProductos = ActiveRecord::fetchFirstRaw(
    "SELECT COUNT(id) AS n FROM productos WHERE activo = 1"
)['n'] ?? 0;

$ultimasVentas = ActiveRecord::fetchRaw(
    "SELECT v.id, v.total, v.fecha, v.tipo_pago, c.nombre AS cliente
     FROM ventas v LEFT JOIN clientes c ON c.id = v.cliente_id
     WHERE v.estado = 'emitida' AND v.sucursal_id = :suc
     ORDER BY v.fecha DESC LIMIT 8",
    [':suc' => $sucursal_id]
);
?>

<div class="row g-3 mb-4">
  <!-- Ventas hoy -->
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card__icon bg-success bg-opacity-10">
        <i class="bi bi-cash-stack text-success"></i>
      </div>
      <div>
        <div class="stat-card__value text-success"><?= formatMoney((float)$ventasHoy['monto']) ?></div>
        <div class="stat-card__label">Ventas hoy (<?= $ventasHoy['cantidad'] ?> facturas)</div>
      </div>
    </div>
  </div>

  <!-- Turno -->
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card__icon bg-primary bg-opacity-10">
        <i class="bi bi-clock-history text-primary"></i>
      </div>
      <div>
        <div class="stat-card__value" style="font-size:1.1rem">
          <?= $turnoActivo ? '<span class="text-success">Abierto</span>' : '<span class="text-secondary">Sin turno</span>' ?>
        </div>
        <div class="stat-card__label">
          <?= $turnoActivo ? s($turnoActivo['sucursal_nombre']) : 'No hay turno activo' ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Productos activos -->
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card__icon bg-warning bg-opacity-10">
        <i class="bi bi-box-seam text-warning"></i>
      </div>
      <div>
        <div class="stat-card__value"><?= $totalProductos ?></div>
        <div class="stat-card__label">Productos activos</div>
      </div>
    </div>
  </div>

  <!-- Acceso rápido -->
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card__icon bg-info bg-opacity-10">
        <i class="bi bi-lightning-charge text-info"></i>
      </div>
      <div>
        <div class="stat-card__label fw-semibold mb-2">Acceso rápido</div>
        <?php if ($turnoActivo): ?>
          <a href="/<?= $_ENV['APP_NAME'] ?>/ventas/nueva" class="btn btn-sm btn-success">
            <i class="bi bi-cart-plus me-1"></i>Nueva venta
          </a>
        <?php else: ?>
          <a href="/<?= $_ENV['APP_NAME'] ?>/turnos/abrir" class="btn btn-sm btn-outline-success">
            <i class="bi bi-play-circle me-1"></i>Abrir turno
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Últimas Ventas -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-receipt me-2 text-success"></i>Últimas ventas</span>
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
            <th>#</th><th>Cliente</th><th>Fecha</th><th>Tipo</th><th class="text-end">Total</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ultimasVentas as $v): ?>
          <tr>
            <td><span class="badge bg-light text-dark border"><?= $v['id'] ?></span></td>
            <td><?= s($v['cliente'] ?? 'Consumidor final') ?></td>
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