<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Registrar Nuevo Traslado</h5>
            </div>
            <div class="card-body">
                <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

                <form method="POST" id="formTraslado">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Sucursal Origen (Desde donde sale)</label>
                            <select name="sucursal_origen_id" class="form-select" required>
                                <option value="">Seleccionar origen...</option>
                                <?php foreach ($sucursales as $s): ?>
                                    <option value="<?= $s->id ?>"><?= s($s->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Sucursal Destino (Hacia donde va)</label>
                            <select name="sucursal_destino_id" class="form-select" required>
                                <option value="">Seleccionar destino...</option>
                                <?php foreach ($sucursales as $s): ?>
                                    <option value="<?= $s->id ?>"><?= s($s->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Nota / Observación</label>
                            <input type="text" name="nota" class="form-control" placeholder="Ej: Abastecimiento semanal">
                        </div>
                    </div>

                    <div class="border-top pt-4">
                        <h6 class="mb-3 text-muted">Productos a Trasladar</h6>
                        
                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-md-7">
                                <label class="small text-muted">Seleccionar Producto</label>
                                <select id="selProducto" class="form-select select2" disabled>
                                    <option value="">Primero selecciona origen...</option>
                                </select>
                                <div id="stockInfo" class="small text-primary mt-1" style="height: 20px;"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted">Cantidad <span id="maxStock"></span></label>
                                <input type="number" id="inpCantidad" class="form-control" step="0.001" min="0.001">
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btnAgregar" class="btn btn-dark w-100">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle" id="tablaItems">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-end" style="width: 150px;">Cantidad</th>
                                        <th class="text-center" style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="filaVacia"><td colspan="3" class="text-center py-3 text-muted small">No hay productos agregados</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="/<?= $_ENV['APP_NAME'] ?>/traslados" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-send me-1"></i>Enviar Traslado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selOrigen  = document.querySelector('select[name="sucursal_origen_id"]');
    const selDestino = document.querySelector('select[name="sucursal_destino_id"]');
    const selProd    = document.getElementById('selProducto');
    const inpCant    = document.getElementById('inpCantidad');
    const stockInfo  = document.getElementById('stockInfo');
    const btnAgregar = document.getElementById('btnAgregar');
    const tabla      = document.getElementById('tablaItems').querySelector('tbody');
    const filaVacia  = document.getElementById('filaVacia');

    // Al cambiar la sucursal de origen, cargar sus productos vía Fetch (Tecnología estándar moderna)
    selOrigen.addEventListener('change', async function() {
        const sucId = this.value;
        
        // Limpiar tabla si cambian de sucursal
        tabla.innerHTML = '';
        tabla.appendChild(filaVacia);
        selProd.innerHTML = '<option value="">Cargando productos...</option>';
        selProd.disabled = true;
        stockInfo.textContent = '';

        if (!sucId) {
            selProd.innerHTML = '<option value="">Primero selecciona origen...</option>';
            return;
        }

        try {
            const res = await fetch(`/<?= $_ENV['APP_NAME'] ?>/inventario/productos-sucursal-ajax?sucursal_id=${sucId}`);
            const data = await res.json();

            if (data.ok && data.productos.length > 0) {
                selProd.innerHTML = '<option value="">Buscar producto con stock...</option>';
                data.productos.forEach(p => {
                    selProd.innerHTML += `<option value="${p.id}" data-nombre="${p.nombre}" data-stock="${p.stock}" data-unidad="${p.unidad}">${p.nombre} [${p.sku}] - Stock: ${parseFloat(p.stock).toFixed(2)} ${p.unidad}</option>`;
                });
                selProd.disabled = false;
            } else {
                selProd.innerHTML = '<option value="">No hay productos con stock en esta sucursal</option>';
            }
        } catch (err) {
            console.error(err);
            selProd.innerHTML = '<option value="">Error al cargar productos</option>';
        }
    });

    // Mostrar stock disponible al elegir producto
    selProd.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt.value) {
            stockInfo.textContent = `Stock disponible: ${parseFloat(opt.dataset.stock).toFixed(2)} ${opt.dataset.unidad}`;
            inpCant.max = opt.dataset.stock;
        } else {
            stockInfo.textContent = '';
        }
    });

    btnAgregar.addEventListener('click', function() {
        const opt = selProd.options[selProd.selectedIndex];
        const id   = opt.value;
        const nombre = opt.dataset.nombre;
        const stock  = parseFloat(opt.dataset.stock);
        const cant   = parseFloat(inpCant.value);

        if (!id || isNaN(cant) || cant <= 0) return;

        if (cant > stock) {
            alert('No puedes trasladar más de lo disponible en origen');
            return;
        }

        if (document.getElementById('filaVacia')) document.getElementById('filaVacia').remove();

        // Evitar duplicados
        const existe = document.querySelector(`input[name="producto_id[]"][value="${id}"]`);
        if (existe) {
            alert('Este producto ya está en la lista');
            return;
        }

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                ${nombre}
                <input type="hidden" name="producto_id[]" value="${id}">
            </td>
            <td>
                <input type="number" name="cantidad[]" class="form-control form-control-sm text-end" value="${cant}" step="0.001" readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tabla.appendChild(tr);

        // Limpiar
        selProd.value = '';
        inpCant.value = '';
        stockInfo.textContent = '';
    });
});
</script>
