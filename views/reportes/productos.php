<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="mb-0">
            <i class="bi bi-box-seam-fill text-warning me-2"></i>Rendimiento Comercial
        </h2>
        <p class="text-muted mb-0">Análisis de rotación de inventario y desempeño de ventas</p>
    </div>
</div>

<div class="card shadow-sm mb-4 border-0">
    <div class="card-body bg-light rounded">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Sucursal</label>
                <select name="sucursal_id" class="form-select">
                    <option value="0">Todas las sucursales (Global)</option>
                    <?php foreach ($sucursales as $s): ?>
                        <option value="<?= $s->id ?>" <?= $sucursal_id === $s->id ? 'selected' : '' ?>><?= s($s->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold">
                    <i class="bi bi-filter"></i> Generar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Ventas por Sucursal -->
    <?php if ($sucursal_id === 0): ?>
    <div class="col-12 mb-2">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-shop me-2"></i>Ventas por Sucursal</h5>
            </div>
            <div class="card-body pt-0">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sucursal</th>
                                    <th class="text-end">Total Vendido</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $nombresSucursales = [];
                                $ventasSucursales = [];
                                foreach ($ventas_sucursal as $vs): 
                                    $nombresSucursales[] = $vs['nombre'];
                                    $ventasSucursales[] = (float)$vs['total_ventas'];
                                ?>
                                <tr>
                                    <td class="fw-bold"><?= s($vs['nombre']) ?></td>
                                    <td class="text-end text-success fw-bold">Q<?= number_format($vs['total_ventas'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-7">
                        <div style="height: 250px;">
                            <canvas id="chartSucursales"
                                    data-labels='<?= htmlspecialchars(json_encode($nombresSucursales), ENT_QUOTES, 'UTF-8') ?>'
                                    data-ventas='<?= htmlspecialchars(json_encode($ventasSucursales), ENT_QUOTES, 'UTF-8') ?>'>
                            </canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Top Productos Vendidos (Cantidad) -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-trophy-fill text-warning me-2"></i>Top 10 Más Vendidos</h5>
                <span class="badge bg-primary rounded-pill">Por Volumen</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($top_vendidos)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No hay ventas registradas.</td></tr>
                            <?php else: ?>
                                <?php $i = 1; foreach($top_vendidos as $p): ?>
                                <tr>
                                    <td class="text-muted fw-bold"><?= $i++ ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= s($p['nombre']) ?></div>
                                        <div class="small text-muted">SKU: <?= s($p['sku']) ?></div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-info text-dark rounded-pill px-3 py-2 fs-6">
                                            <?= number_format($p['cantidad_total'], 2) ?> <?= $p['unidad'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        Q<?= number_format($p['ingresos_totales'], 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Ganancias -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100 border-start border-success border-4">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-success"><i class="bi bi-graph-up-arrow me-2"></i>Top 10 Mayor Ganancia</h5>
                <span class="badge bg-success rounded-pill">Por Utilidad Neta</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th class="text-end">Ingresos</th>
                                <th class="text-end">Ganancia (Utilidad)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($top_ganancias)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No hay ventas registradas.</td></tr>
                            <?php else: ?>
                                <?php $i = 1; foreach($top_ganancias as $p): ?>
                                <tr>
                                    <td class="text-muted fw-bold"><?= $i++ ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= s($p['nombre']) ?></div>
                                        <div class="small text-muted">SKU: <?= s($p['sku']) ?></div>
                                    </td>
                                    <td class="text-end text-muted">
                                        Q<?= number_format($p['ingresos_totales'], 2) ?>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-3 py-2 fs-6">
                                            Q<?= number_format($p['utilidad_total'], 2) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos sin rotación -->
    <div class="col-12 mt-5">
        <div class="card shadow-sm border-0 border-top border-danger border-4">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Alerta de Estancamiento (Dead Stock)</h5>
                <span class="text-muted small">Productos con inventario que no tuvieron ventas en este periodo</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-danger">
                            <tr>
                                <th>SKU</th>
                                <th>Producto</th>
                                <th class="text-end">Stock Actual Estancado</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($productos_sin_rotacion)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-success fw-bold"><i class="bi bi-check-circle-fill me-2"></i>¡Excelente! Todos tus productos en inventario se están moviendo.</td></tr>
                            <?php else: ?>
                                <?php foreach($productos_sin_rotacion as $p): ?>
                                <tr>
                                    <td class="text-muted"><?= s($p['sku']) ?></td>
                                    <td class="fw-bold"><?= s($p['nombre']) ?></td>
                                    <td class="text-end fw-bold text-danger">
                                        <?= number_format($p['stock_total'], 2) ?> <?= s($p['unidad']) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">Sin Movimiento</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/reportes.js') ?>"></script>
