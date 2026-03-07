/**
 * compras.js — Lógica de la vista Nueva Compra
 * Tabla dinámica de productos: agregar filas, recalcular subtotal y total.
 * La celda de fecha de vencimiento se vuelve OBLIGATORIA cuando el producto lo requiere.
 */

document.addEventListener('DOMContentLoaded', () => {

    const tbody = document.getElementById('compra-items');
    const btnAdd = document.getElementById('btn-add-compra');
    const form   = document.querySelector('form');

    if (!tbody || !btnAdd) return;

    btnAdd.addEventListener('click', () => addRowCompra(tbody));

    // Quitar fila
    tbody.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remove-row')) {
            e.target.closest('tr').remove();
            recalcTotalCompra();
        }
    });

    // Recalcular subtotal al cambiar cantidad o costo
    tbody.addEventListener('input', (e) => {
        if (e.target.matches('.compra-cantidad, .compra-costo')) {
            recalcRowCompra(e.target.closest('tr'));
            recalcTotalCompra();
        }
    });

    // Actualizar celda de fecha vencimiento al cambiar producto
    tbody.addEventListener('change', (e) => {
        if (e.target.matches('select[name="producto_id[]"]')) {
            actualizarFechaVenc(e.target);
        }
    });

    // Validación extra antes de enviar: todos los productos con vencimiento deben tener fecha
    if (form) {
        form.addEventListener('submit', (e) => {
            let valido = true;
            tbody.querySelectorAll('tr').forEach(tr => {
                const sel      = tr.querySelector('select[name="producto_id[]"]');
                const fechaInp = tr.querySelector('input[name="fecha_vencimiento[]"][type="date"]');
                if (!sel || !fechaInp) return;
                const opt = sel.options[sel.selectedIndex];
                const req = parseInt(opt?.dataset.manejaVencimiento || '0', 10);
                if (req && !fechaInp.value) {
                    fechaInp.classList.add('is-invalid');
                    valido = false;
                } else if (fechaInp) {
                    fechaInp.classList.remove('is-invalid');
                }
            });
            if (!valido) {
                e.preventDefault();
                // Scroll al primer campo inválido
                const first = tbody.querySelector('.is-invalid');
                if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    // Primera fila por defecto
    addRowCompra(tbody);
});

// ── Actualizar celda de fecha vencimiento ─────────────────
function actualizarFechaVenc(select) {
    const tr = select.closest('tr');
    const selectedOpt = select.options[select.selectedIndex];
    const manejaVenc = parseInt(selectedOpt?.dataset.manejaVencimiento || '0', 10);
    const celda = tr.querySelector('.celda-fecha-venc');
    if (!celda) return;

    if (manejaVenc) {
        celda.innerHTML = `
          <div style="position:relative">
            <input type="date" name="fecha_vencimiento[]"
                   class="form-control form-control-sm"
                   required
                   title="Fecha de vencimiento requerida para este producto">
            <div class="invalid-feedback" style="font-size:.72rem">Ingresa la fecha de venc.</div>
          </div>`;
    } else {
        celda.innerHTML = `<input type="hidden" name="fecha_vencimiento[]" value=""><span class="text-muted small">N/A</span>`;
    }
}

// ── Agregar fila ──────────────────────────────────────────
function addRowCompra(tbody) {
    const select = document.getElementById('productos-template');
    if (!select) return;

    const options = Array.from(select.options)
        .map(o => `<option value="${o.value}" data-sku="${o.dataset.sku || ''}" data-maneja-vencimiento="${o.dataset.manejaVencimiento || 0}">${o.text}</option>`)
        .join('');

    const row = `
    <tr>
      <td><select name="producto_id[]" class="form-select form-select-sm" required><option value="">Seleccione...</option>${options}</select></td>
      <td><input type="number" name="cantidad[]" class="form-control form-control-sm compra-cantidad" min="0.001" step="0.001" value="1" required></td>
      <td><input type="number" name="costo_unitario[]" class="form-control form-control-sm compra-costo" min="0" step="0.0001" value="0" required></td>
      <td class="celda-fecha-venc"><input type="hidden" name="fecha_vencimiento[]" value=""><span class="text-muted small">N/A</span></td>
      <td class="text-end fw-semibold subtotal-cell">Q 0.00</td>
      <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
    </tr>`;

    tbody.insertAdjacentHTML('beforeend', row);

    // Revisar si el primer producto por defecto ya maneja vencimiento
    const newRow = tbody.lastElementChild;
    const newSelect = newRow.querySelector('select[name="producto_id[]"]');
    if (newSelect) actualizarFechaVenc(newSelect);
}

// ── Recalcular fila ───────────────────────────────────────
function recalcRowCompra(tr) {
    const qty   = parseFloat(tr.querySelector('.compra-cantidad')?.value) || 0;
    const costo = parseFloat(tr.querySelector('.compra-costo')?.value)    || 0;
    const cell  = tr.querySelector('.subtotal-cell');
    if (cell) cell.textContent = 'Q ' + (qty * costo).toFixed(2);
}

// ── Recalcular total ──────────────────────────────────────
function recalcTotalCompra() {
    let total = 0;
    document.querySelectorAll('#compra-items .subtotal-cell').forEach(c => {
        total += parseFloat(c.textContent.replace('Q ', '')) || 0;
    });
    const el = document.getElementById('compra-total');
    if (el) el.textContent = 'Q ' + total.toFixed(2);
}
