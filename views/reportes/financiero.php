<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="mb-0">
            <i class="bi bi-bar-chart-fill text-primary me-2"></i>Estado de Resultados
        </h2>
        <p class="text-muted mb-0">Análisis de rentabilidad e indicadores financieros</p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body bg-light">
        <form method="GET" class="row g-2 align-items-end">
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
                    <option value="0">Todas (Global)</option>
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

<!-- KPIs Financieros -->
<div class="row g-3 mb-4">
    <div class="col-md-20 col-lg-4">
        <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-muted fw-bold mb-0">Ingresos (Flujo de Efectivo)</h6>
                    <div class="bg-success bg-opacity-10 text-success rounded p-2">
                        <i class="bi bi-cash-coin fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">$<?= number_format($total_ingresos, 2) ?></h3>
                <small class="text-muted d-block mt-1">
                    Ventas al Contado: $<?= number_format($total_ingresos_contado, 2) ?><br>
                    Abonos (Créditos): $<?= number_format($total_abonos, 2) ?>
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-muted fw-bold mb-0">Costo de Ventas</h6>
                    <div class="bg-warning bg-opacity-10 text-warning rounded p-2">
                        <i class="bi bi-cart-x fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-warning">-$<?= number_format($total_costos, 2) ?></h3>
                <small class="text-muted">Costo de la mercadería vendida</small>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 border-start border-primary border-4 bg-primary bg-opacity-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-primary fw-bold mb-0">Utilidad Bruta</h6>
                    <div class="bg-primary bg-opacity-25 text-primary rounded p-2">
                        <i class="bi bi-graph-up fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-primary">$<?= number_format($utilidad_bruta, 2) ?></h3>
                <small class="text-muted">Ingresos menos Costo de Ventas</small>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-6 mt-4">
        <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-muted fw-bold mb-0">Gastos Operativos</h6>
                    <div class="bg-danger bg-opacity-10 text-danger rounded p-2">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-danger">-$<?= number_format($total_gastos, 2) ?></h3>
                <small class="text-muted">Egresos administrativos y operativos</small>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-6 mt-4">
        <div class="card border-0 shadow-sm h-100 border-start <?= $utilidad_neta >= 0 ? 'border-success' : 'border-danger' ?> border-5 <?= $utilidad_neta >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10' ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 <?= $utilidad_neta >= 0 ? 'text-success' : 'text-danger' ?>">UTILIDAD NETA</h6>
                    <div class="<?= $utilidad_neta >= 0 ? 'bg-success bg-opacity-25 text-success' : 'bg-danger bg-opacity-25 text-danger' ?> rounded p-2">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0 <?= $utilidad_neta >= 0 ? 'text-success' : 'text-danger' ?>">
                    $<?= number_format($utilidad_neta, 2) ?>
                </h2>
                <small class="text-muted">Ganancia o pérdida real del periodo</small>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos y Tablas -->
<div class="row mt-4">
    <!-- Gráfico Resumen -->
    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="card-title fw-bold m-0"><i class="bi bi-bar-chart me-2"></i>Resumen Financiero</h6>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="chartResumen" 
                            data-ingresos="<?= $total_ingresos ?>" 
                            data-costos="<?= $total_costos ?>" 
                            data-gastos="<?= $total_gastos ?>">
                    </canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico Gastos -->
    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="card-title fw-bold m-0"><i class="bi bi-pie-chart me-2"></i>Distribución de Gastos Operativos</h6>
            </div>
            <div class="card-body">
                <?php if ($total_gastos > 0): ?>
                    <div style="height: 300px;">
                        <canvas id="chartGastos" 
                                data-labels='<?= htmlspecialchars($categorias_gastos, ENT_QUOTES, 'UTF-8') ?>' 
                                data-montos='<?= htmlspecialchars($montos_gastos, ENT_QUOTES, 'UTF-8') ?>'>
                        </canvas>
                    </div>
                <?php else: ?>
                    <div class="d-flex h-100 align-items-center justify-content-center text-muted">
                        No hay gastos operativos registrados en este periodo.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/reportes.js') ?>"></script>
