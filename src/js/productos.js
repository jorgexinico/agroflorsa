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

});
