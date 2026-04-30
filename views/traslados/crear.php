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
                                    <option value="<?= $s->id ?>" <?= (($datos['sucursal_origen_id'] ?? '') == $s->id) ? 'selected' : '' ?>>
                                        <?= s($s->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Sucursal Destino (Hacia donde va)</label>
                            <select name="sucursal_destino_id" class="form-select" required>
                                <option value="">Seleccionar destino...</option>
                                <?php foreach ($sucursales as $s): ?>
                                    <option value="<?= $s->id ?>" <?= (($datos['sucursal_destino_id'] ?? '') == $s->id) ? 'selected' : '' ?>>
                                        <?= s($s->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Nota / Observación</label>
                            <input type="text" name="nota" class="form-control" placeholder="Ej: Abastecimiento semanal" value="<?= s($datos['nota'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="border-top pt-4">
                        <h6 class="mb-3 text-muted">Productos a Trasladar</h6>
                        
                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-md-12">
                                <label class="small text-muted fw-bold mb-1">Buscar y agregar producto</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-search text-primary"></i></span>
                                    <input type="text" id="selProducto" class="form-control form-control-lg border-start-0" 
                                           placeholder="Primero selecciona sucursal origen..." list="lista-productos-origen" disabled>
                                    <datalist id="lista-productos-origen"></datalist>
                                </div>
                                <div id="stockInfo" class="small text-muted mt-1" style="min-height: 20px;">
                                    Escribe el nombre o SKU y selecciona de la lista para agregarlo al traslado.
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle" id="tablaItems">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center" style="width: 150px;">Disp. Origen</th>
                                        <th class="text-end" style="width: 150px;">Cantidad a Enviar</th>
                                        <th class="text-center" style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($datos['producto_id'])): ?>
                                        <?php foreach ($datos['producto_id'] as $i => $pid): 
                                            // Buscar el nombre del producto en el array $productos
                                            $prodNombre = 'Producto #' . $pid;
                                            foreach ($productos as $p) {
                                                if ($p->id == $pid) {
                                                    $prodNombre = $p->nombre;
                                                    break;
                                                }
                                            }
                                        ?>
                                        <tr>
                                            <td>
                                                <?= s($prodNombre) ?>
                                                <input type="hidden" name="producto_id[]" value="<?= $pid ?>">
                                            </td>
                                            <td class="text-center text-muted small">
                                                —
                                            </td>
                                            <td>
                                                <input type="number" name="cantidad[]" class="form-control form-control-sm text-end fw-bold" value="<?= $datos['cantidad'][$i] ?>" step="0.001" min="0.001" required>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr id="filaVacia"><td colspan="3" class="text-center py-3 text-muted small">No hay productos agregados</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="<?= $base ?>/traslados" class="btn btn-outline-secondary">Cancelar</a>
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
            selProd.placeholder = 'Primero selecciona origen...';
            return;
        }

        selProd.placeholder = 'Cargando productos...';
        
        try {
            const res = await fetch(`<?= $base ?>/inventario/productos-sucursal-ajax?sucursal_id=${sucId}`);
            const data = await res.json();
            
            const datalist = document.getElementById('lista-productos-origen');
            datalist.innerHTML = '';

            if (data.ok && data.productos.length > 0) {
                selProd.placeholder = 'Buscar por nombre o SKU...';
                data.productos.forEach(p => {
                    const desc = `${p.nombre} [${p.sku || 'S/S'}] - Stock: ${parseFloat(p.stock).toFixed(2)} ${p.unidad}`;
                    const opt = document.createElement('option');
                    opt.value = desc;
                    opt.dataset.id = p.id;
                    opt.dataset.nombre = p.nombre;
                    opt.dataset.stock = p.stock;
                    opt.dataset.unidad = p.unidad;
                    datalist.appendChild(opt);
                });
                selProd.disabled = false;
            } else {
                selProd.placeholder = 'No hay stock en esta sucursal';
            }
        } catch (err) {
            console.error(err);
            selProd.placeholder = 'Error al cargar productos';
        }
    });

    // Escuchar selección del datalist
    function procesarBusqueda(e) {
        const val = selProd.value.trim().toLowerCase();
        if (!val) return;
        
        const datalist = document.getElementById('lista-productos-origen');
        const options = datalist.options;
        
        let seleccion = null;
        for (let i = 0; i < options.length; i++) {
            if (options[i].value.toLowerCase() === val) {
                seleccion = options[i];
                break;
            }
        }
        
        if (seleccion) {
            agregarATabla(seleccion);
            selProd.value = '';
            selProd.blur();
            setTimeout(() => selProd.focus(), 50);
        }
    }

    selProd.addEventListener('input', procesarBusqueda);
    selProd.addEventListener('change', procesarBusqueda);
    selProd.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            procesarBusqueda(e);
        }
    });

    function agregarATabla(opt) {
        const id   = opt.dataset.id;
        const nombre = opt.dataset.nombre;
        const stock  = parseFloat(opt.dataset.stock);
        const unidad = opt.dataset.unidad;

        if (document.getElementById('filaVacia')) document.getElementById('filaVacia').remove();

        // Evitar duplicados
        const existe = document.querySelector(`input[name="producto_id[]"][value="${id}"]`);
        if (existe) {
            // Si ya existe, enfocar su input de cantidad e incrementar visualmente?
            const tr = existe.closest('tr');
            const inp = tr.querySelector('input[name="cantidad[]"]');
            inp.focus();
            inp.select();
            Swal.fire({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, 
                icon: 'info', title: 'El producto ya está en la lista'
            });
            return;
        }

        const tr = document.createElement('tr');
        tr.className = 'animate__animated animate__fadeIn bg-light';
        setTimeout(() => tr.classList.remove('bg-light'), 800); // Efecto visual

        tr.innerHTML = `
            <td>
                <div class="fw-bold text-primary"><i class="bi bi-box-seam me-1"></i> ${nombre}</div>
                <input type="hidden" name="producto_id[]" value="${id}">
            </td>
            <td class="text-center">
                <span class="badge bg-secondary">${stock.toFixed(2)} ${unidad}</span>
            </td>
            <td>
                <input type="number" name="cantidad[]" class="form-control form-control-sm text-end fw-bold" 
                       value="1" step="0.001" min="0.001" max="${stock}" required
                       oninput="validarCant(this, ${stock})">
                <div class="invalid-feedback" style="font-size: 0.7rem; display: none;">Excede disponible</div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()">
                    <i class="bi bi-trash fs-5"></i>
                </button>
            </td>
        `;
        tabla.prepend(tr); // Agregar arriba de la lista
        
        // Enfocar el input de cantidad recien agregado
        const newInp = tr.querySelector('input[name="cantidad[]"]');
        newInp.focus();
        newInp.select();
    }
    
    // Validar cantidad dinámicamente
    window.validarCant = function(input, maxStock) {
        const val = parseFloat(input.value);
        if (val > maxStock) {
            input.classList.add('is-invalid');
            input.nextElementSibling.style.display = 'block';
            input.value = maxStock; // Auto-corregir al máximo permitido
            setTimeout(() => {
                input.classList.remove('is-invalid');
                input.nextElementSibling.style.display = 'none';
            }, 2000);
        } else {
            input.classList.remove('is-invalid');
            input.nextElementSibling.style.display = 'none';
        }
    };

    // Si ya hay un origen seleccionado (re-poblado por error), cargar sus productos
    if (selOrigen.value) {
        selOrigen.dispatchEvent(new Event('change'));
    }
});
</script>
