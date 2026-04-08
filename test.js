const sucursalId = 123;

  document.addEventListener('DOMContentLoaded', () => {
      // 1. Buscador/Filtro directo en la tabla de stock (accesible para todos)
      const filterInput = document.getElementById('filtro-tabla-stock');
      if (filterInput) {
          filterInput.addEventListener('keyup', function() {
              const val = this.value.toLowerCase().trim();
              const rows = document.querySelectorAll('#tablaStockBody tr:not(.row-new)');
              rows.forEach(row => {
                  const text = row.textContent.toLowerCase();
                  if (text.includes(val)) {
                      row.style.display = '';
                  } else {
                      row.style.display = 'none';
                  }
              });
          });
      }

      // 2. Buscador global para agregar nuevo ingreso (solo admin)
      const searchInput = document.getElementById('global-product-search');
      const dataListGlobales = document.getElementById('productos-globales');
      
      if (searchInput && dataListGlobales) {
          // Evitar envío por Enter (lectores de códigos de barra)
          searchInput.addEventListener('keydown', (e) => {
              if (e.key === 'Enter') {
                  e.preventDefault();
                  procesarBusquedaGlobal(e);
              }
          });

          function procesarBusquedaGlobal(e) {
              const val = e.target.value.trim().toLowerCase();
              if (!val) return;
              const options = dataListGlobales.options;
              
              for (let i = 0; i < options.length; i++) {
                  const optValue = options[i].value.trim().toLowerCase();
                  const optSku = (options[i].dataset.sku || '').trim().toLowerCase();
                  
                  if (optValue === val || (optSku !== '' && optSku === val)) {
                      agregarFilaDirecta(options[i]);
                      e.target.value = ''; // Limpiar buscador
                      searchInput.blur();
                      setTimeout(() => searchInput.focus(), 50);
                      break;
                  }
              }
          }

          searchInput.addEventListener('input', procesarBusquedaGlobal);
          searchInput.addEventListener('change', procesarBusquedaGlobal);
      }
  });

  function agregarFilaDirecta(opt) {
      const tbody = document.getElementById('tablaStockBody');
      const id = opt.dataset.id;
      
      // Evitar duplicados si ya está en la tabla como fila editable (opcional)
      // Pero el usuario quiere "ingreso" así que permitimos agregar
      
      const row = document.createElement('tr');
      row.className = 'row-new animate__animated animate__fadeIn';
      row.innerHTML = `
          <td class="text-muted small">${opt.dataset.sku}</td>
          <td class="fw-semibold text-primary"><i class="bi bi-plus-circle-fill me-1"></i> ${opt.dataset.nombre}</td>
          <td>${opt.dataset.unidad}</td>
          <td class="text-center">
            <input type="number" step="0.001" class="form-control form-control-sm input-inline qty-input" value="0">
            <div class="x-small text-muted mt-1">Cantidad</div>
            ${opt.dataset.maneja === '1' ? \`<input type="date" class="form-control form-control-sm mt-1 fv-input" required><div class="x-small text-danger mt-1">Vence</div>\` : \`\`}
          </td>
          <td class="text-center">
            <input type="number" step="0.01" class="form-control form-control-sm input-inline cost-input" value="0">
            <div class="x-small text-muted mt-1">Costo</div>
          </td>
          <td class="text-center">
            <input type="number" step="0.01" class="form-control form-control-sm input-inline pub-input" value="${opt.dataset.pub}">
            <div class="x-small text-muted mt-1">Pub. Act: Q${opt.dataset.pub}</div>
          </td>
          <td class="text-center">
            <input type="number" step="0.01" class="form-control form-control-sm input-inline may-input" value="${opt.dataset.may}">
            <div class="x-small text-muted mt-1">May. Act: Q${opt.dataset.may}</div>
          </td>
          <td class="text-end">
            <button type="button" class="btn btn-success btn-sm btn-save-row" onclick="guardarIngresoFila(this, ${id})">
              <i class="bi bi-check-lg"></i>
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('tr').remove()">
              <i class="bi bi-x-lg"></i>
            </button>
          </td>
      `;
      
      tbody.prepend(row);
      row.querySelector('.qty-input').focus();
      row.querySelector('.qty-input').select();
  }

  async function guardarIngresoFila(btn, productoId) {
      const row = btn.closest('tr');
      const qty = row.querySelector('.qty-input').value;
      const cost = row.querySelector('.cost-input').value;
      const pub = row.querySelector('.pub-input').value;
      const may = row.querySelector('.may-input').value;
      const fvInput = row.querySelector('.fv-input');
      const fv = fvInput ? fvInput.value : '';

      if (parseFloat(qty) === 0) {
          Swal.fire('Atención', 'Ingresa una cantidad válida', 'warning');
          return;
      }
      
      if (fvInput && !fv) {
          Swal.fire('Atención', 'Ingresa la fecha de vencimiento', 'warning');
          return;
      }

      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

      try {
          const response = await fetch('/inventario/ingreso-rapido-ajax', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                  sucursal_id: sucursalId,
                  producto_id: productoId,
                  cantidad: qty,
                  costo: cost,
                  precio_publico: pub,
                  precio_mayorista: may,
                  motivo: 'Carga inicial rápida',
                  fecha_vencimiento: fv
              })
          });

          const data = await response.json();
          if (data.ok) {
              const Toast = Swal.mixin({
                  toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true
              });
              Toast.fire({ icon: 'success', title: 'Producto ingresado correctamente' });
              
              // Transformar fila a estática o recargar
              row.classList.remove('row-new');
              row.classList.add('table-success');
              row.innerHTML = `
                  <td colspan="7" class="text-center fw-bold py-2"><i class="bi bi-check-circle-fill me-2"></i> ¡Ingresado Correctamente!</td>
                  <td class="text-end"><button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">Recargar</button></td>
              `;
              setTimeout(() => { 
                // O simplemente recargar la página para ver el stock actualizado
                // location.reload(); 
              }, 1500);
          } else {
              Swal.fire('Error', data.error || 'No se pudo procesar', 'error');
              btn.disabled = false;
              btn.innerHTML = '<i class="bi bi-check-lg"></i>';
          }
      } catch (error) {
          console.error(error);
          Swal.fire('Error', 'Problema al procesar la respuesta del servidor. ' + error.message, 'error');
      } finally {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-check-lg"></i>';
      }
  }

  async function asignarVencimiento(productoId) {
      const { value: formValues } = await Swal.fire({
        title: 'Asignar Vencimiento',
        html: `
          <p class="small text-muted">Aislar stock en un nuevo lote.</p>
          <div class="mb-3 text-start">
            <label class="form-label">Cantidad a extraer del stock huérfano:</label>
            <input type="number" step="0.001" id="swal-cant" class="form-control" placeholder="Ej. 10">
          </div>
          <div class="text-start">
            <label class="form-label">Fecha de Caducidad:</label>
            <input type="date" id="swal-fecha" class="form-control">
          </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Crear Lote',
        preConfirm: () => {
          const c = document.getElementById('swal-cant').value;
          const f = document.getElementById('swal-fecha').value;
          if (!c || c <= 0) {
              Swal.showValidationMessage('Ingresa una cantidad válida');
              return false;
          }
          if (!f) {
              Swal.showValidationMessage('Selecciona una fecha');
              return false;
          }
          return { cantidad: c, fecha: f }
        }
      });

      if (formValues) {
          try {
              const res = await fetch('/inventario/asignar-lote-stock-existente', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({
                      sucursal_id: sucursalId,
                      producto_id: productoId,
                      cantidad: formValues.cantidad,
                      fecha_vencimiento: formValues.fecha
                  })
              });
              const data = await res.json();
              if (data.ok) {
                  Swal.fire('¡Lote Creado!', 'Se ha regularizado esta porción de stock en un nuevo lote.', 'success');
              } else {
                  Swal.fire('Error', data.error || 'Hubo un problema', 'error');
              }
          } catch(e) {
              Swal.fire('Error', 'Error de conexión', 'error');
          }
      }
  }

  async function verLotes(productoId) {
      try {
          const res = await fetch('/inventario/ver-lotes-ajax?sucursal_id=' + sucursalId + '&producto_id=' + productoId);
          const data = await res.json();
          if (data.ok) {
              let html = '<ul class="list-group list-group-flush text-start">';
              if (data.lotes.length === 0) {
                  html += '<li class="list-group-item text-muted">No hay lotes con existencias mayores a 0 registrados.</li>';
              } else {
                  data.lotes.forEach(l => {
                      const dateObj = new Date(l.fecha_vencimiento + 'T00:00:00');
                      const hoy = new Date();
                      hoy.setHours(0,0,0,0);
                      const warning = dateObj < hoy ? 'text-danger fw-bold' : '';
                      const extra = dateObj < hoy ? ' <span class="badge bg-danger">Vencido</span>' : '';
                      
                      html += \`<li class="list-group-item d-flex justify-content-between align-items-center">
                          <div>
                              <small class="text-muted d-block">\${l.codigo_lote}</small>
                              <span class="\${warning}"><i class="bi bi-calendarx"></i> \${l.fecha_vencimiento}</span>
                              \${extra}
                          </div>
                          <span class="badge bg-dark rounded-pill fs-6">\${l.cantidad}</span>
                      </li>\`;
                  });
              }
              html += '</ul>';
              
              Swal.fire({
                  title: 'Lotes Activos',
                  html: html,
                  confirmButtonText: 'Cerrar'
              });
          }
      } catch(e) {
          Swal.fire('Error', 'Error al cargar lotes', 'error');
      }
  }

  function exportarExcel() {
  }
