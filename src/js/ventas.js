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
            const row = e.target.closest('tr');
            validateStockRow(row);
            recalcRowVenta(row);
            recalcTotalVenta();
        }
    });

    // ── GESTIÓN DE CARRITO (localStorage) ──
    const cartItems = JSON.parse(localStorage.getItem('agro_cart') || '[]');
    const btnClearLocal = document.getElementById('btn-clear-cart-local');

    if (cartItems.length > 0) {
        // Limpiar la fila inicial vacía si existe
        tbody.innerHTML = '';
        
        cartItems.forEach(item => {
            addRowVenta(tbody, item.id);
        });

        // Mostrar botón de vaciar
        if (btnClearLocal) {
            btnClearLocal.classList.remove('d-none');
            btnClearLocal.addEventListener('click', () => {
                localStorage.removeItem('agro_cart');
                window.location.reload();
            });
        }
    } else {
        // Solo agregar fila vacía inicial si no hay carrito
        addRowVenta(tbody);
    }

    // Limpiar carrito al enviar el formulario (o al tener éxito)
    const ventaForm = document.querySelector('form');
    ventaForm?.addEventListener('submit', () => {
        // Solo limpiamos si el formulario es válido
        if (ventaForm.checkValidity()) {
            localStorage.removeItem('agro_cart');
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

    // Cambiar tipo de precio en masa
    const tipoPrecioSelector = document.getElementById('tipo-precio');
    if (tipoPrecioSelector && tbody) {
        tipoPrecioSelector.addEventListener('change', () => {
            const currentTipo = tipoPrecioSelector.value;
            const selects = tbody.querySelectorAll('.venta-select');
            
            selects.forEach(sel => {
                const row = sel.closest('tr');
                const opt = sel.selectedOptions[0];
                if (opt) {
                    const precio = currentTipo === 'mayorista' 
                                   ? (opt.dataset.precioMayorista || 0) 
                                   : (opt.dataset.precioPublico || 0);
                    row.querySelector('.venta-precio').value = precio;
                    recalcRowVenta(row);
                }
            });
            recalcTotalVenta();
        });
    }
});

// ── Agregar fila ─────────────────────────────────────────
function addRowVenta(tbody, initialProductId = null) {
    const selectTemplate = document.getElementById('productos-template');
    if (!selectTemplate) return;

    // Clonar las opciones del template
    const options = Array.from(selectTemplate.options)
        .map(o => {
            const pPub = o.dataset.precioPublico || 0;
            const pMay = o.dataset.precioMayorista || 0;
            const stock = o.dataset.stock || 0;
            const selected = (initialProductId && String(o.value) === String(initialProductId)) ? 'selected' : '';
            return `<option value="${o.value}" 
                      ${selected}
                      data-precio-publico="${pPub}" 
                      data-precio-mayorista="${pMay}" 
                      data-stock="${stock}"
                      data-sku="${o.dataset.sku || ''}">${o.text}</option>`;
        }).join('');

    const rowHtml = `
    <tr>
      <td>
        <select name="producto_id[]" class="form-select form-select-sm venta-select" required>
          <option value="" disabled selected>-- Elija Producto --</option>
          ${options}
        </select>
        <div class="mt-1"><small class="text-muted stock-label">Stock: --</small></div>
      </td>
      <td>
        <input type="number" name="cantidad[]" class="form-control form-control-sm venta-cantidad" min="0.001" step="0.001" value="1" required>
      </td>
      <td>
        <input type="number" name="precio_unitario[]" class="form-control form-control-sm venta-precio" min="0" step="0.01" value="0" required>
      </td>
      <td class="text-end fw-semibold subtotal-cell">Q 0.00</td>
      <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
    </tr>`;

    tbody.insertAdjacentHTML('beforeend', rowHtml);

    const lastRow = tbody.lastElementChild;
    const sel = lastRow.querySelector('.venta-select');
    
    if (sel) {
        sel.addEventListener('change', () => {
            const currentTipo = document.getElementById('tipo-precio')?.value || 'publico';
            const option = sel.selectedOptions[0];
            if (!option || !option.value) return;

            const precio = currentTipo === 'mayorista' 
                           ? (option.dataset.precioMayorista || 0) 
                           : (option.dataset.precioPublico || 0);
            
            const stock = option.dataset.stock || 0;
            lastRow.querySelector('.venta-precio').value = precio;
            lastRow.querySelector('.stock-label').textContent = 'Stock: ' + stock;
            lastRow.dataset.maxStock = stock;
            
            validateStockRow(lastRow);
            recalcRowVenta(lastRow);
            recalcTotalVenta();
        });
        
        // Si hay una selección inicial, disparar el cambio
        if (initialProductId) {
            sel.dispatchEvent(new Event('change'));
        }
    }
}

// ── Validar Stock en la fila ──
function validateStockRow(tr) {
    const qtyInput = tr.querySelector('.venta-cantidad');
    const qty = parseFloat(qtyInput.value) || 0;
    const max = parseFloat(tr.dataset.maxStock) || 0;

    if (qty > max) {
        qtyInput.classList.add('is-invalid');
        qtyInput.classList.remove('is-valid');
    } else {
        qtyInput.classList.remove('is-invalid');
        qtyInput.classList.add('is-valid');
    }
}

// ── Recalcular fila ──────────────────────────────────────
function recalcRowVenta(tr) {
    const qtyInput = tr.querySelector('.venta-cantidad');
    const priceInput = tr.querySelector('.venta-precio');
    const qty = parseFloat(qtyInput?.value) || 0;
    const precio = parseFloat(priceInput?.value) || 0;
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


