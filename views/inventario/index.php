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
    <?php if ($sucursalId): ?>
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
                data-may="<?= (float)$p->precio_mayorista ?>">
        <?php endforeach; ?>
      </datalist>
    </div>
    <?php endif; ?>
  </div>
  <div class="col-auto ms-auto">
    <a href="/<?= $_ENV['APP_NAME'] ?>/inventario/ajuste" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-pencil-square me-1"></i>Ajuste manual
    </a>
    <a href="/<?= $_ENV['APP_NAME'] ?>/compras/crear" class="btn btn-success btn-sm ms-2">
      <i class="bi bi-truck me-1"></i>Registrar compra
    </a>
  </div>
</div>

<?php if ($sucursalId && !empty($stock)): ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <i class="bi bi-boxes me-2"></i>Stock de sucursal
        <span class="text-muted small ms-2 d-none d-sm-inline"><?= count($stock) ?> productos</span>
      </div>
      <button class="btn btn-sm btn-outline-success border-2 fw-bold" onclick="exportarExcel()">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Descargar Excel
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
            <td class="text-center text-muted">—</td>
            <td class="text-center text-muted">—</td>
            <td class="text-center text-muted">—</td>
            <td class="text-end">
              <a href="/<?= $_ENV['APP_NAME'] ?>/inventario/kardex?producto_id=<?= $item['producto_id'] ?>&sucursal_id=<?= $sucursalId ?>" 
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
      const searchInput = document.getElementById('global-product-search');
      if (searchInput) {
          searchInput.addEventListener('input', (e) => {
              const val = e.target.value;
              const opts = document.getElementById('productos-globales').options;
              for (let i = 0; i < opts.length; i++) {
                  if (opts[i].value === val) {
                      agregarFilaDirecta(opts[i]);
                      e.target.value = ''; // Limpiar buscador
                      break;
                  }
              }
          });
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

      if (parseFloat(qty) === 0) {
          Swal.fire('Atención', 'Ingresa una cantidad válida', 'warning');
          return;
      }

      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

      try {
          const response = await fetch('/<?= $_ENV['APP_NAME'] ?>/inventario/ingreso-rapido-ajax', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                  sucursal_id: sucursalId,
                  producto_id: productoId,
                  cantidad: qty,
                  costo: cost,
                  precio_publico: pub,
                  precio_mayorista: may,
                  motivo: 'Carga inicial rápida'
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
