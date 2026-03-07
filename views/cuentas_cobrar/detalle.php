<?php // views/cuentas_cobrar/detalle.php ?>

<?php
$estaAbierta = in_array($cuenta['estado'], ['pendiente', 'parcial']);
$pct = $cuenta['total'] > 0 ? round(($cuenta['pagado'] / $cuenta['total']) * 100) : 0;
$badgeClass = match($cuenta['estado']) {
    'pagada'  => 'bg-success',
    'parcial' => 'bg-warning text-dark',
    'anulada' => 'bg-secondary',
    default   => 'bg-danger',
};
?>

<?php if (!empty($_GET['ok'])): ?>
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
  <i class="bi bi-check-circle-fill me-2"></i><strong>Abono registrado.</strong> El saldo ha sido actualizado correctamente.
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($_GET['err']) && $_GET['err'] === 'monto'): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>El monto debe ser mayor a <strong>Q 0.00</strong> y no exceder el saldo pendiente de <strong><?= formatMoney((float)$cuenta['saldo']) ?></strong>.
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($_GET['err']) && $_GET['err'] === 'db'): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>Ocurrió un error al registrar el abono. Intenta de nuevo.
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ══ RESUMEN TOP ════════════════════════════════════════════ -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card__icon bg-primary bg-opacity-10">
        <i class="bi bi-receipt text-primary"></i>
      </div>
      <div>
        <div class="stat-card__value"><?= formatMoney((float)$cuenta['total']) ?></div>
        <div class="stat-card__label">Total facturado</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card__icon bg-success bg-opacity-10">
        <i class="bi bi-cash-stack text-success"></i>
      </div>
      <div>
        <div class="stat-card__value text-success"><?= formatMoney((float)$cuenta['pagado']) ?></div>
        <div class="stat-card__label">Total pagado</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card__icon bg-danger bg-opacity-10">
        <i class="bi bi-hourglass-split text-danger"></i>
      </div>
      <div>
        <div class="stat-card__value text-danger"><?= formatMoney((float)$cuenta['saldo']) ?></div>
        <div class="stat-card__label">Saldo pendiente</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-card__icon <?= $estaAbierta ? 'bg-warning bg-opacity-10' : 'bg-success bg-opacity-10' ?>">
        <i class="bi bi-tag <?= $estaAbierta ? 'text-warning' : 'text-success' ?>"></i>
      </div>
      <div>
        <div class="stat-card__value" style="font-size:1.05rem">
          <span class="badge <?= $badgeClass ?> fs-6"><?= ucfirst($cuenta['estado']) ?></span>
        </div>
        <div class="stat-card__label"><?= $pct ?>% completado</div>
      </div>
    </div>
  </div>
</div>

<!-- Barra de progreso global -->
<div class="card mb-4">
  <div class="card-body py-2">
    <div class="d-flex justify-content-between small text-muted mb-1">
      <span>Progreso de pago</span>
      <span><?= $pct ?>% — <?= formatMoney((float)$cuenta['pagado']) ?> de <?= formatMoney((float)$cuenta['total']) ?></span>
    </div>
    <div class="progress" style="height:12px;border-radius:8px">
      <div class="progress-bar bg-success <?= $pct < 100 ? 'progress-bar-striped progress-bar-animated' : '' ?>"
           style="width:<?= $pct ?>%;border-radius:8px" role="progressbar"></div>
    </div>
  </div>
</div>

