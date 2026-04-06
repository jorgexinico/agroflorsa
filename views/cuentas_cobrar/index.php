<?php // views/cuentas_cobrar/index.php ?>

<!-- ══ KPIs ══════════════════════════════════════════════════ -->
<div class="row g-3 mb-4">

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card__icon bg-danger bg-opacity-10">
        <i class="bi bi-hourglass-split text-danger"></i>
      </div>
      <div>
        <div class="stat-card__value text-danger"><?= count(array_filter($cuentas, fn($c) => $c['estado'] === 'pendiente')) ?></div>
        <div class="stat-card__label">Pendientes</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card__icon bg-warning bg-opacity-10">
        <i class="bi bi-pie-chart text-warning"></i>
      </div>
      <div>
        <div class="stat-card__value text-warning"><?= count(array_filter($cuentas, fn($c) => $c['estado'] === 'parcial')) ?></div>
        <div class="stat-card__label">Con abono parcial</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card__icon bg-danger bg-opacity-10">
        <i class="bi bi-currency-dollar text-danger"></i>
      </div>
      <div>
        <div class="stat-card__value text-danger"><?= formatMoney($totalSaldo) ?></div>
        <div class="stat-card__label">Saldo por cobrar</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card__icon bg-primary bg-opacity-10">
        <i class="bi bi-receipt text-primary"></i>
      </div>
      <div>
        <div class="stat-card__value"><?= formatMoney($totalGeneral) ?></div>
        <div class="stat-card__label">Total facturado (CxC)</div>
      </div>
    </div>
  </div>

</div>

<!-- ══ FILTROS ════════════════════════════════════════════════ -->
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?= $base ?>/cuentas-cobrar" class="row g-2 align-items-end">
      <div class="col-md-5 col-lg-4">
        <label class="form-label form-label-sm mb-1 fw-semibold">Filtrar por cliente</label>
        <select name="cliente_id" id="filtro-cliente" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="0">— Todos los clientes —</option>
          <?php foreach ($clientes as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $cliente_id == $c['id'] ? 'selected' : '' ?>>
            <?= s($c['nombre']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <?php if ($cliente_id): ?>
        <a href="<?= $base ?>/cuentas-cobrar" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-x-circle me-1"></i>Limpiar filtro
        </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- ══ TABLA ══════════════════════════════════════════════════ -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>
      <i class="bi bi-file-earmark-text me-2 text-warning"></i>
      Cuentas por Cobrar Abiertas
    </span>
    <span class="badge bg-secondary"><?= count($cuentas) ?> cuenta<?= count($cuentas) != 1 ? 's' : '' ?></span>
  </div>

  <?php if (empty($cuentas)): ?>
  <div class="card-body text-center py-5">
    <i class="bi bi-check-circle-fill fs-1 text-success d-block mb-2"></i>
    <p class="text-muted mb-0">No hay cuentas pendientes<?= $cliente_id ? ' para este cliente' : '' ?>.</p>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="tabla-cxc">
      <thead class="table-light">
        <tr>
          <th class="ps-3">#CxC</th>
          <th>Venta</th>
          <th>Cliente</th>
          <th>Sucursal</th>
          <th>Fecha</th>
          <th class="text-end">Total</th>
          <th class="text-end">Pagado</th>
          <th class="text-end">Saldo</th>
          <th>Estado</th>
          <th class="text-center" style="width:60px">Prog.</th>
          <th class="pe-3" style="width:90px"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cuentas as $c):
          $pct = $c['total'] > 0 ? round(($c['pagado'] / $c['total']) * 100) : 0;
          $rowClass = $c['estado'] === 'parcial' ? 'table-warning-subtle' : '';
        ?>
        <tr class="<?= $rowClass ?>">
          <td class="ps-3">
            <span class="badge bg-light text-dark border"><?= $c['id'] ?></span>
          </td>
          <td>
            <a href="<?= $base ?>/ventas/detalle?id=<?= $c['venta_id'] ?>"
               class="text-decoration-none fw-semibold">#<?= $c['venta_id'] ?></a>
          </td>
          <td>
            <div class="fw-semibold"><?= s($c['cliente_nombre']) ?></div>
          </td>
          <td class="text-muted small"><?= s($c['sucursal_nombre']) ?></td>
          <td class="text-muted small"><?= date('d/m/Y', strtotime($c['venta_fecha'])) ?></td>
          <td class="text-end"><?= formatMoney((float)$c['total']) ?></td>
          <td class="text-end text-success fw-semibold"><?= formatMoney((float)$c['pagado']) ?></td>
          <td class="text-end fw-bold text-danger"><?= formatMoney((float)$c['saldo']) ?></td>
          <td>
            <?php if ($c['estado'] === 'parcial'): ?>
              <span class="badge bg-warning text-dark">Parcial</span>
            <?php else: ?>
              <span class="badge bg-danger">Pendiente</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <div class="progress" style="height:6px;min-width:50px" title="<?= $pct ?>% pagado">
              <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
            </div>
            <small class="text-muted" style="font-size:.68rem"><?= $pct ?>%</small>
          </td>
          <td class="pe-3">
            <a href="<?= $base ?>/cuentas-cobrar/detalle?id=<?= $c['id'] ?>"
               class="btn btn-sm btn-outline-primary w-100">
              <i class="bi bi-cash-coin me-1"></i>Abonar
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td colspan="5" class="text-end ps-3">Totales:</td>
          <td class="text-end"><?= formatMoney($totalGeneral) ?></td>
          <td class="text-end text-success"><?= formatMoney($totalGeneral - $totalSaldo) ?></td>
          <td class="text-end text-danger"><?= formatMoney($totalSaldo) ?></td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php endif; ?>
</div>
