<?php // views/compras/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">
    <?= count($compras) ?> compras registradas
    <?php if (!empty($sucursal_actual)): ?>
      — <span class="fw-semibold text-dark"><?= s($sucursal_actual) ?></span>
    <?php endif; ?>
  </p>
  <a href="/<?= $_ENV['APP_NAME'] ?>/compras/crear" class="btn btn-success btn-sm">
    <i class="bi bi-plus-circle me-1"></i>Nueva compra
  </a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Fecha</th><th>Proveedor</th><th class="d-none-mobile">Sucursal</th><th class="d-none-mobile">Estado</th><th class="text-end">Total</th></tr></thead>
      <tbody>
        <?php foreach ($compras as $c): ?>
        <tr class="tr-clickable" data-compra-id="<?= $c['id'] ?>" onclick="toggleDetalle(<?= $c['id'] ?>, this)">
          <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($c['fecha'])) ?></td>
          <td>
            <?= s($c['proveedor_nombre'] ?? 'Sin proveedor') ?>
            <div class="d-block d-sm-none small text-muted"><?= s($c['estado']) ?></div>
          </td>
          <td class="d-none-mobile"><?= s($c['sucursal_nombre']) ?></td>
          <td class="d-none-mobile"><span class="badge bg-<?= $c['estado']==='recibida'?'success':'secondary' ?>"><?= s($c['estado']) ?></span></td>
          <td class="text-end fw-semibold"><?= formatMoney((float)$c['total']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($compras)): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No hay compras registradas.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
/* Estilos para la fila expandible (acordeón) */
.tr-clickable {
    cursor: pointer;
    transition: background-color 0.2s;
}
.tr-clickable:hover {
    background-color: var(--bs-gray-100);
}
.tr-expanded {
    background-color: var(--bs-success-bg-subtle) !important;
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
    const tableBody = document.querySelector('tbody');
    const currentRow = document.querySelector(`tr[data-compra-id="${id}"]`);
    const detailsId = `detalle-${id}`;
    let detailsRow = document.getElementById(detailsId);

    // Si ya existe y está abierto, cerrarlo
    if (detailsRow) {
        detailsRow.remove();
        currentRow.classList.remove('tr-expanded');
        return;
    }

    // Cerrar cualquier otro detalle abierto (opcional, acordeón único)
    document.querySelectorAll('.detalle-row').forEach(row => row.remove());
    document.querySelectorAll('.tr-expanded').forEach(row => row.classList.remove('tr-expanded'));

    currentRow.classList.add('tr-expanded');

    // Crear la nueva fila visualmente de carga
    detailsRow = document.createElement('tr');
    detailsRow.id = detailsId;
    detailsRow.className = 'detalle-row';
    detailsRow.innerHTML = `<td colspan="5"><div class="detalle-container text-center text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Cargando detalle...</div></td>`;
    currentRow.insertAdjacentElement('afterend', detailsRow);

    try {
        const res = await fetch(`/<?= $_ENV['APP_NAME'] ?>/compras/detalle-ajax?id=${id}`);
        const data = await res.json();
        
        if (!data.ok) {
            Swal.fire('Error', data.error || 'Error al obtener detalles', 'error');
            detailsRow.remove();
            currentRow.classList.remove('tr-expanded');
            return;
        }

        const compra = data.compra;
        const detalle = data.detalle;
        const cxp = data.cxp;

        // Construir la tabla de productos
        let productosHTML = '';
        if (detalle.length > 0) {
            productosHTML = `
                <table class="table table-sm table-bordered mt-2 mb-0 bg-white shadow-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Unidad</th>
                            <th class="text-end">Cant.</th>
                            <th class="text-end">Costo U.</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${detalle.map(d => `
                            <tr>
                                <td>${d.producto_nombre || '—'}</td>
                                <td><span class="badge bg-secondary">${d.unidad_abreviatura || ''}</span></td>
                                <td class="text-end">${parseFloat(d.cantidad).toFixed(3)}</td>
                                <td class="text-end">Q${parseFloat(d.costo_unitario).toFixed(2)}</td>
                                <td class="text-end fw-semibold text-success">Q${parseFloat(d.subtotal).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">TOTAL COMPRA</td>
                            <td class="text-end fw-bold text-success">Q${parseFloat(compra.total).toFixed(2)}</td>
                        </tr>
                    </tfoot>
                </table>
            `;
        } else {
            productosHTML = `<div class="alert alert-warning py-2 mb-0 mt-2">No se encontraron productos en esta compra.</div>`;
        }

        // Construir información de CxP si es a crédito
        let cxpHTML = '';
        if (compra.tipo_pago === 'credito') {
            if (cxp) {
                let badgeCxp = 'bg-danger';
                if (cxp.estado === 'pagada') badgeCxp = 'bg-success';
                else if (cxp.estado === 'parcial') badgeCxp = 'bg-warning text-dark';
                
                cxpHTML = `
                <div class="mt-3 p-2 bg-light border rounded">
                    <h6 class="mb-2"><i class="bi bi-wallet2 me-2"></i>Cuenta por Pagar</h6>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge ${badgeCxp}">${cxp.estado.toUpperCase()}</span>
                        <span class="small text-muted">Abonado: <strong>Q${parseFloat(cxp.pagado).toFixed(2)}</strong></span>
                        <span class="small text-muted">Saldo Pendiente: <strong class="text-danger">Q${parseFloat(cxp.saldo).toFixed(2)}</strong></span>
                        ${ (cxp.estado === 'pendiente' || cxp.estado === 'parcial') ? 
                          `<a href="/<?= $_ENV['APP_NAME'] ?>/cuentas-pagar/detalle?id=${cxp.id}" class="btn btn-sm btn-outline-primary ms-auto"><i class="bi bi-cash-coin me-1"></i>Ver CxP</a>` 
                          : '' }
                    </div>
                </div>`;
            } else {
                cxpHTML = `<div class="mt-3 text-muted small"><i class="bi bi-info-circle me-1"></i>Compra al crédito pero no se encontró la CxP asociada.</div>`;
            }
        }

        // Tipo de Pago (badge)
        const badgePago = compra.tipo_pago === 'contado' ? 'bg-success' : 'bg-warning text-dark';

        detailsRow.innerHTML = `
            <td colspan="5">
                <div class="detalle-container">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success mb-2"><i class="bi bi-receipt me-2"></i>Detalle de Compra #${compra.id}</h6>
                            <div class="small text-muted mb-1"><strong>Proveedor:</strong> ${compra.proveedor_nombre || 'Sin proveedor'} ${compra.proveedor_nit ? `(NIT: ${compra.proveedor_nit})` : ''}</div>
                            <div class="small text-muted mb-1"><strong>Fecha:</strong> ${new Date(compra.fecha).toLocaleString()}</div>
                            <div class="small text-muted mb-1"><strong>Tipo de Pago:</strong> <span class="badge ${badgePago}">${compra.tipo_pago.toUpperCase()}</span></div>
                            ${compra.observacion ? `<div class="small text-muted mt-2 border-start border-3 border-secondary ps-2"><em>"${compra.observacion}"</em></div>` : ''}
                        </div>
                    </div>
                    
                    ${productosHTML}
                    ${cxpHTML}
                    
                    <div class="mt-3 text-end d-none d-sm-block">
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleDetalle(${compra.id})"><i class="bi bi-chevron-up me-1"></i>Cerrar Detalle</button>
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