<div class="row g-4">

  <!-- ══ INFO CUENTA ═════════════════════════════════════════ -->
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-info-circle text-primary"></i>
        <span class="fw-semibold">Datos de la Cuenta</span>
        <span class="badge bg-light text-dark border ms-auto">#<?= $cuenta['id'] ?></span>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-5 text-muted fw-normal">Cliente</dt>
          <dd class="col-7 fw-semibold"><?= s($cuenta['cliente_nombre']) ?></dd>

          <dt class="col-5 text-muted fw-normal">Venta origen</dt>
          <dd class="col-7">
            <a href="/<?= $_ENV['APP_NAME'] ?>/ventas/detalle?id=<?= $cuenta['venta_id'] ?>"
               class="btn btn-xs btn-outline-secondary btn-sm py-0 px-2">
              <i class="bi bi-eye me-1"></i>#<?= $cuenta['venta_id'] ?>
            </a>
          </dd>

          <dt class="col-5 text-muted fw-normal">Sucursal</dt>
          <dd class="col-7"><?= s($cuenta['sucursal_nombre']) ?></dd>

          <dt class="col-5 text-muted fw-normal">Fecha venta</dt>
          <dd class="col-7 text-muted"><?= date('d/m/Y H:i', strtotime($cuenta['venta_fecha'])) ?></dd>

          <dt class="col-5 text-muted fw-normal">Estado</dt>
          <dd class="col-7"><span class="badge <?= $badgeClass ?>"><?= ucfirst($cuenta['estado']) ?></span></dd>
        </dl>

        <?php if (count($abonos) > 0): ?>
        <hr class="my-3">
        <div class="small text-muted">
          <i class="bi bi-clock-history me-1"></i>
          <?= count($abonos) ?> abono<?= count($abonos) != 1 ? 's' : '' ?> registrado<?= count($abonos) != 1 ? 's' : '' ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ══ FORMULARIO ABONO ════════════════════════════════════ -->
  <?php if ($estaAbierta): ?>
  <div class="col-lg-7">
    <div class="card h-100 border-success" style="border-width:2px!important">
      <div class="card-header bg-success bg-opacity-10">
        <i class="bi bi-cash-coin text-success me-2"></i>
        <span class="fw-semibold text-success">Registrar Abono</span>
      </div>
      <div class="card-body">
        <form method="POST" action="/<?= $_ENV['APP_NAME'] ?>/cuentas-cobrar/abonar" id="form-abono">
          <input type="hidden" name="cxc_id" value="<?= $cuenta['id'] ?>">

          <!-- Accesos rápidos de monto -->
          <div class="mb-3">
            <label class="form-label fw-semibold mb-1">Monto del abono <span class="text-danger">*</span></label>
            <div class="d-flex gap-2 mb-2 flex-wrap">
              <?php
              $saldo = (float)$cuenta['saldo'];
              foreach ([25, 50, 75, 100] as $pct_btn):
                $montoSugerido = round($saldo * $pct_btn / 100, 2);
              ?>
              <button type="button" class="btn btn-sm btn-outline-secondary btn-monto-quick"
                      data-monto="<?= $montoSugerido ?>"
                      title="<?= $pct_btn ?>% del saldo">
                <?= $pct_btn ?>% (<?= formatMoney($montoSugerido) ?>)
              </button>
              <?php endforeach; ?>
            </div>
            <div class="input-group">
              <span class="input-group-text fw-bold">Q</span>
              <input type="number" name="monto" id="inp-monto" class="form-control form-control-lg"
                     step="0.01" min="0.01" max="<?= $saldo ?>"
                     placeholder="0.00" required>
            </div>
            <div class="form-text">Saldo máximo a abonar: <strong class="text-danger"><?= formatMoney($saldo) ?></strong></div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label class="form-label fw-semibold mb-1">Método de pago</label>
              <select name="metodo" class="form-select" id="sel-metodo">
                <option value="efectivo">💵 Efectivo</option>
                <option value="transferencia">🏦 Transferencia</option>
                <option value="cheque">🧾 Cheque</option>
                <option value="otro">📋 Otro</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold mb-1">Referencia / Nota</label>
              <input type="text" name="referencia" class="form-control"
                     placeholder="Núm. cheque, referencia..." maxlength="120">
            </div>
          </div>

          <!-- Preview del saldo resultante -->
          <div class="alert alert-light border mb-3 py-2" id="preview-resultado" style="display:none">
            <div class="row text-center">
              <div class="col">
                <div class="small text-muted">Abono</div>
                <div class="fw-bold text-success" id="prev-abono">Q 0.00</div>
              </div>
              <div class="col border-start border-end">
                <div class="small text-muted">Saldo actual</div>
                <div class="fw-bold text-danger"><?= formatMoney($saldo) ?></div>
              </div>
              <div class="col">
                <div class="small text-muted">Saldo resultante</div>
                <div class="fw-bold" id="prev-saldo-nuevo">—</div>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-success w-100 btn-lg" id="btn-abonar">
            <i class="bi bi-check-circle me-2"></i>Registrar Abono
          </button>
        </form>
      </div>
    </div>
  </div>
  <?php else: ?>
  <div class="col-lg-7 d-flex align-items-center justify-content-center">
    <div class="text-center py-4">
      <i class="bi bi-<?= $cuenta['estado'] === 'pagada' ? 'check-circle-fill text-success' : 'slash-circle text-secondary' ?> fs-1 d-block mb-3"></i>
      <h5><?= $cuenta['estado'] === 'pagada' ? 'Cuenta saldada completamente' : 'Cuenta anulada' ?></h5>
      <p class="text-muted">No se pueden registrar abonos en esta cuenta.</p>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /row -->

