<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-gray-800"><i class="bi bi-wallet2 me-2"></i><?= $titulo ?></h1>
    <a href="<?= $base ?>/gastos" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver al Listado
    </a>
</div>

<?php include __DIR__ . '/../templates/alertas.php'; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="POST" action="<?= $base ?>/gastos/crear" class="row g-4">
            
            <div class="col-md-6">
                <label class="form-label fw-bold">Categoría de Gasto <span class="text-danger">*</span></label>
                <select name="categoria_id" class="form-select" required>
                    <option value="">Seleccionar categoría...</option>
                    <?php foreach($categorias as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $gasto->categoria_id == $c['id'] ? 'selected' : '' ?>>
                        <?= s($c['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Clasificación para tus reportes financieros.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Sucursal Asociada</label>
                <select name="sucursal_id" class="form-select">
                    <option value="">Ninguna (Gasto Administrativo Global)</option>
                    <?php foreach($sucursales as $s): ?>
                    <option value="<?= $s->id ?>" <?= $gasto->sucursal_id == $s->id ? 'selected' : '' ?>>
                        <?= s($s->nombre) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Si el gasto es de una tienda específica, selecciónala aquí.</div>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-bold">Descripción del Gasto <span class="text-danger">*</span></label>
                <input type="text" name="descripcion" class="form-control" placeholder="Ej: Pago quincenal vendedor Juan Perez, Recibo Luz Mayo" value="<?= s($gasto->descripcion) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Monto (Q) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">Q</span>
                    <input type="number" name="monto" class="form-control fw-bold fs-5 text-danger" step="0.01" min="0.01" value="<?= $gasto->monto ?: '' ?>" required>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Fecha del Gasto</label>
                <input type="datetime-local" name="fecha" class="form-control" value="<?= $gasto->fecha ? date('Y-m-d\TH:i', strtotime($gasto->fecha)) : date('Y-m-d\TH:i') ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Comprobante / Referencia</label>
                <input type="text" name="comprobante" class="form-control" placeholder="No. Factura o Recibo (Opcional)" value="<?= s($gasto->comprobante) ?>">
            </div>

            <div class="col-12 text-end mt-5">
                <hr>
                <button type="submit" class="btn btn-success btn-lg px-5">
                    <i class="bi bi-save me-2"></i>Registrar Gasto
                </button>
            </div>

        </form>
    </div>
</div>
