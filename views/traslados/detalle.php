<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Traslado #<?= $traslado['id'] ?></h5>
                <a href="/<?= $_ENV['APP_NAME'] ?>/traslados" class="btn btn-outline-secondary btn-sm">Regresar</a>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-sm-4">
                        <label class="text-muted small d-block">Fecha:</label>
                        <span class="fw-semibold"><?= date('d/m/Y H:i', strtotime($traslado['fecha'])) ?></span>
                    </div>
                    <div class="col-sm-4">
                        <label class="text-muted small d-block">Origen:</label>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2"><?= s($traslado['origen']) ?></span>
                        <i class="bi bi-arrow-right mx-2 text-muted"></i>
                        <span class="badge bg-success bg-opacity-10 text-success border px-2"><?= s($traslado['destino']) ?></span>
                    </div>
                    <div class="col-sm-4">
                        <label class="text-muted small d-block">Estado:</label>
                        <?php if ($traslado['estado'] === 'enviado'): ?>
                            <span class="badge bg-warning text-dark">ENVIADO (PENDIENTE)</span>
                        <?php else: ?>
                            <span class="badge bg-success">RECIBIDO</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($traslado['nota']): ?>
                    <div class="col-12 mt-2">
                        <label class="text-muted small d-block">Nota:</label>
                        <div class="p-2 border rounded bg-light small"><?= s($traslado['nota']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <h6 class="mb-3 border-bottom pb-2">Detalle de Productos</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>SKU</th>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Costo Ref.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalle as $item): ?>
                            <tr>
                                <td class="small text-muted"><?= s($item['sku'] ?: '—') ?></td>
                                <td><?= s($item['producto_nombre']) ?></td>
                                <td class="text-end fw-bold"><?= number_format($item['cantidad'], 3) ?> <span class="small text-muted fw-normal"><?= s($item['unidad']) ?></span></td>
                                <td class="text-end small text-muted">Q<?= number_format($item['costo_unitario'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
