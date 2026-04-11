<?php // views/inventario/index.php ?>
<div class="row mb-3">
  <div class="col-md-5">
    <form method="GET" action="" class="d-flex gap-2">
      <select name="sucursal_id" id="sucursal_id_selector" class="form-select border-2">
        <option value="">Seleccionar sucursal...</option>
        <?php foreach ($sucursales as $s): ?>
        <option value="<?= $s->id ?>" <?= $sucursalId == $s->id ? 'selected' : '' ?>><?= s($s->nombre) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-dark">Ver Stock</button>
    </form>
  </div>
  <div class="col-md-5">
    <?php $usuarioRol = $_SESSION['usuario_rol'] ?? ''; ?>
    <?php if ($sucursalId && in_array($usuarioRol, ['admin', 'supervisor'])): ?>
    <div class="input-group">
      <span class="input-group-text bg-white border-2 border-end-0"><i class="bi bi-search text-success"></i></span>
      <input type="text" id="global-product-search" class="form-control border-2 border-start-0" 
             placeholder="Buscar producto para agregar..." list="productos-globales">
      <datalist id="productos-globales">
        <?php foreach ($productos as $p): 
            $desc = s($p->nombre) . " [" . s($p->sku ?? 'S/S') . "] - " . s($p->unidad_abreviatura ?? '');
        ?>
        <option value="<?= $desc ?>" data-id="<?= $p->id ?>" 
                data-sku="<?= s($p->sku ?? '') ?>" 
                data-unidad="<?= s($p->unidad_abreviatura ?? '') ?>"
                data-nombre="<?= s($p->nombre) ?>"
                data-pub="<?= (float)$p->precio_publico ?>" 
                data-may="<?= (float)$p->precio_mayorista ?>"
                data-maneja="<?= $p->maneja_vencimiento ?? '0' ?>">
        <?php endforeach; ?>
      </datalist>
    </div>
    <?php endif; ?>
  </div>
  <div class="col-auto ms-auto">
    <?php if (in_array($usuarioRol, ['admin', 'supervisor'])): ?>
    <a href="<?= $base ?>/inventario/ajuste" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-pencil-square me-1"></i>Ajuste manual
    </a>
    <a href="<?= $base ?>/compras/crear" class="btn btn-success btn-sm ms-2">
      <i class="bi bi-truck me-1"></i>Registrar compra
    </a>
    <?php endif; ?>
  </div>
</div>

