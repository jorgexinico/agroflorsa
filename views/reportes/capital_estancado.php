<?php
$base = $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '';

$total_capital = 0;
foreach($productos_estancados as $p) {
    $total_capital += (float)$p['valor_estancado'];
}
?>

<div class="container-fluid px-4 pb-5">
    <h1 class="mt-4 text-dark fw-bold"><i class="bi bi-box-seam me-2 text-warning"></i><?= $titulo ?></h1>
    <ol class="breadcrumb mb-4 shadow-sm p-2 bg-white rounded">
        <li class="breadcrumb-item"><a href="<?= $base ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active">Reportes</li>
        <li class="breadcrumb-item active"><?= $titulo ?></li>
    </ol>

    <!-- Filtros -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body bg-light rounded">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary">Sucursal</label>
                    <select name="sucursal_id" class="form-select border-primary shadow-sm">
                        <option value="0">Todas las Sucursales</option>
                        <?php foreach($sucursales as $s): ?>
                            <option value="<?= $s->id ?>" <?= $s->id == $sucursal_id ? 'selected' : '' ?>><?= s($s->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary">Días sin rotación</label>
                    <select name="dias" class="form-select border-primary shadow-sm">
                        <option value="30" <?= $dias_estancado == 30 ? 'selected' : '' ?>>Más de 30 días</option>
                        <option value="60" <?= $dias_estancado == 60 ? 'selected' : '' ?>>Más de 60 días</option>
                        <option value="90" <?= $dias_estancado == 90 ? 'selected' : '' ?>>Más de 90 días</option>
                        <option value="120" <?= $dias_estancado == 120 ? 'selected' : '' ?>>Más de 120 días</option>
                        <option value="180" <?= $dias_estancado == 180 ? 'selected' : '' ?>>Más de 6 meses (180 días)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm py-2">
                        <i class="bi bi-filter me-1"></i> Generar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs Gerenciales -->
    <div class="row mb-4">
        <!-- Tarjeta: Dinero Congelado -->
        <div class="col-xl-6 col-md-6 mb-3 mb-md-0">
            <div class="card bg-danger bg-gradient text-white shadow border-0 h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 text-uppercase fw-bold mb-1" style="letter-spacing: 1px;">Capital Congelado (Costo)</div>
                            <div class="display-5 fw-bold mb-0">Q <?= number_format($total_capital, 2) ?></div>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="bi bi-cash-stack fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                    <span class="small text-white-50"><i class="bi bi-info-circle me-1"></i>Dinero inmovilizado en inventario de lento movimiento.</span>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Cantidad de Productos -->
        <div class="col-xl-6 col-md-6">
            <div class="card bg-warning bg-gradient text-dark shadow border-0 h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-dark-50 text-uppercase fw-bold mb-1" style="letter-spacing: 1px;">Productos Estancados</div>
                            <div class="display-5 fw-bold mb-0"><?= count($productos_estancados) ?></div>
                        </div>
                        <div class="bg-dark bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-boxes fs-1"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                    <span class="small text-dark-50"><i class="bi bi-info-circle me-1"></i>Cantidad de referencias que requieren atención.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Detalles -->
    <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark"><i class="bi bi-table text-primary me-2"></i>Detalle de Productos</h5>
            <button class="btn btn-outline-success fw-bold px-3 rounded-pill" onclick="exportarExcel()">
                <i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" id="tablaEstancados">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Producto</th>
                            <th>SKU</th>
                            <th>Sucursal</th>
                            <th class="text-center">Última Venta</th>
                            <th class="text-center">Stock Actual</th>
                            <th class="text-end">Costo Unitario</th>
                            <th class="text-end pe-4">Valor Estancado (Q)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos_estancados)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center text-muted">
                                        <i class="bi bi-emoji-sunglasses fs-1 text-success mb-2"></i>
                                        <h5 class="fw-bold">¡Inventario Saludable!</h5>
                                        <p class="mb-0">No hay productos estancados con los filtros seleccionados.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos_estancados as $p): 
                                $ultima = $p['ultima_venta'] ? date('d/m/Y', strtotime($p['ultima_venta'])) : '<span class="badge bg-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle me-1"></i> Nunca Vendido</span>';
                                
                                // Calcular días
                                if ($p['ultima_venta']) {
                                    $dias = (strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($p['ultima_venta'])))) / (60 * 60 * 24);
                                    $ultima .= " <small class='text-danger fw-bold d-block mt-1'>Hace " . round($dias) . " días</small>";
                                }
                            ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?= s($p['nombre']) ?></td>
                                    <td><span class="text-muted" style="font-family: monospace;"><?= s($p['sku']) ?></span></td>
                                    <td><span class="badge bg-light text-dark border"><i class="bi bi-shop me-1"></i> <?= s($p['sucursal_nombre']) ?></span></td>
                                    <td class="text-center"><?= $ultima ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary fs-6 rounded-pill px-3"><?= (float)$p['stock_total'] ?></span>
                                    </td>
                                    <td class="text-end text-muted">Q <?= number_format((float)$p['precio_compra'], 2) ?></td>
                                    <td class="text-end pe-4 fw-bold text-danger fs-5">
                                        Q <?= number_format((float)$p['valor_estancado'], 2) ?>
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

<script>
function exportarExcel() {
    let tabla = document.getElementById("tablaEstancados");
    // Clonar tabla para quitar íconos/estilos si es necesario, pero básico funciona
    let html = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head><meta charset="UTF-8"></head>
        <body>${tabla.outerHTML}</body>
        </html>
    `;
    let blob = new Blob([html], {type: 'application/vnd.ms-excel'});
    let url = URL.createObjectURL(blob);
    let link = document.createElement("a");
    link.href = url;
    link.download = "Reporte_Capital_Estancado_<?= date('Y-m-d_H-i') ?>.xls";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
