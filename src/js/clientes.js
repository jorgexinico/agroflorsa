/**
 * clientes.js — Lógica de las vistas de Clientes
 * Las confirmaciones de desactivar las maneja el handler global en app.js.
 * Este módulo es el punto de extensión para validaciones de formulario,
 * búsquedas en tiempo real, etc.
 */
import { validarFormulario } from './funciones.js';

document.addEventListener('DOMContentLoaded', () => {

    // ── Validación del formulario de crear/editar ─────────
    const form = document.getElementById('form-cliente');
    if (form) {
        form.addEventListener('submit', (e) => {
            const valido = validarFormulario(form, ['nit', 'telefono', 'direccion']);
            if (!valido) e.preventDefault();
        });
    }

});
