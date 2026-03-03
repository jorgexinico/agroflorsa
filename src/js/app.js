import '../scss/app.scss';

// Bootstrap components
import * as bootstrap from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {

  // ── Sidebar toggle ─────────────────────────────────────
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar       = document.getElementById('sidebar');
  const mainContent   = document.getElementById('main-content');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      if (window.innerWidth <= 768) {
        sidebar.classList.toggle('show');
      } else {
        sidebar.classList.toggle('collapsed');
        mainContent?.classList.toggle('expanded');
      }
    });
  }

  // ── Cerrar sidebar al hacer clic fuera (mobile) ────────
  document.addEventListener('click', (e) => {
    if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('show')) {
      if (!sidebar.contains(e.target) && e.target !== sidebarToggle) {
        sidebar.classList.remove('show');
      }
    }
  });

  // ── Ventas: tabla dinámica de productos ───────────────
  initVentasForm();

  // ── Compras: tabla dinámica de productos ──────────────
  initComprasForm();

  // ── Auto-dismiss alerts ─────────────────────────────
  document.querySelectorAll('.alert:not(.alert-permanent)').forEach(el => {
    setTimeout(() => {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
      bsAlert?.close();
    }, 5000);
  });

});

// ── VENTAS: agregar/quitar fila de producto ─────────────
function initVentasForm() {
  const tbody = document.getElementById('venta-items');
  const btnAdd = document.getElementById('btn-add-item');

  if (!tbody || !btnAdd) return;

  btnAdd.addEventListener('click', () => addRow(tbody, 'venta'));

  tbody.addEventListener('click', (e) => {
    if (e.target.closest('.btn-remove-row')) {
      e.target.closest('tr').remove();
      recalcTotal('venta');
    }
  });

  tbody.addEventListener('input', (e) => {
    if (e.target.matches('.venta-cantidad, .venta-precio')) {
      recalcRow(e.target.closest('tr'), 'venta');
      recalcTotal('venta');
    }
  });

  // Agregar primera fila por defecto
  addRow(tbody, 'venta');
}

function initComprasForm() {
  const tbody = document.getElementById('compra-items');
  const btnAdd = document.getElementById('btn-add-compra');

  if (!tbody || !btnAdd) return;

  btnAdd.addEventListener('click', () => addRow(tbody, 'compra'));

  tbody.addEventListener('click', (e) => {
    if (e.target.closest('.btn-remove-row')) {
      e.target.closest('tr').remove();
      recalcTotal('compra');
    }
  });

  tbody.addEventListener('input', (e) => {
    if (e.target.matches('.compra-cantidad, .compra-costo')) {
      recalcRow(e.target.closest('tr'), 'compra');
      recalcTotal('compra');
    }
  });

  addRow(tbody, 'compra');
}

function addRow(tbody, tipo) {
  const select = document.getElementById('productos-template');
  if (!select) return;

  const options = Array.from(select.options)
    .map(o => `<option value="${o.value}" data-precio="${o.dataset.precio||0}" data-sku="${o.dataset.sku||''}">${o.text}</option>`)
    .join('');

  let row = '';
  if (tipo === 'venta') {
    row = `
    <tr>
      <td><select name="producto_id[]" class="form-select form-select-sm venta-select" required>${options}</select></td>
      <td><input type="number" name="cantidad[]" class="form-control form-control-sm venta-cantidad" min="0.001" step="0.001" value="1" required></td>
      <td><input type="number" name="precio_unitario[]" class="form-control form-control-sm venta-precio" min="0" step="0.01" value="0" required></td>
      <td class="text-end fw-semibold subtotal-cell">Q 0.00</td>
      <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
    </tr>`;
  } else {
    row = `
    <tr>
      <td><select name="producto_id[]" class="form-select form-select-sm" required>${options}</select></td>
      <td><input type="number" name="cantidad[]" class="form-control form-control-sm compra-cantidad" min="0.001" step="0.001" value="1" required></td>
      <td><input type="number" name="costo_unitario[]" class="form-control form-control-sm compra-costo" min="0" step="0.0001" value="0" required></td>
      <td class="text-end fw-semibold subtotal-cell">Q 0.00</td>
      <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
    </tr>`;
  }

  tbody.insertAdjacentHTML('beforeend', row);

  // Auto-fill precio al seleccionar producto (ventas)
  const lastRow = tbody.lastElementChild;
  const sel = lastRow.querySelector('.venta-select');
  if (sel) {
    sel.addEventListener('change', () => {
      const precio = sel.selectedOptions[0]?.dataset.precio || 0;
      lastRow.querySelector('.venta-precio').value = precio;
      recalcRow(lastRow, 'venta');
      recalcTotal('venta');
    });
  }
}

function recalcRow(tr, tipo) {
  const qty     = parseFloat(tr.querySelector(tipo==='venta'?'.venta-cantidad':'.compra-cantidad')?.value) || 0;
  const precio  = parseFloat(tr.querySelector(tipo==='venta'?'.venta-precio':'.compra-costo')?.value) || 0;
  const sub     = qty * precio;
  const cell    = tr.querySelector('.subtotal-cell');
  if (cell) cell.textContent = 'Q ' + sub.toFixed(2);
}

function recalcTotal(tipo) {
  const cells = document.querySelectorAll('.subtotal-cell');
  let total = 0;
  cells.forEach(c => { total += parseFloat(c.textContent.replace('Q ','')) || 0; });
  const el = document.getElementById(tipo==='venta' ? 'venta-total' : 'compra-total');
  if (el) el.textContent = 'Q ' + total.toFixed(2);
}
