<?php
// views/inventario/tomas/conteo.php
$base = $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '';

$pendientes = array_filter($detalles, fn($d) => $d['estado'] === 'pendiente');
$contados = array_filter($detalles, fn($d) => $d['estado'] === 'contado');
?>

<div class="d-flex flex-column h-100 bg-light" style="min-height: 100vh;">
    <!-- Topbar Específico para Conteo -->
    <header class="bg-dark text-white p-3 d-flex justify-content-between align-items-center sticky-top shadow-sm z-3">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= $base ?>/inventario/tomas" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Salir</a>
            <h4 class="mb-0 fs-5 d-none d-md-block">Conteo Físico: <?= s($sucursal->nombre) ?></h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-success fw-bold px-4 shadow-sm" onclick="finalizarToma()">
                <i class="bi bi-check2-all me-1"></i> Finalizar Inventario
            </button>
        </div>
    </header>

    <div class="container-fluid py-4 flex-grow-1 d-flex flex-column">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-white text-primary border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="buscador" class="form-control border-start-0" placeholder="Buscar por nombre o SKU..." onkeyup="filtrarProductos()">
                </div>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <button class="btn btn-lg btn-outline-primary shadow-sm bg-white" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
                    <i class="bi bi-plus-circle me-1"></i> Añadir Producto Rápido
                </button>
            </div>
        </div>

        <div class="row flex-grow-1">
            <!-- PENDIENTES -->
            <div class="col-md-6 d-flex flex-column">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-warning bg-opacity-10 text-dark fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-hourglass-split text-warning me-2"></i>Pendientes de Contar</span>
                        <span class="badge bg-warning text-dark" id="count-pendientes"><?= count($pendientes) ?></span>
                    </div>
                    <div class="card-body p-0 overflow-auto" style="height: calc(100vh - 250px);" id="lista-pendientes">
                        <?php foreach($pendientes as $p): ?>
                        <div class="producto-item p-3 border-bottom d-flex flex-column gap-2" data-nombre="<?= strtolower(s($p['producto_nombre'])) ?>" data-sku="<?= strtolower(s($p['producto_sku'])) ?>" id="prod-<?= $p['id'] ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-0 fw-bold"><?= s($p['producto_nombre']) ?></h6>
                                    <small class="text-muted">SKU: <?= s($p['producto_sku'] ?: 'N/A') ?> | Sistema: <span class="fw-bold text-dark"><?= (float)$p['stock_sistema'] ?></span> <?= s($p['unidad_nombre']) ?></small>
                                </div>
                            </div>
                            <div class="input-group input-group-sm mt-1">
                                <span class="input-group-text bg-light">Conteo:</span>
                                <input type="number" step="0.01" class="form-control text-end fs-5 fw-bold text-primary input-conteo" id="input-conteo-<?= $p['id'] ?>" value="<?= (float)$p['stock_sistema'] ?>">
                                <button class="btn btn-success px-3 fw-bold" onclick="guardarConteo(<?= $p['id'] ?>)">Confirmar</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($pendientes)): ?>
                            <div class="p-4 text-center text-muted empty-state"><i class="bi bi-emoji-smile fs-1 d-block mb-2 text-success"></i>¡Todo contado!</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- CONTADOS -->
            <div class="col-md-6 d-flex flex-column mt-4 mt-md-0">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-success bg-opacity-10 text-dark fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-check-circle text-success me-2"></i>Ya Contados</span>
                        <span class="badge bg-success" id="count-contados"><?= count($contados) ?></span>
                    </div>
                    <div class="card-body p-0 overflow-auto" style="height: calc(100vh - 250px);" id="lista-contados">
                        <?php foreach($contados as $c): ?>
                            <?= generarItemContado($c) ?>
                        <?php endforeach; ?>
                        <div class="p-4 text-center text-muted empty-state <?= count($contados) > 0 ? 'd-none' : '' ?>" id="empty-contados">Aún no has contado nada.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function generarItemContado($c) {
    $dif = (float)$c['diferencia'];
    $color = $dif == 0 ? 'success' : ($dif > 0 ? 'info' : 'danger');
    $icono = $dif == 0 ? 'check2-circle' : ($dif > 0 ? 'arrow-up-circle' : 'arrow-down-circle');
    $signo = $dif > 0 ? '+' : '';
    
    return '
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light bg-gradient item-contado" id="contado-'.$c['id'].'">
        <div>
            <h6 class="mb-0 text-dark">'.s($c['producto_nombre']).'</h6>
            <small class="text-muted">Conteo: <strong class="fs-6">'.(float)$c['conteo_fisico'].'</strong> | Sis: '.(float)$c['stock_sistema'].'</small>
        </div>
        <div class="text-end">
            <span class="badge bg-'.$color.'"><i class="bi bi-'.$icono.' me-1"></i>Dif: '.$signo.$dif.'</span>
            <div class="mt-1"><button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" onclick="editarConteo('.$c['id'].')"><i class="bi bi-pencil-square"></i> Corregir</button></div>
        </div>
    </div>';
}
?>

