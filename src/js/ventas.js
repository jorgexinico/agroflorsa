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

    // ── Buscador de productos con datalist ───────────────
    const searchInput = document.getElementById('buscar-producto-venta');
    const dataList = document.getElementById('lista-productos-venta');
    
    if (searchInput && dataList) {
        // Evitar que al escanear o presionar Enter se envíe el formulario
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
                
                // Buscar por opción exacta o por SKU
                if (optValue === val || (optSku !== '' && optSku === val)) {
                    const id = options[i].dataset.id || options[i].getAttribute('data-id');
                    if (id) {
                        e.target.value = ''; // Limpiar buscador
                        addRowVenta(tbody, id);
                        // Desenfoque y reenfoque rápido para limpiar estado del datalist y navegadores móviles
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
            e.target.closest('.venta-item-row').remove();
            recalcTotalVenta();
        }
        
        // Manejar botones + y - para móviles
        if (e.target.closest('.btn-plus') || e.target.closest('.btn-minus')) {
            const row = e.target.closest('.venta-item-row');
            const input = row.querySelector('.venta-cantidad');
            let val = parseFloat(input.value) || 0;
            if (e.target.closest('.btn-plus')) {
                input.value = val + 1;
            } else if (e.target.closest('.btn-minus') && val > 1) {
                input.value = val - 1;
            }
            // Disparar input event para que recalcule
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });

    tbody.addEventListener('input', (e) => {
        if (e.target.matches('.venta-cantidad, .venta-precio')) {
            const row = e.target.closest('.venta-item-row');
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
        // La tabla inicia vacía para obligar el uso del buscador
        // addRowVenta(tbody); 
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
                const row = sel.closest('.venta-item-row');
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
    <div class="row align-items-center border rounded py-3 px-2 mb-3 venta-item-row bg-white shadow-sm">
      <div class="col-12 col-md-5 mb-3 mb-md-0">
        <label class="d-md-none fw-bold small text-muted">Producto</label>
        <select name="producto_id[]" class="form-select venta-select border-primary fw-bold" required>
          <option value="" disabled selected>-- Elija Producto --</option>
          ${options}
        </select>
        <div class="mt-2"><span class="badge bg-success text-white stock-label px-3 py-2 border border-success">Stock: --</span></div>
      </div>
      <div class="col-12 col-md-2 mb-3 mb-md-0">
        <label class="d-md-none fw-bold small text-muted">Cantidad</label>
        <div class="input-group">
          <button type="button" class="btn btn-outline-secondary btn-minus px-3 fw-bold fs-5">-</button>
          <input type="number" name="cantidad[]" class="form-control text-center fw-bold venta-cantidad" min="0.001" step="any" value="1" required>
          <button type="button" class="btn btn-outline-secondary btn-plus px-3 fw-bold fs-5">+</button>
        </div>
      </div>
      <div class="col-12 col-md-2 mb-3 mb-md-0">
        <label class="d-md-none fw-bold small text-muted">Precio U.</label>
        <div class="input-group">
          <span class="input-group-text bg-light text-muted fw-bold">Q</span>
          <input type="number" name="precio_unitario[]" class="form-control venta-precio fw-bold" min="0" step="0.01" value="0" required>
        </div>
      </div>
      <div class="col-8 col-md-2 mb-2 mb-md-0 text-md-end text-start">
        <label class="d-md-none fw-bold small text-muted d-block">Subtotal</label>
        <span class="fw-bold text-success fs-4 subtotal-cell">Q 0.00</span>
      </div>
      <div class="col-4 col-md-1 text-end">
        <button type="button" class="btn btn-outline-danger btn-remove-row w-100 w-md-auto py-2"><i class="bi bi-trash fs-5"></i></button>
      </div>
    </div>`;

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
            // Focus en cantidad
            setTimeout(() => lastRow.querySelector('.venta-cantidad')?.focus(), 50);
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

    const stockLabel = tr.querySelector('.stock-label');
    if (stockLabel) {
        if (tr.dataset.maxStock === undefined || tr.dataset.maxStock === '') {
             stockLabel.textContent = 'Stock: --';
             stockLabel.className = 'badge bg-secondary text-white stock-label px-3 py-2 border';
             return;
        }

        const remaining = max - qty;
        let decimalPlaces = (max % 1 === 0 && qty % 1 === 0) ? 0 : 2;
        stockLabel.textContent = `Stock restante: ${remaining.toFixed(decimalPlaces)}`;
        
        if (remaining < 0) {
            stockLabel.className = 'badge bg-danger text-white stock-label px-3 py-2 border';
        } else if (remaining === 0) {
            stockLabel.className = 'badge bg-warning text-dark stock-label px-3 py-2 border';
        } else {
            stockLabel.className = 'badge bg-success text-white stock-label px-3 py-2 border border-success';
        }
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


