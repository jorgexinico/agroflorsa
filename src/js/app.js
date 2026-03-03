/**
 * app.js — Bundle global del layout principal de Agroflorsa
 * Responsabilidades:
 *   1. Estilos globales (SCSS)
 *   2. Toggle / colapso del sidebar
 *   3. Handler global de confirmaciones SweetAlert2 (ag-confirm-btn)
 *   4. Flash messages toast (parámetros ?ok= y ?err= en la URL)
 */

import '../scss/app.scss';
import * as bootstrap from 'bootstrap';
import Swal from 'sweetalert2';
import { Toast } from './funciones.js';

document.addEventListener('DOMContentLoaded', () => {

  // ── 1. Sidebar toggle ──────────────────────────────────
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('main-content');

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

  // ── 2. Cerrar sidebar al hacer clic fuera (mobile) ─────
  document.addEventListener('click', (e) => {
    if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('show')) {
      if (!sidebar.contains(e.target) && e.target !== sidebarToggle) {
        sidebar.classList.remove('show');
      }
    }
  });

  // ── 3. Confirmaciones SweetAlert2 (ag-confirm-btn) ─────
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.ag-confirm-btn');
    if (!btn) return;
    e.preventDefault();

    const titulo = btn.dataset.titulo || '¿Confirmar acción?';
    const nombre = btn.dataset.nombre || '';
    const form = btn.closest('.ag-confirm-form');
    if (!form) return;

    Swal.fire({
      icon: 'warning',
      title: titulo,
      html: nombre ? `<span class="fw-semibold">${nombre}</span>` : '',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="bi bi-power me-1"></i> Sí, desactivar',
      cancelButtonText: 'Cancelar',
      focusCancel: true,
    }).then((result) => {
      if (result.isConfirmed) form.submit();
    });
  });

  // ── 4. Flash messages via URL (?ok= / ?err=) ──────────
  const params = new URLSearchParams(window.location.search);

  if (params.has('ok')) {
    // Los mensajes ok vienen del servidor; si hay texto en el DOM lo leemos,
    // de lo contrario mostramos mensaje genérico.
    const msgEl = document.getElementById('flash-ok-msg');
    Toast.fire({
      icon: 'success',
      title: msgEl ? msgEl.dataset.msg : '¡Operación completada!',
    });
  }

  if (params.get('err') === '403') {
    Toast.fire({
      icon: 'error',
      title: 'Sin permisos',
      text: 'No tienes permisos para esa acción.',
    });
  }

});