<!-- Modal Nuevo Producto -->
<div class="modal fade" id="modalNuevoProducto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form-nuevo-producto" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Producto (Completo)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Nombre del Producto *</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">SKU (Vacío = Auto)</label>
                        <input type="text" name="sku" class="form-control" placeholder="Generado automático">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Precio Compra</label>
                        <div class="input-group">
                            <span class="input-group-text">Q</span>
                            <input type="number" step="0.01" name="precio_compra" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Precio Venta (Público)</label>
                        <div class="input-group">
                            <span class="input-group-text">Q</span>
                            <input type="number" step="0.01" name="precio_publico" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Precio Mayorista</label>
                        <div class="input-group">
                            <span class="input-group-text">Q</span>
                            <input type="number" step="0.01" name="precio_mayorista" class="form-control" value="0">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Marca</label>
                        <select name="marca_id" class="form-select">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach($marcas as $m): ?>
                                <option value="<?= $m->id ?>"><?= s($m->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="categoria_id" class="form-select">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach($categorias as $c): ?>
                                <option value="<?= $c->id ?>"><?= s($c->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unidad (Presentación) *</label>
                        <select name="unidad_id" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach($unidades as $u): ?>
                                <option value="<?= $u->id ?>"><?= s($u->nombre) ?> (<?= s($u->abreviatura) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de Producto</label>
                        <select name="tipo" class="form-select">
                            <option value="mercaderia">Mercadería para la Venta</option>
                            <option value="materia_prima">Materia Prima</option>
                            <option value="terminado">Producto Terminado (Producción)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3 mt-md-4">
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" role="switch" name="maneja_vencimiento" id="flexSwitchCheckChecked">
                            <label class="form-check-label ms-2" for="flexSwitchCheckChecked">¿Maneja Fecha de Vencimiento?</label>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="toma_id" value="<?= $toma->id ?>">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btn-guardar-prod">Guardar y Añadir a Conteo</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// --- BUSCADOR ---
function filtrarProductos() {
    const term = document.getElementById('buscador').value.toLowerCase();
    document.querySelectorAll('#lista-pendientes .producto-item').forEach(el => {
        const n = el.dataset.nombre;
        const s = el.dataset.sku;
        if(n.includes(term) || s.includes(term)) {
            el.classList.remove('d-none');
        } else {
            el.classList.add('d-none');
        }
    });
}

// --- GUARDAR CONTEO ---
async function guardarConteo(detalle_id) {
    const input = document.getElementById(`input-conteo-${detalle_id}`);
    const valor = input.value;
    const btn = input.nextElementSibling;
    
    if(valor === '') {
        input.focus();
        return;
    }
    
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;

    try {
        const res = await fetch('<?= $base ?>/inventario/tomas/guardar-detalle', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: detalle_id, conteo: valor })
        });
        const data = await res.json();
        
        if(data.ok) {
            // Mover visualmente, pasando el stock_sistema devuelto por el servidor
            moverAContados(detalle_id, valor, data.diferencia, data.stock_sistema);
        } else {
            Swal.fire('Error', data.error || 'No se pudo guardar', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch(err) {
        Swal.fire('Error', 'Fallo de conexión', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

function moverAContados(id, conteo, dif, stockSistemaRealtime) {
    const item = document.getElementById(`prod-${id}`);
    if(!item) return;

    const nombre = item.querySelector('h6').innerText;

    dif = parseFloat(dif);
    let color = dif === 0 ? 'success' : (dif > 0 ? 'info' : 'danger');
    let icono = dif === 0 ? 'check2-circle' : (dif > 0 ? 'arrow-up-circle' : 'arrow-down-circle');
    let signo = dif > 0 ? '+' : '';

    const html = `
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light bg-gradient item-contado" id="contado-${id}" style="animation: fade-in 0.5s;">
        <div>
            <h6 class="mb-0 text-dark">${nombre}</h6>
            <small class="text-muted">Conteo: <strong class="fs-6">${parseFloat(conteo)}</strong> | Sis: ${stockSistemaRealtime}</small>
        </div>
        <div class="text-end">
            <span class="badge bg-${color}"><i class="bi bi-${icono} me-1"></i>Dif: ${signo}${dif}</span>
            <div class="mt-1"><button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" onclick="editarConteo(${id}, ${stockSistemaRealtime})"><i class="bi bi-pencil-square"></i> Corregir</button></div>
        </div>
    </div>`;

    document.getElementById('empty-contados').classList.add('d-none');
    document.getElementById('lista-contados').insertAdjacentHTML('afterbegin', html);
    
    // Remover de pendientes
    item.remove();
    actualizarContadores();
}

// Para corregir, simplemente recargamos la página o volvemos a ponerlo en pendientes.
// Como es un prototipo rápido, recargar es seguro y fácil.
function editarConteo(id) {
    // Para no recargar, podríamos hacer la inversa (mandarlo a pendientes). 
    // Pero requeriría revertir el estado en BD. Lo ideal es dejarlo ahí y abrir un modal rápido.
    Swal.fire({
        title: 'Corregir Conteo',
        input: 'number',
        inputAttributes: { step: '0.01' },
        showCancelButton: true,
        confirmButtonText: 'Actualizar',
        showLoaderOnConfirm: true,
        preConfirm: async (valor) => {
            try {
                const res = await fetch('<?= $base ?>/inventario/tomas/guardar-detalle', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id: id, conteo: valor })
                });
                return await res.json();
            } catch (error) {
                Swal.showValidationMessage(`Request failed: ${error}`);
            }
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value.ok) {
            window.location.reload(); // Recargar para sincronizar vista fácil
        }
    });
}

function actualizarContadores() {
    document.getElementById('count-pendientes').innerText = document.querySelectorAll('#lista-pendientes .producto-item').length;
    document.getElementById('count-contados').innerText = document.querySelectorAll('#lista-contados .item-contado').length;
}

// --- CREAR PRODUCTO ---
document.getElementById('form-nuevo-producto').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-guardar-prod');
    btn.disabled = true;

    try {
        const formData = new FormData(this);
        const res = await fetch('<?= $base ?>/inventario/tomas/crear-producto-rapido', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if(data.ok) {
            // Añadir a la lista de pendientes local
            const html = `
            <div class="producto-item p-3 border-bottom d-flex flex-column gap-2" data-nombre="${data.nombre.toLowerCase()}" data-sku="${(data.sku || '').toLowerCase()}" id="prod-${data.detalle_id}">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-0 fw-bold">${data.nombre} <span class="badge bg-primary ms-1">NUEVO</span></h6>
                        <small class="text-muted">SKU: ${data.sku || 'N/A'} | Sistema: <span class="fw-bold text-dark">0</span> ${data.unidad_nombre || 'UND'}</small>
                    </div>
                </div>
                <div class="input-group input-group-sm mt-1">
                    <span class="input-group-text bg-light">Conteo:</span>
                    <input type="number" step="0.01" class="form-control text-end fs-5 fw-bold text-primary input-conteo" id="input-conteo-${data.detalle_id}" value="0">
                    <button class="btn btn-success px-3 fw-bold" onclick="guardarConteo(${data.detalle_id})">Confirmar</button>
                </div>
            </div>`;
            
            const emptyState = document.querySelector('#lista-pendientes .empty-state');
            if(emptyState) emptyState.remove();

            document.getElementById('lista-pendientes').insertAdjacentHTML('afterbegin', html);
            actualizarContadores();

            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoProducto'));
            modal.hide();
            this.reset();
            
            Swal.fire({ toast:true, position:'top-end', icon:'success', title:'Añadido', showConfirmButton:false, timer:1500 });
        } else {
            Swal.fire('Error', data.error, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Fallo de conexión', 'error');
    }
    btn.disabled = false;
});

// --- FINALIZAR TOMA ---
function finalizarToma() {
    const pend = document.querySelectorAll('#lista-pendientes .producto-item').length;
    if(pend > 0) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Aún tienes ${pend} productos sin contar. Si finalizas, su stock en el sistema no se ajustará (se asume que su stock está correcto o no se contó).`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Sí, finalizar de todos modos'
        }).then((result) => {
            if (result.isConfirmed) procesarFinalizacion();
        });
    } else {
        Swal.fire({
            title: '¡Gran trabajo!',
            text: 'Has contado todos los productos. ¿Procesar ajuste de inventario?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Sí, procesar y ajustar'
        }).then((result) => {
            if (result.isConfirmed) procesarFinalizacion();
        });
    }
}

async function procesarFinalizacion() {
    Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
    try {
        const formData = new FormData();
        formData.append('toma_id', '<?= $toma->id ?>');
        const res = await fetch('<?= $base ?>/inventario/tomas/finalizar', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if(data.ok) {
            Swal.fire('¡Éxito!', 'Toma finalizada y stock ajustado.', 'success').then(() => {
                window.location.href = '<?= $base ?>/inventario/tomas';
            });
        } else {
            Swal.fire('Error', data.error, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Fallo de conexión', 'error');
    }
}

// --- AUTO-GENERAR SKU EN MODAL ---
const modalInputNombre = document.querySelector('#modalNuevoProducto input[name="nombre"]');
const modalInputSKU = document.querySelector('#modalNuevoProducto input[name="sku"]');
const modalSelectMarca = document.querySelector('#modalNuevoProducto select[name="marca_id"]');
const modalSelectCat = document.querySelector('#modalNuevoProducto select[name="categoria_id"]');
const modalSelectUnidad = document.querySelector('#modalNuevoProducto select[name="unidad_id"]');

function autoGenerarSkuModal() {
    if(!modalInputNombre.value) {
        modalInputSKU.value = '';
        return;
    }
    
    let marcaStr = 'GENE';
    if(modalSelectMarca.selectedIndex > 0) {
        let txt = modalSelectMarca.options[modalSelectMarca.selectedIndex].text.replace(/[^A-Za-z0-9]/g, '');
        if(txt) {
            marcaStr = txt.substring(0,4).toUpperCase();
            if(marcaStr.length < 4) marcaStr = marcaStr.padEnd(4, 'X');
        }
    }

    let catStr = 'XXXX';
    if(modalSelectCat.selectedIndex > 0) {
        let txt = modalSelectCat.options[modalSelectCat.selectedIndex].text.replace(/[^A-Za-z0-9]/g, '');
        if(txt) {
            catStr = txt.substring(0,4).toUpperCase();
            if(catStr.length < 4) catStr = catStr.padEnd(4, 'X');
        }
    }

    let nombreStr = 'XXXX';
    let nombreLimpio = modalInputNombre.value.replace(/[^A-Za-z0-9]/g, '');
    if (nombreLimpio.length > 0) {
        nombreStr = nombreLimpio.substring(0, 4).toUpperCase();
        if (nombreStr.length < 4) nombreStr = nombreStr.padEnd(4, 'X');
    }

    let unidadStr = 'UND';
    if(modalSelectUnidad.selectedIndex > 0) {
        let txt = modalSelectUnidad.options[modalSelectUnidad.selectedIndex].text;
        // Extraer lo que está entre paréntesis (Abreviatura)
        let match = txt.match(/\((.*?)\)/);
        if(match) unidadStr = match[1].toUpperCase();
    }

    modalInputSKU.value = `${marcaStr}-${catStr}-${nombreStr}-${unidadStr}`;
}

[modalInputNombre, modalSelectMarca, modalSelectCat, modalSelectUnidad].forEach(el => {
    el.addEventListener('input', autoGenerarSkuModal);
    el.addEventListener('change', autoGenerarSkuModal);
});

// Estilos dinámicos
const style = document.createElement('style');
style.textContent = `
    @keyframes fade-in { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .item-contado:hover { filter: brightness(0.95); transition: 0.2s; }
`;
document.head.append(style);
</script>
