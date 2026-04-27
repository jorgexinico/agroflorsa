<?php // views/producciones/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">
    <?= count($producciones) ?> producciones registradas
    <?php if (!empty($sucursal_actual)): ?>
      — <span class="fw-semibold text-dark"><?= s($sucursal_actual) ?></span>
    <?php endif; ?>
  </p>
  <a href="<?= $base ?>/producciones/crear" class="btn btn-warning btn-sm text-dark fw-bold">
    <i class="bi bi-box-seam me-1"></i>Nueva Producción
  </a>
</div>
<div class="card border-warning border-opacity-50">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-warning"><tr><th>Fecha</th><th>Usuario</th><th class="d-none-mobile">Sucursal Destino</th><th class="d-none-mobile">Estado</th><th class="text-end">Costo Total</th></tr></thead>
      <tbody>
        <?php foreach ($producciones as $p): ?>
        <tr class="tr-clickable" data-produccion-id="<?= $p['id'] ?>" onclick="toggleDetalle(<?= $p['id'] ?>, this)">
          <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></td>
          <td>
            <i class="bi bi-person-fill text-muted me-1"></i><?= s($p['usuario_nombre'] ?? 'Sistema') ?>
            <div class="d-block d-sm-none small text-muted"><?= s($p['estado']) ?></div>
          </td>
          <td class="d-none-mobile fw-semibold"><?= s($p['sucursal_nombre']) ?></td>
          <td class="d-none-mobile"><span class="badge bg-<?= $p['estado']==='terminada'?'success':'secondary' ?>"><?= s($p['estado']) ?></span></td>
          <td class="text-end fw-semibold text-success"><?= formatMoney((float)$p['total']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($producciones)): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No hay producciones registradas.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
.tr-clickable {
    cursor: pointer;
    transition: background-color 0.2s;
}
.tr-clickable:hover {
    background-color: var(--bs-warning-bg-subtle);
}
.tr-expanded {
    background-color: var(--bs-warning-bg-subtle) !important;
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
    const currentRow = document.querySelector(`tr[data-produccion-id="${id}"]`);
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
    detailsRow.innerHTML = `<td colspan="5"><div class="detalle-container text-center text-muted"><div class="spinner-border spinner-border-sm me-2 text-warning" role="status"></div>Cargando detalle...</div></td>`;
    currentRow.insertAdjacentElement('afterend', detailsRow);

    try {
        const res = await fetch(`<?= $base ?>/producciones/detalle-ajax?id=${id}`);
        const data = await res.json();
        
        if (!data.ok) {
            Swal.fire('Error', data.error || 'Error al obtener detalles', 'error');
            detailsRow.remove();
            currentRow.classList.remove('tr-expanded');
            return;
        }

        const produccion = data.produccion;
        const detalle = data.detalle;

        let productosHTML = '';
        if (detalle.length > 0) {
            productosHTML = `
                <table class="table table-sm table-bordered mt-2 mb-0 bg-white shadow-sm">
                    <thead class="table-warning opacity-75">
                        <tr>
                            <th>Producto Producido</th>
                            <th>Lote</th>
                            <th class="text-end">Cant.</th>
                            <th class="text-end">Costo U.</th>
                            <th class="text-end">Costo Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${detalle.map(d => `
                            <tr>
                                <td class="fw-semibold">${d.producto_nombre || '—'} <span class="badge bg-secondary ms-1">${d.unidad_abreviatura || ''}</span></td>
                                <td><span class="badge bg-info text-dark">${d.lote_codigo || '—'}</span></td>
                                <td class="text-end">${parseFloat(d.cantidad).toFixed(3)}</td>
                                <td class="text-end">Q${parseFloat(d.costo_unitario).toFixed(2)}</td>
                                <td class="text-end fw-semibold text-success">Q${parseFloat(d.subtotal).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">COSTO TOTAL PRODUCCIÓN</td>
                            <td class="text-end fw-bold text-success">Q${parseFloat(produccion.total).toFixed(2)}</td>
                        </tr>
                    </tfoot>
                </table>
            `;
        }

        detailsRow.innerHTML = `
            <td colspan="5">
                <div class="detalle-container">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-warning text-darken-2 mb-2"><i class="bi bi-box-seam me-2"></i>Detalle de Producción #${produccion.id}</h6>
                            <div class="small text-muted mb-1"><strong>Destino:</strong> ${produccion.sucursal_nombre}</div>
                            <div class="small text-muted mb-1"><strong>Fecha:</strong> ${new Date(produccion.fecha).toLocaleString()}</div>
                            ${produccion.observacion ? `<div class="small text-muted mt-2 border-start border-3 border-warning ps-2"><em>"${produccion.observacion}"</em></div>` : ''}
                        </div>
                    </div>
                    
                    ${productosHTML}
                    
                    <div class="mt-3 text-end d-none d-sm-block">
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleDetalle(${produccion.id})"><i class="bi bi-chevron-up me-1"></i>Cerrar Detalle</button>
                    </div>
                </div>
            </td>
        `;

    } catch (err) {
        console.error(err);
        detailsRow.innerHTML = `<td colspan="5"><div class="alert alert-danger mb-0 rounded-0"><i class="bi bi-x-circle me-1"></i>Error de conexión al cargar detalles.</div></td>`;
    }
}
</script>