<?php if ($sucursalId && !empty($stock)): ?>
<?php 
$totalValorCosto = 0;
$totalValorPublico = 0;
if ($usuarioRol === 'admin' || $usuarioRol === 'supervisor') {
    foreach ($stock as $i) {
        if ((float)$i['cantidad'] > 0) {
            $totalValorCosto += ((float)$i['cantidad'] * (float)($i['ultimo_costo'] ?? 0));
            $totalValorPublico += ((float)$i['cantidad'] * (float)($i['precio_publico'] ?? 0));
        }
    }
}
?>
<?php if ($usuarioRol === 'admin' || $usuarioRol === 'supervisor'): ?>
<div class="row mb-3">
  <div class="col-12">
    <div class="d-flex flex-wrap gap-3 justify-content-end animate__animated animate__fadeIn">
      <div class="bg-white p-2 px-4 rounded shadow-sm border-start border-warning border-4 text-center">
        <span class="d-block small text-muted fw-bold text-uppercase">Valor Estimado al Costo</span>
        <span class="fs-5 text-dark fw-bold"><?= formatMoney($totalValorCosto) ?></span>
      </div>
      <div class="bg-white p-2 px-4 rounded shadow-sm border-start border-success border-4 text-center">
        <span class="d-block small text-muted fw-bold text-uppercase">Valor Estimado al Público</span>
        <span class="fs-5 text-success fw-bold"><?= formatMoney($totalValorPublico) ?></span>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
      <div class="d-flex align-items-center me-3">
        <i class="bi bi-boxes me-2"></i>Stock de sucursal
        <span class="text-muted small ms-2 d-none d-sm-inline"><?= count($stock) ?> productos</span>
      </div>
      <div class="flex-grow-1 mx-sm-3" style="max-width: 400px;">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
          <input type="text" id="filtro-tabla-stock" class="form-control" placeholder="Buscar producto en la tabla (Nombre o SKU)...">
        </div>
      </div>
      <button class="btn btn-sm btn-outline-success border-2 fw-bold flex-shrink-0" onclick="exportarExcel()">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tablaStock">
        <thead class="table-light">
          <tr>
            <th>SKU</th>
            <th>Producto</th>
            <th>Unidad</th>
            <th class="text-center" style="width: 120px;">Cantidad</th>
            <th class="text-center" style="width: 120px;">Costo</th>
            <th class="text-center" style="width: 120px;">Pr. Público</th>
            <th class="text-center" style="width: 120px;">Pr. Mayorista</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody id="tablaStockBody">
          <?php foreach ($stock as $item): ?>
          <tr class="<?= (float)$item['cantidad'] <= 0 ? 'table-danger' : '' ?>">
            <td class="text-muted small"><?= s($item['sku'] ?? '—') ?></td>
            <td class="fw-semibold"><?= s($item['producto_nombre']) ?></td>
            <td><?= s($item['unidad_abreviatura']) ?></td>
            <td class="text-end fw-bold <?= (float)$item['cantidad'] <= 0 ? 'text-danger' : 'text-success' ?>">
              <?= number_format((float)$item['cantidad'], 3) ?>
            </td>
            <td class="text-center text-muted fw-semibold">
              <?= $item['ultimo_costo'] ? formatMoney((float)$item['ultimo_costo']) : '—' ?>
            </td>
            <td class="text-center text-primary fw-semibold">
              <?= formatMoney((float)$item['precio_publico']) ?>
            </td>
            <td class="text-center text-secondary fw-semibold">
              <?= formatMoney((float)$item['precio_mayorista']) ?>
            </td>
            <td class="text-end text-nowrap">
              <?php if ((int)($item['maneja_vencimiento'] ?? 0) === 1): ?>
              <button class="btn btn-outline-warning btn-sm" onclick="asignarVencimiento(<?= $item['producto_id'] ?>)" title="Asignar Vencimiento a Stock Huérfano">
                <i class="bi bi-calendar-plus"></i>
              </button>
              <button class="btn btn-outline-info btn-sm" onclick="verLotes(<?= $item['producto_id'] ?>)" title="Ver Lotes">
                <i class="bi bi-tags"></i>
              </button>
              <?php endif; ?>
              <a href="<?= $base ?>/inventario/kardex?producto_id=<?= $item['producto_id'] ?>&sucursal_id=<?= $sucursalId ?>" 
                 class="btn btn-outline-dark btn-sm" title="Ver Kardex">
                <i class="bi bi-journal-text"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <style>
    .input-inline {
        border: 2px solid transparent;
        background: #f8f9fa;
        border-radius: 4px;
        transition: all 0.2s;
        text-align: center;
        font-weight: bold;
    }
    .input-inline:focus {
        border-color: #198754;
        background: white;
        outline: none;
        box-shadow: 0 0 5px rgba(25, 135, 84, 0.2);
    }
    .row-new {
        background-color: rgba(255, 193, 7, 0.05) !important;
    }
  </style>

  <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
  <script>
  const sucursalId = <?= (int)$sucursalId ?>;

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
            ${opt.dataset.maneja === '1' ? '<input type="date" class="form-control form-control-sm mt-1 fv-input" required><div class="x-small text-danger mt-1">Vence</div>' : ''}
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
          const response = await fetch('<?= $base ?>/inventario/ingreso-rapido-ajax', {
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
              const res = await fetch('<?= $base ?>/inventario/asignar-lote-stock-existente', {
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
          const res = await fetch('<?= $base ?>/inventario/ver-lotes-ajax?sucursal_id=' + sucursalId + '&producto_id=' + productoId);
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
                      
                      html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                          <div>
                              <small class="text-muted d-block">${l.codigo_lote}</small>
                              <span class="${warning}"><i class="bi bi-calendarx"></i> ${l.fecha_vencimiento}</span>
                              ${extra}
                          </div>
                          <span class="badge bg-dark rounded-pill fs-6">${l.cantidad}</span>
                      </li>`;
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
      // Obtener la tabla
      const tabla = document.getElementById('tablaStock');
      
      // Crear un libro de Excel y una hoja de cálculo desde la tabla HTML
      const wb = XLSX.utils.table_to_book(tabla, {sheet: "Stock"});
      
      // Obtener el nombre de la sucursal seleccionada (del select del filtro)
      const selectSucursal = document.querySelector('select[name="sucursal_id"]');
      const nombreSucursal = selectSucursal.options[selectSucursal.selectedIndex].text.replace(/[^a-z0-9]/gi, '_');
      
      // Generar la fecha actual para el nombre del archivo
      const fecha = new Date().toISOString().slice(0, 10);
      const nombreArchivo = `inventario_${nombreSucursal}_${fecha}.xlsx`;
      
      // Escribir el archivo y descargarlo
      XLSX.writeFile(wb, nombreArchivo);
  }
  </script>
<?php elseif ($sucursalId): ?>
<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No hay stock registrado para esta sucursal.</div>
<?php else: ?>
<div class="alert alert-secondary text-center py-5">
  <i class="bi bi-boxes fs-1 d-block mb-2"></i>
  Selecciona una sucursal para ver el inventario.
</div>
<?php endif; ?>
