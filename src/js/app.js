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

  // ── 1. Sidebar toggle y Backdrop ───────────────────────
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('main-content');
  
  // Crear backdrop dinámico si no existe
  let backdrop = document.querySelector('.sidebar-backdrop');
  if (!backdrop) {
    backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
  }

  function toggleSidebar() {
    if (window.innerWidth <= 768) {
      const isShowing = sidebar.classList.contains('show');
      if (isShowing) {
        sidebar.classList.remove('show');
        backdrop.classList.remove('show');
      } else {
        sidebar.classList.add('show');
        backdrop.classList.add('show');
      }
    } else {
      sidebar.classList.toggle('collapsed');
      mainContent?.classList.toggle('expanded');
    }
  }

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', toggleSidebar);
  }

  // ── 2. Cerrar sidebar al hacer clic en el backdrop ─────
  backdrop.addEventListener('click', () => {
    if (window.innerWidth <= 768 && sidebar.classList.contains('show')) {
      sidebar.classList.remove('show');
      backdrop.classList.remove('show');
    }
  });

  // ── 2.5. Recordar posición del scroll del sidebar ───────
  const sidebarNav = document.querySelector('.ag-sidebar__nav');
  if (sidebarNav) {
    // Restaurar scroll al cargar
    const savedScroll = sessionStorage.getItem('sidebarScrollTop');
    if (savedScroll) {
      sidebarNav.scrollTop = parseInt(savedScroll, 10);
    }
    // Guardar scroll antes de salir de la página o al hacer clic en un enlace
    sidebarNav.addEventListener('click', () => {
      sessionStorage.setItem('sidebarScrollTop', sidebarNav.scrollTop);
    });
  }

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