<!-- ══ HISTORIAL ══════════════════════════════════════════════ -->
<div class="card mt-4">
  <div class="card-header d-flex align-items-center gap-2">
    <i class="bi bi-clock-history text-primary"></i>
    <span class="fw-semibold">Historial de Abonos</span>
    <?php if (count($abonos) > 0): ?>
    <span class="badge bg-secondary ms-auto"><?= count($abonos) ?></span>
    <?php endif; ?>
  </div>

  <?php if (empty($abonos)): ?>
  <div class="card-body text-center py-4 text-muted">
    <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>
    Sin abonos registrados todavía.
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3">#</th>
          <th>Fecha</th>
          <th>Método</th>
          <th>Referencia</th>
          <th class="text-end pe-3">Monto</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($abonos as $a): ?>
        <tr>
          <td class="ps-3"><span class="badge bg-light text-dark border"><?= $a['id'] ?></span></td>
          <td class="text-muted"><?= date('d/m/Y H:i', strtotime($a['fecha'])) ?></td>
          <td>
            <?php
            $iconMetodo = match($a['metodo']) {
              'efectivo'      => '💵',
              'transferencia' => '🏦',
              'cheque'        => '🧾',
              default         => '📋',
            };
            ?>
            <?= $iconMetodo ?> <span class="text-capitalize"><?= s($a['metodo']) ?></span>
          </td>
          <td class="text-muted small"><?= s($a['referencia'] ?? '—') ?></td>
          <td class="text-end pe-3 fw-semibold text-success"><?= formatMoney((float)$a['monto']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr>
          <td colspan="4" class="text-end fw-bold ps-3">Total abonado:</td>
          <td class="text-end pe-3 fw-bold text-success"><?= formatMoney((float)$cuenta['pagado']) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php endif; ?>
</div>

<div class="mt-3">
  <a href="/<?= $_ENV['APP_NAME'] ?>/cuentas-cobrar" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Volver a Cuentas por Cobrar
  </a>
</div>

<script>
(function() {
  const inpMonto  = document.getElementById('inp-monto');
  const preview   = document.getElementById('preview-resultado');
  const prevAbono = document.getElementById('prev-abono');
  const prevNuevo = document.getElementById('prev-saldo-nuevo');
  const saldoMax  = <?= (float)$cuenta['saldo'] ?>;

  // Botones de monto rápido
  document.querySelectorAll('.btn-monto-quick').forEach(btn => {
    btn.addEventListener('click', () => {
      inpMonto.value = btn.dataset.monto;
      inpMonto.dispatchEvent(new Event('input'));
    });
  });

  // Preview en tiempo real
  if (inpMonto) {
    inpMonto.addEventListener('input', () => {
      const val = parseFloat(inpMonto.value) || 0;
      if (val > 0 && val <= saldoMax) {
        const nuevo = Math.max(0, saldoMax - val);
        prevAbono.textContent = 'Q ' + val.toFixed(2);
        prevNuevo.textContent = 'Q ' + nuevo.toFixed(2);
        prevNuevo.className   = 'fw-bold ' + (nuevo === 0 ? 'text-success' : 'text-danger');
        preview.style.display = '';
      } else {
        preview.style.display = 'none';
      }
    });
  }
})();
</script>
