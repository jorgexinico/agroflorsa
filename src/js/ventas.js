/**
 * ventas.js — Lógica de la vista Nueva Venta
 * Tabla dinámica de productos: agregar filas, recalcular subtotal y total.
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Tabla dinámica de productos ───────────────────────
    const tbody = document.getElementById('venta-items');
    const btnAdd = document.getElementById('btn-add-item');

    if (!tbody || !btnAdd) return;

    btnAdd.addEventListener('click', () => addRowVenta(tbody));

    tbody.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remove-row')) {
            e.target.closest('tr').remove();
            recalcTotalVenta();
        }
    });

    tbody.addEventListener('input', (e) => {
        if (e.target.matches('.venta-cantidad, .venta-precio')) {
            recalcRowVenta(e.target.closest('tr'));
            recalcTotalVenta();
        }
    });

    // ── Ocultar/mostrar método de pago ────────────────────
    const tipoPago = document.getElementById('tipo-pago');
    const metodoPagoWrap = document.getElementById('metodo-pago-wrap');
    tipoPago?.addEventListener('change', function () {
        if (metodoPagoWrap) {
            metodoPagoWrap.style.display = this.value === 'contado' ? '' : 'none';
        }
    });

    // Agregar primera fila por defecto
    addRowVenta(tbody);
});

// ── Agregar fila ─────────────────────────────────────────
function addRowVenta(tbody) {
    const select = document.getElementById('productos-template');
    if (!select) return;

    const options = Array.from(select.options)
        .map(o => `<option value="${o.value}" data-precio="${o.dataset.precio || 0}" data-sku="${o.dataset.sku || ''}">${o.text}</option>`)
        .join('');

    const row = `
    <tr>
      <td><select name="producto_id[]" class="form-select form-select-sm venta-select" required>${options}</select></td>
      <td><input type="number" name="cantidad[]" class="form-control form-control-sm venta-cantidad" min="0.001" step="0.001" value="1" required></td>
      <td><input type="number" name="precio_unitario[]" class="form-control form-control-sm venta-precio" min="0" step="0.01" value="0" required></td>
      <td class="text-end fw-semibold subtotal-cell">Q 0.00</td>
      <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
    </tr>`;

    tbody.insertAdjacentHTML('beforeend', row);

    // Auto-fill precio al seleccionar producto
    const lastRow = tbody.lastElementChild;
    const sel = lastRow.querySelector('.venta-select');
    if (sel) {
        sel.addEventListener('change', () => {
            const precio = sel.selectedOptions[0]?.dataset.precio || 0;
            lastRow.querySelector('.venta-precio').value = precio;
            recalcRowVenta(lastRow);
            recalcTotalVenta();
        });
    }
}

// ── Recalcular fila ──────────────────────────────────────
function recalcRowVenta(tr) {
    const qty = parseFloat(tr.querySelector('.venta-cantidad')?.value) || 0;
    const precio = parseFloat(tr.querySelector('.venta-precio')?.value) || 0;
    const cell = tr.querySelector('.subtotal-cell');
    if (cell) cell.textContent = 'Q ' + (qty * precio).toFixed(2);
}

// ── Recalcular total ─────────────────────────────────────
function recalcTotalVenta() {
    let total = 0;
    document.querySelectorAll('#venta-items .subtotal-cell').forEach(c => {
        total += parseFloat(c.textContent.replace('Q ', '')) || 0;
    });
    const el = document.getElementById('venta-total');
    if (el) el.textContent = 'Q ' + total.toFixed(2);
}
