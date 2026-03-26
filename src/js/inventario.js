import { confirmar } from './funciones.js';

document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('ajuste-items');
    const btnAdd = document.getElementById('btn-add-item');
    const btnLoadAll = document.getElementById('btn-load-all');
    const searchInput = document.getElementById('search-producto');

    if (!tbody) return;

    if (btnAdd) {
        btnAdd.addEventListener('click', () => addRowAjuste(tbody));
    }

    if (btnLoadAll) {
        btnLoadAll.addEventListener('click', async () => {
            const ok = await confirmar({
                titulo: '¿Cargar todos los productos?',
                texto: 'Se cargarán todos los productos activos en la tabla. Esto borrará lo que tengas actualmente.',
                icono: 'info'
            });
            if (ok) {
                loadAllProducts(tbody);
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.toLowerCase();
            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                const select = row.querySelector('.ajuste-select');
                if (!select) return;
                const text = select.options[select.selectedIndex]?.text.toLowerCase() || '';
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    tbody.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remove-row')) {
            e.target.closest('tr').remove();
        }
    });

    // Agregar primera fila si está vacío
    if (tbody.children.length === 0 && !btnLoadAll) {
        addRowAjuste(tbody);
    }
});

function addRowAjuste(tbody, data = null) {
    const template = document.getElementById('productos-template');
    if (!template) return;

    const options = Array.from(template.options).map(o => {
        const selected = data && data.id == o.value ? 'selected' : '';
        return `<option value="${o.value}" ${selected}
                data-precio-publico="${o.dataset.precioPublico}" 
                data-precio-mayorista="${o.dataset.precioMayorista}">
        ${o.text}</option>`;
    }).join('');

    const row = document.createElement('tr');
    row.innerHTML = `
        <td><select name="producto_id[]" class="form-select form-select-sm ajuste-select" required>${options}</select></td>
        <td><input type="number" name="cantidad[]" class="form-control form-control-sm" step="0.001" value="${data ? data.cantidad : 0}" required></td>
        <td><input type="number" name="precio_compra[]" class="form-control form-control-sm" step="0.01" value="0"></td>
        <td><input type="number" name="precio_publico[]" class="form-control form-control-sm ajuste-publico" step="0.01" value="${data ? data.precio_publico : 0}"></td>
        <td><input type="number" name="precio_mayorista[]" class="form-control form-control-sm ajuste-mayorista" step="0.01" value="${data ? data.precio_mayorista : 0}"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
    `;

    tbody.appendChild(row);
    
    const sel = row.querySelector('.ajuste-select');
    if (sel) {
        sel.addEventListener('change', () => {
            const opt = sel.selectedOptions[0];
            row.querySelector('.ajuste-publico').value = opt.dataset.precioPublico || 0;
            row.querySelector('.ajuste-mayorista').value = opt.dataset.precioMayorista || 0;
        });
        // Si no es carga masiva, disparamos el cambio para llenar precios
        if (!data) sel.dispatchEvent(new Event('change'));
    }
}

function loadAllProducts(tbody) {
    const template = document.getElementById('productos-template');
    if (!template) return;
    tbody.innerHTML = '';
    const options = Array.from(template.options);
    options.forEach(opt => {
        if (opt.value === "") return;
        addRowAjuste(tbody, {
            id: opt.value,
            cantidad: 0,
            precio_publico: opt.dataset.precioPublico,
            precio_mayorista: opt.dataset.precioMayorista
        });
    });
}
