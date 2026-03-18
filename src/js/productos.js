/**
 * productos.js — Lógica de las vistas de Productos
 * Las confirmaciones de desactivar las maneja el handler global en app.js.
 * Este módulo es el punto de extensión para validaciones y funcionalidad extra.
 */
import { validarFormulario } from './funciones.js';

document.addEventListener('DOMContentLoaded', () => {

    // ── Validación del formulario de crear/editar ─────────
    const form = document.getElementById('form-producto');
    if (form) {
        form.addEventListener('submit', (e) => {
            const valido = validarFormulario(form, ['sku', 'maneja_vencimiento']);
            if (!valido) e.preventDefault();
        });
    }

    // ── Toggle maneja_vencimiento ─────────────────────────
    // Si en el futuro se agrega un checkbox para ello, se puede manejar aquí.

    // ── Autogenerar SKU ───────────────────────────────────
    const inputNombre = document.getElementById('nombre_producto');
    const selectMarca = document.getElementById('marca_id');
    const selectCategoria = document.getElementById('categoria_id');
    const selectUnidad = document.getElementById('unidad_id');
    const inputSKU = document.getElementById('sku_producto');

    if (inputSKU && inputNombre && selectMarca && selectUnidad) {
        const generarSKU = () => {
            // 1. Marca (4 letras)
            let marcaStr = 'GENE';
            if (selectMarca.selectedIndex > 0) { // Si no es la opción "Seleccione..."
                const marcaTexto = selectMarca.options[selectMarca.selectedIndex].text;
                const marcaLimpia = marcaTexto.replace(/[^A-Za-z0-9]/g, '');
                if (marcaLimpia.length > 0) {
                    marcaStr = marcaLimpia.substring(0, 4).toUpperCase();
                    if (marcaStr.length < 4) marcaStr = marcaStr.padEnd(4, 'X');
                }
            }
            
            // 2. Categoria (4 letras)
            let categoriaStr = 'XXXX';
            if (selectCategoria && selectCategoria.selectedIndex > 0) {
                const catTexto = selectCategoria.options[selectCategoria.selectedIndex].text;
                const catLimpia = catTexto.replace(/[^A-Za-z0-9]/g, '');
                if (catLimpia.length > 0) {
                    categoriaStr = catLimpia.substring(0, 4).toUpperCase();
                    if (categoriaStr.length < 4) categoriaStr = categoriaStr.padEnd(4, 'X');
                }
            }

            // 3. Nombre (4 letras)
            let nombreStr = 'XXXX';
            const nombreLimpio = inputNombre.value.replace(/[^A-Za-z0-9]/g, '');
            if (nombreLimpio.length > 0) {
                nombreStr = nombreLimpio.substring(0, 4).toUpperCase();
                if (nombreStr.length < 4) nombreStr = nombreStr.padEnd(4, 'X');
            }

            // 4. Presentación (Unidad)
            let unidadStr = 'UND';
            if (selectUnidad.selectedIndex > 0) {
                const optionUnidad = selectUnidad.options[selectUnidad.selectedIndex];
                unidadStr = optionUnidad.dataset.abreviatura ? optionUnidad.dataset.abreviatura.toUpperCase() : 'UND';
            }

            // Generar y asignar
            inputSKU.value = `${marcaStr}-${categoriaStr}-${nombreStr}-${unidadStr}`;
        };

        // Escuchar eventos
        inputNombre.addEventListener('input', generarSKU);
        selectMarca.addEventListener('change', generarSKU);
        if(selectCategoria) selectCategoria.addEventListener('change', generarSKU);
        selectUnidad.addEventListener('change', generarSKU);

        // Generar inicialmente (útil al editar si cambiaron las reglas o para que el usuario lo vea)
        // Solo si está vacío para no sobreescribir inmediatamente al editar si es que tenían un SKU viejo manual
        // Pero como el requisito es generarlo automáticamente, lo regeneramos siempre para mantener consistencia.
        if (!inputSKU.value) { // O quitar el condicional si SIEMPRE debe regenerarse al cargar la vista de edición. 
            generarSKU();      // En este caso, solo si está vacío. Si editan, y cambian nombre, se regenerará.
        }
    }

});
