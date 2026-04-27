<?php // views/traslados/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">
    <?= count($traslados) ?> traslados registrados
  </p>
  <a href="<?= $base ?>/traslados/crear" class="btn btn-info btn-sm text-white fw-bold">
    <i class="bi bi-arrow-left-right me-1"></i>Nuevo Traslado
  </a>
</div>

<div class="card border-info border-opacity-50">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-info opacity-75">
        <tr>
          <th>ID</th>
          <th>Fecha</th>
          <th>Origen</th>
          <th>Destino</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($traslados as $t): ?>
        <tr class="tr-clickable" data-traslado-id="<?= $t['id'] ?>" onclick="toggleDetalle(<?= $t['id'] ?>, this)">
          <td>#<?= $t['id'] ?></td>
          <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($t['fecha'])) ?></td>
          <td><span class="badge bg-secondary bg-opacity-10 text-secondary border px-2"><?= s($t['origen']) ?></span></td>
          <td><span class="badge bg-primary bg-opacity-10 text-primary border px-2"><?= s($t['destino']) ?></span></td>
          <td>
            <?php if ($t['estado'] === 'enviado'): ?>
                <span class="badge bg-warning text-dark">ENVIADO (PENDIENTE)</span>
            <?php elseif ($t['estado'] === 'rechazado'): ?>
                <span class="badge bg-danger">RECHAZADO</span>
            <?php else: ?>
                <span class="badge bg-success">RECIBIDO</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($traslados)): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No hay traslados registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Variables JS para permisos -->
<script>
    const USR_ROL = "<?= $usuarioRol ?>";
    const USR_SUCURSAL = parseInt("<?= $sucursalId ?>") || 0;
</script>

<style>
.tr-clickable {
    cursor: pointer;
    transition: background-color 0.2s;
}
.tr-clickable:hover {
    background-color: var(--bs-info-bg-subtle);
}
.tr-expanded {
    background-color: var(--bs-info-bg-subtle) !important;
}
.detalle-row td {
    padding: 0;
    border-bottom: none;
}
.detalle-container {
    padding: 1rem;
    background-color: var(--bs-body-bg);
    border-top: 1px solid var(--bs-border-color-translucent);
    box-shadow: inset 0 3px 6px -3px rgba(0,0,0,.1);
    animation: slideDown 0.3s ease-out;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
async function toggleDetalle(id, btnClicked) {
    const currentRow = document.querySelector(`tr[data-traslado-id="${id}"]`);
    const detailsId = `detalle-${id}`;
    let detailsRow = document.getElementById(detailsId);

    if (detailsRow) {
        detailsRow.remove();
        currentRow.classList.remove('tr-expanded');
        return;
    }

    document.querySelectorAll('.detalle-row').forEach(row => row.remove());
    document.querySelectorAll('.tr-expanded').forEach(row => row.classList.remove('tr-expanded'));

    currentRow.classList.add('tr-expanded');

    detailsRow = document.createElement('tr');
    detailsRow.id = detailsId;
    detailsRow.className = 'detalle-row';
    detailsRow.innerHTML = `<td colspan="5"><div class="detalle-container text-center text-muted"><div class="spinner-border spinner-border-sm me-2 text-info" role="status"></div>Cargando detalle...</div></td>`;
    currentRow.insertAdjacentElement('afterend', detailsRow);

    try {
        const res = await fetch(`<?= $base ?>/traslados/detalle-ajax?id=${id}`);
        const data = await res.json();
        
        if (!data.ok) {
            Swal.fire('Error', data.error || 'Error al obtener detalles', 'error');
            detailsRow.remove();
            currentRow.classList.remove('tr-expanded');
            return;
        }

        const traslado = data.traslado;
        const detalle = data.detalle;

        let productosHTML = '';
        if (detalle.length > 0) {
            productosHTML = `
                <table class="table table-sm table-bordered mt-2 mb-0 bg-white shadow-sm">
                    <thead class="table-info opacity-75">
                        <tr>
                            <th>Producto</th>
                            <th class="text-end">Cantidad Enviada</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${detalle.map(d => `
                            <tr>
                                <td class="fw-semibold">${d.producto_nombre || '—'} <span class="badge bg-secondary ms-1">${d.unidad_abreviatura || ''}</span></td>
                                <td class="text-end fw-bold">${parseFloat(d.cantidad).toFixed(3)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }

        // Evaluar permisos para botones
        const puedeAccionar = traslado.estado === 'enviado' && (USR_ROL === 'admin' || USR_SUCURSAL === parseInt(traslado.sucursal_destino_id));
        
        let actionsHTML = '';
        if (puedeAccionar) {
            actionsHTML = `
                <div class="mt-3 p-3 bg-light border rounded d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div>
                        <h6 class="mb-1 text-dark"><i class="bi bi-exclamation-circle text-warning me-1"></i>Acciones Pendientes</h6>
                        <small class="text-muted">Por favor, revisa la mercadería física antes de aceptar el traslado.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="<?= $base ?>/traslados/rechazar" method="POST" class="form-rechazar m-0">
                            <input type="hidden" name="id" value="${traslado.id}">
                            <button type="submit" class="btn btn-danger text-white"><i class="bi bi-x-circle me-1"></i>Rechazar</button>
                        </form>
                        <form action="<?= $base ?>/traslados/recibir" method="POST" class="form-recibir m-0">
                            <input type="hidden" name="id" value="${traslado.id}">
                            <button type="submit" class="btn btn-success text-white"><i class="bi bi-check-circle me-1"></i>Recibir Todo</button>
                        </form>
                    </div>
                </div>
            `;
        }

        detailsRow.innerHTML = `
            <td colspan="5">
                <div class="detalle-container">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-info text-darken-2 mb-2"><i class="bi bi-arrow-left-right me-2"></i>Detalle de Traslado #${traslado.id}</h6>
                            <div class="small text-muted mb-1"><strong>Origen:</strong> ${traslado.origen}</div>
                            <div class="small text-muted mb-1"><strong>Destino:</strong> ${traslado.destino}</div>
                            <div class="small text-muted mb-1"><strong>Fecha:</strong> ${new Date(traslado.fecha).toLocaleString()}</div>
                            ${traslado.nota ? `<div class="small text-muted mt-2 border-start border-3 border-info ps-2"><em>"${traslado.nota}"</em></div>` : ''}
                        </div>
                    </div>
                    
                    ${productosHTML}
                    ${actionsHTML}
                    
                    <div class="mt-3 text-end d-none d-sm-block">
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleDetalle(${traslado.id})"><i class="bi bi-chevron-up me-1"></i>Cerrar Detalle</button>
                    </div>
                </div>
            </td>
        `;

        // Atar eventos de SweetAlert
        const formRecibir = detailsRow.querySelector('.form-recibir');
        if (formRecibir) {
            formRecibir.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Confirmar Recepción?',
                    text: '¿Confirmas que has recibido todos estos productos y están completos? Esto sumará el inventario a tu bodega.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, recibir todo',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) formRecibir.submit();
                });
            });
        }

        const formRechazar = detailsRow.querySelector('.form-rechazar');
        if (formRechazar) {
            formRechazar.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Rechazar Traslado?',
                    text: 'Los productos serán devueltos virtualmente al inventario de la sucursal de ORIGEN.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, rechazar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) formRechazar.submit();
                });
            });
        }

    } catch (err) {
        console.error(err);
        detailsRow.innerHTML = `<td colspan="5"><div class="alert alert-danger mb-0 rounded-0"><i class="bi bi-x-circle me-1"></i>Error de conexión al cargar detalles.</div></td>`;
    }
}
</script>
