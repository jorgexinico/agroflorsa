<?php // views/cuentas_pagar/detalle.php ?>
<div class="row align-items-center mb-3">
    <div class="col-6">
        <a href="/<?= $_ENV['APP_NAME'] ?>/cuentas-pagar" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Atrás
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Información de la Cuenta -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Resumen de la Cuenta</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted">Proveedor</dt>
                    <dd class="col-sm-7 fw-semibold"><?= s($cxp['proveedor_nombre'] ?? 'N/A') ?></dd>

                    <dt class="col-sm-5 text-muted">Compra #</dt>
                    <dd class="col-sm-7">
                        <a href="/<?= $_ENV['APP_NAME'] ?>/compras/detalle?id=<?= $cxp['compra_id'] ?>">
                            <?= $cxp['compra_id'] ?>
                        </a>
                    </dd>

                    <dt class="col-sm-5 text-muted">Fecha Compra</dt>
                    <dd class="col-sm-7"><?= s($cxp['compra_fecha']) ?></dd>

                    <dt class="col-sm-5 text-muted">Sucursal</dt>
                    <dd class="col-sm-7"><?= s($cxp['sucursal_nombre']) ?></dd>

                    <hr class="my-3">

                    <dt class="col-sm-5 text-muted fs-5">Total Deuda</dt>
                    <dd class="col-sm-7 fs-5">Q <?= number_format($cxp['total'], 2) ?></dd>

                    <dt class="col-sm-5 text-success">Total Pagado</dt>
                    <dd class="col-sm-7 text-success">Q <?= number_format($cxp['pagado'], 2) ?></dd>

                    <dt class="col-sm-5 text-danger fw-bold fs-4">Saldo Actual</dt>
                    <dd class="col-sm-7 text-danger fw-bold fs-4">Q <?= number_format($cxp['saldo'], 2) ?></dd>

                    <dt class="col-sm-5 text-muted mt-2">Estado</dt>
                    <dd class="col-sm-7 mt-2">
                        <?php if ($cxp['estado'] === 'pagada'): ?>
                            <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>Pagada</span>
                        <?php elseif ($cxp['estado'] === 'parcial'): ?>
                            <span class="badge bg-info text-dark fs-6"><i class="bi bi-circle-half me-1"></i>Parcial</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-clock me-1"></i>Pendiente</span>
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Pagos y Formulario -->
    <div class="col-lg-7">
        <?php if ($cxp['estado'] !== 'pagada'): ?>
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-cash-coin me-1"></i> Registrar Nuevo Abono
            </div>
            <div class="card-body">
                <form method="POST" action="/<?= $_ENV['APP_NAME'] ?>/cuentas-pagar/abonar">
                    <input type="hidden" name="cxp_id" value="<?= $cxp['id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Monto a abonar (Q)</label>
                            <input type="number" name="monto" class="form-control fw-bold text-success fs-5"
                                   min="0.01" step="0.01" max="<?= $cxp['saldo'] ?>" value="<?= $cxp['saldo'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Método de Pago</label>
                            <select name="metodo" class="form-select text-capitalize" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="cheque">Cheque</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Ref. / No. Boleta (Opcional)</label>
                            <input type="text" name="referencia" class="form-control form-control-sm"
                                   placeholder="Ej. Cheque #5240">
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Guardar Abono
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Historial -->
        <div class="card">
            <div class="card-header bg-light">
                <i class="bi bi-clock-history me-1"></i> Historial de Pagos
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Método</th>
                            <th>Referencia</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pagos)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No hay pagos registrados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($pagos as $p): ?>
                        <tr>
                            <td><?= s((new DateTime($p['fecha']))->format('d/m/Y H:i')) ?></td>
                            <td><span class="badge bg-secondary text-capitalize"><?= s($p['metodo']) ?></span></td>
                            <td class="text-muted"><?= s($p['referencia'] ?? '—') ?></td>
                            <td class="text-end fw-semibold text-success">Q <?= number_format($p['monto'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
