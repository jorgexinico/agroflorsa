/**
 * producciones.js — Lógica de la vista Nueva Producción
 */

document.addEventListener('DOMContentLoaded', () => {

    const tbody = document.getElementById('produccion-items');
    const btnAdd = document.getElementById('btn-add-produccion');
    const form   = document.querySelector('form');

    if (!tbody || !btnAdd) return;

    btnAdd.addEventListener('click', () => addRowProduccion(tbody));

    // ── Buscador de productos con datalist ───────────────
    const searchInput = document.getElementById('buscar-producto-produccion');
    const dataList = document.getElementById('lista-productos-produccion');
    
    if (searchInput && dataList) {
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                procesarBusqueda(e);
            }
        });

        function procesarBusqueda(e) {
            const val = e.target.value.trim().toLowerCase();
            if (!val) return;
            const options = dataList.options;
            
            for (let i = 0; i < options.length; i++) {
                const optValue = options[i].value.trim().toLowerCase();
                const optSku = (options[i].dataset.sku || options[i].getAttribute('data-sku') || '').trim().toLowerCase();
                
                if (optValue === val || (optSku !== '' && optSku === val)) {
                    const id = options[i].dataset.id || options[i].getAttribute('data-id');
                    if (id) {
                        e.target.value = '';
                        addRowProduccion(tbody, id);
                        searchInput.blur();
                        setTimeout(() => searchInput.focus(), 50);
                    }
                    break;
                }
            }
        }

        searchInput.addEventListener('input', procesarBusqueda);
        searchInput.addEventListener('change', procesarBusqueda);
    }

    tbody.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remove-row')) {
            e.target.closest('tr').remove();
            recalcTotalProduccion();
        }
    });

    tbody.addEventListener('input', (e) => {
        if (e.target.matches('.produccion-cantidad, .produccion-costo')) {
            recalcRowProduccion(e.target.closest('tr'));
            recalcTotalProduccion();
        }
    });

    tbody.addEventListener('change', (e) => {
        if (e.target.matches('select[name="producto_id[]"]')) {
            const select = e.target;
            const tr = select.closest('tr');
            
            actualizarFechaVenc(select);
            
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const costo = parseFloat(selectedOpt.dataset.costo || 0).toFixed(2);
                const pub = parseFloat(selectedOpt.dataset.pub || 0).toFixed(2);
                const may = parseFloat(selectedOpt.dataset.may || 0).toFixed(2);
                
                tr.querySelector('.produccion-costo').value = costo;
                tr.querySelector('.produccion-pub').value = pub;
                tr.querySelector('.produccion-may').value = may;
                
                const titlePub = tr.querySelector('.title-pub');
                if (titlePub) titlePub.textContent = 'Act: Q' + pub;
                
                const titleMay = tr.querySelector('.title-may');
                if (titleMay) titleMay.textContent = 'Act: Q' + may;
            }
        }
    });
});

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
                   title="Fecha de vencimiento (Opcional)">
            <div class="invalid-feedback" style="font-size:.72rem">Opcional</div>
          </div>`;
    } else {
        celda.innerHTML = `<input type="hidden" name="fecha_vencimiento[]" value=""><span class="text-muted small">N/A</span>`;
    }
}

function addRowProduccion(tbody, initialProductId = null) {
    const select = document.getElementById('productos-template');
    if (!select) return;

    const options = Array.from(select.options)
        .map(o => {
            const selected = (initialProductId && String(o.value) === String(initialProductId)) ? 'selected' : '';
            return `<option value="${o.value}" data-sku="${o.dataset.sku || ''}" data-maneja-vencimiento="${o.dataset.manejaVencimiento || 0}" data-costo="${o.dataset.costo || 0}" data-pub="${o.dataset.pub || 0}" data-may="${o.dataset.may || 0}" ${selected}>${o.text}</option>`;
        })
        .join('');

    const row = `
    <tr>
      <td><select name="producto_id[]" class="form-select form-select-sm" required><option value="">Seleccione...</option>${options}</select></td>
      <td><input type="number" name="cantidad[]" class="form-control form-control-sm produccion-cantidad border-warning" min="0.001" step="any" value="1" required></td>
      <td>
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-warning text-dark border-warning">Q</span>
            <input type="number" name="costo_unitario[]" class="form-control form-control-sm produccion-costo border-warning" min="0" step="0.0001" value="0" required>
        </div>
      </td>
      <td>
        <div class="input-group input-group-sm">
            <span class="input-group-text">Q</span>
            <input type="number" name="precio_publico[]" class="form-control form-control-sm produccion-pub" min="0" step="0.01" required>
        </div>
        <div class="x-small text-muted mt-1 title-pub">Act: Q0.00</div>
      </td>
      <td>
        <div class="input-group input-group-sm">
            <span class="input-group-text">Q</span>
            <input type="number" name="precio_mayorista[]" class="form-control form-control-sm produccion-may" min="0" step="0.01" required>
        </div>
        <div class="x-small text-muted mt-1 title-may">Act: Q0.00</div>
      </td>
      <td class="celda-fecha-venc"><input type="hidden" name="fecha_vencimiento[]" value=""><span class="text-muted small">N/A</span></td>
      <td class="text-end fw-semibold text-success subtotal-cell">Q 0.00</td>
      <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
    </tr>`;

    tbody.insertAdjacentHTML('beforeend', row);

    const newRow = tbody.lastElementChild;
    const newSelect = newRow.querySelector('select[name="producto_id[]"]');
    
    if (newSelect) {
        if (initialProductId) {
            newSelect.dispatchEvent(new Event('change', { bubbles: true }));
            actualizarFechaVenc(newSelect);
            setTimeout(() => newRow.querySelector('.produccion-cantidad')?.focus(), 50);
        }
    }
}

function recalcRowProduccion(tr) {
    const qty   = parseFloat(tr.querySelector('.produccion-cantidad')?.value) || 0;
    const costo = parseFloat(tr.querySelector('.produccion-costo')?.value)    || 0;
    const cell  = tr.querySelector('.subtotal-cell');
    if (cell) cell.textContent = 'Q ' + (qty * costo).toFixed(2);
}

function recalcTotalProduccion() {
    let total = 0;
    document.querySelectorAll('#produccion-items .subtotal-cell').forEach(c => {
        total += parseFloat(c.textContent.replace('Q ', '')) || 0;
    });
    const el = document.getElementById('produccion-total');
    if (el) el.textContent = 'Q ' + total.toFixed(2);
}
