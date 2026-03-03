/**
 * turnos.js — Lógica de las vistas de Turnos
 * Actualmente: confirmación SweetAlert2 para cierre de turno.
 */
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {

    // ── Confirmación para cerrar turno ────────────────────
    const btnCerrar = document.getElementById('ag-cerrar-turno-btn');
    if (btnCerrar) {
        btnCerrar.addEventListener('click', () => {
            Swal.fire({
                icon: 'warning',
                title: '¿Cerrar turno?',
                text: 'Esta acción cerrará el turno actual. ¿Estás seguro?',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-stop-circle me-1"></i> Sí, cerrar turno',
                cancelButtonText: 'Cancelar',
                focusCancel: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelector('form[action*="turnos/cerrar"]').submit();
                }
            });
        });
    }

});
