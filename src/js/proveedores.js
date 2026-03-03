/**
 * proveedores.js — Lógica de las vistas de Proveedores
 * Las confirmaciones de desactivar las maneja el handler global en app.js.
 * Este módulo es el punto de extensión para validaciones y funcionalidad extra.
 */
import { validarFormulario } from './funciones.js';

document.addEventListener('DOMContentLoaded', () => {

    // ── Validación del formulario de crear/editar ─────────
    const form = document.getElementById('form-proveedor');
    if (form) {
        form.addEventListener('submit', (e) => {
            const valido = validarFormulario(form, ['nit', 'telefono', 'correo', 'direccion']);
            if (!valido) e.preventDefault();
        });
    }

});
