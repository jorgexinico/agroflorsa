/**
 * compras.js — Lógica de la vista Nueva Compra
 * Tabla dinámica de productos: agregar filas, recalcular subtotal y total.
 */

document.addEventListener('DOMContentLoaded', () => {

    const tbody = document.getElementById('compra-items');
    const btnAdd = document.getElementById('btn-add-compra');

    if (!tbody || !btnAdd) return;

    btnAdd.addEventListener('click', () => addRowCompra(tbody));

    tbody.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remove-row')) {
            e.target.closest('tr').remove();
            recalcTotalCompra();
        }
    });

    tbody.addEventListener('input', (e) => {
        if (e.target.matches('.compra-cantidad, .compra-costo')) {
            recalcRowCompra(e.target.closest('tr'));
            recalcTotalCompra();
        }
    });

    // Primera fila por defecto
    addRowCompra(tbody);
});

// ── Agregar fila ─────────────────────────────────────────
function addRowCompra(tbody) {
    const select = document.getElementById('productos-template');
    if (!select) return;

    const options = Array.from(select.options)
        .map(o => `<option value="${o.value}" data-sku="${o.dataset.sku || ''}">${o.text}</option>`)
        .join('');

    const row = `
    <tr>
      <td><select name="producto_id[]" class="form-select form-select-sm" required>${options}</select></td>
      <td><input type="number" name="cantidad[]" class="form-control form-control-sm compra-cantidad" min="0.001" step="0.001" value="1" required></td>
      <td><input type="number" name="costo_unitario[]" class="form-control form-control-sm compra-costo" min="0" step="0.0001" value="0" required></td>
      <td class="text-end fw-semibold subtotal-cell">Q 0.00</td>
      <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
    </tr>`;

    tbody.insertAdjacentHTML('beforeend', row);
}

// ── Recalcular fila ──────────────────────────────────────
function recalcRowCompra(tr) {
    const qty = parseFloat(tr.querySelector('.compra-cantidad')?.value) || 0;
    const costo = parseFloat(tr.querySelector('.compra-costo')?.value) || 0;
    const cell = tr.querySelector('.subtotal-cell');
    if (cell) cell.textContent = 'Q ' + (qty * costo).toFixed(2);
}

// ── Recalcular total ─────────────────────────────────────
function recalcTotalCompra() {
    let total = 0;
    document.querySelectorAll('#compra-items .subtotal-cell').forEach(c => {
        total += parseFloat(c.textContent.replace('Q ', '')) || 0;
    });
    const el = document.getElementById('compra-total');
    if (el) el.textContent = 'Q ' + total.toFixed(2);
}
