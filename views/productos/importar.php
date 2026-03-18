<?php // views/productos/importar.php — Importación masiva via Excel/XLSX con SheetJS ?>

<!-- SheetJS desde CDN (sin instalar nada en PHP) -->
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<div class="row g-4">

  <!-- Instrucciones + Upload -->
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-file-earmark-excel me-2 text-success"></i>Subir archivo Excel (.xlsx)
      </div>
      <div class="card-body">

        <div class="alert alert-info small mb-4">
          <strong><i class="bi bi-info-circle me-1"></i>Formato requerido del Excel:</strong>
          La primera fila debe ser el encabezado con las columnas en el orden correcto.<br>
          Descarga la plantilla para comenzar.
        </div>

        <!-- Zona de drop -->
        <div id="drop-zone" class="border border-2 border-dashed rounded-3 text-center p-5 mb-4"
             style="border-color: #198754 !important; background: #f8fff9; cursor: pointer; transition: background .2s;">
          <i class="bi bi-cloud-upload display-4 text-success d-block mb-2"></i>
          <p class="mb-1 fw-semibold">Arrastra tu archivo Excel aquí</p>
          <p class="text-muted small mb-3">o haz clic para seleccionar</p>
          <input type="file" id="excel-file" accept=".xlsx,.xls,.csv" class="d-none">
          <button type="button" class="btn btn-outline-success" onclick="document.getElementById('excel-file').click()">
            <i class="bi bi-folder2-open me-1"></i>Seleccionar archivo
          </button>
        </div>

        <!-- Progreso -->
        <div id="progress-wrap" class="d-none mb-3">
          <div class="d-flex justify-content-between small mb-1">
            <span id="progress-label">Procesando...</span>
            <span id="progress-pct">0%</span>
          </div>
          <div class="progress" style="height:8px">
            <div id="progress-bar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width:0%"></div>
          </div>
        </div>

        <!-- Footer con Download -->
        <div class="d-flex justify-content-between align-items-center">
          <a href="#" id="btn-download-template" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-download me-1"></i>Descargar plantilla
          </a>
          <button id="btn-importar" class="btn btn-success px-4" disabled>
            <i class="bi bi-upload me-1"></i>Importar productos
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Vista previa + columnas -->
  <div class="col-lg-5">
    <div class="card mb-3">
      <div class="card-header"><i class="bi bi-table me-2"></i>Columnas requeridas</div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Columna</th><th>Valor ejemplo</th><th>¿Requerido?</th></tr></thead>
          <tbody>
            <tr><td><code>nombre</code></td><td>Bravo 720</td><td><span class="badge bg-danger">Sí</span></td></tr>
            <tr><td><code>marca</code></td><td>Syngenta</td><td><span class="badge bg-danger">Sí</span></td></tr>
            <tr>
              <td><code>unidad</code></td>
              <td>
                <?php foreach ($unidades as $u): ?>
                  <span class="badge bg-light text-dark border me-1"><?= s($u->abreviatura) ?></span>
                <?php endforeach; ?>
              </td>
              <td><span class="badge bg-secondary">No*</span> <br><small class="text-muted">*Se inferirá del nombre si se omite</small></td>
            </tr>
            <tr><td><code>precio_publico</code></td><td>130.50</td><td><span class="badge bg-secondary">No</span></td></tr>
            <tr><td><code>precio_mayorista</code></td><td>115.00</td><td><span class="badge bg-secondary">No</span></td></tr>
            <tr><td><code>sku</code></td><td><span class="text-muted small">Auto-generado</span></td><td><span class="badge bg-secondary">No</span></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Preview tabla -->
    <div class="card" id="preview-card" style="display:none!important">
      <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-eye me-2"></i>Vista previa</span>
        <span id="preview-count" class="badge bg-success align-self-center"></span>
      </div>
      <div class="card-body p-0" style="max-height:280px;overflow-y:auto">
        <table class="table table-sm mb-0" id="preview-table">
          <thead id="preview-thead"></thead>
          <tbody id="preview-tbody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Resultados -->
<div id="resultado" class="mt-4 d-none">
  <div id="resultado-inner"></div>
</div>

<div class="mt-3">
  <a href="/<?= $_ENV['APP_NAME'] ?>/productos" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Volver a productos
  </a>
</div>

<script>
const BASE = '/<?= $_ENV['APP_NAME'] ?>';
let datosParaImportar = [];

// ── Drag & Drop ──────────────────────────────────────────
const dropZone  = document.getElementById('drop-zone');
const fileInput = document.getElementById('excel-file');

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.background = '#e8f8ef'; });
dropZone.addEventListener('dragleave', ()  => { dropZone.style.background = '#f8fff9'; });
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.style.background = '#f8fff9';
  if (e.dataTransfer.files[0]) procesarArchivo(e.dataTransfer.files[0]);
});
fileInput.addEventListener('change', () => {
  if (fileInput.files[0]) procesarArchivo(fileInput.files[0]);
});

// ── Leer archivo con SheetJS ─────────────────────────────
function procesarArchivo(file) {
  const reader = new FileReader();
  reader.onload = function(e) {
    const data     = new Uint8Array(e.target.result);
    const workbook = XLSX.read(data, { type: 'array' });
    const sheet    = workbook.Sheets[workbook.SheetNames[0]];
    const rows     = XLSX.utils.sheet_to_json(sheet, { defval: '' });

    if (!rows.length) {
      Swal.fire({
        icon: 'error',
        title: 'Archivo inválido',
        text: 'El archivo está vacío o no tiene el formato correcto.',
        confirmButtonColor: '#198754',
      });
      return;
    }

    datosParaImportar = rows;
    mostrarPreview(rows);
    document.getElementById('btn-importar').disabled = false;
  };
  reader.readAsArrayBuffer(file);
}

// ── Vista previa ─────────────────────────────────────────
function mostrarPreview(rows) {
  const card    = document.getElementById('preview-card');
  const thead   = document.getElementById('preview-thead');
  const tbody   = document.getElementById('preview-tbody');
  const counter = document.getElementById('preview-count');

  card.style.removeProperty('display');

  const cols = Object.keys(rows[0]);
  thead.innerHTML = '<tr>' + cols.map(c => `<th class="small">${c}</th>`).join('') + '</tr>';

  const muestra = rows.slice(0, 8);
  tbody.innerHTML = muestra.map(row =>
    '<tr>' + cols.map(c => `<td class="small">${String(row[c]).substring(0, 30)}</td>`).join('') + '</tr>'
  ).join('');

  counter.textContent = rows.length + ' filas';
}

// ── Importar ─────────────────────────────────────────────
document.getElementById('btn-importar').addEventListener('click', async () => {
  if (!datosParaImportar.length) return;

  // Normalizar claves (por si el usuario usa encabezados distintos)
  const mapa = {
    'nombre': 'nombre', 'name': 'nombre',
    'sku': 'sku', 'codigo': 'sku', 'code': 'sku',
    'tipo': 'tipo', 'type': 'tipo',
    'unidad': 'unidad', 'unit': 'unidad', 'um': 'unidad', 'abreviatura': 'unidad',
    'maneja_vencimiento': 'maneja_vencimiento', 'vencimiento': 'maneja_vencimiento',
  };

  const normalizado = datosParaImportar.map(fila => {
    const n = {};
    for (const [k, v] of Object.entries(fila)) {
      const key = mapa[k.toLowerCase().trim()] || k.toLowerCase().trim();
      n[key] = v;
    }
    return n;
  });

  // Mostrar barra de progreso
  document.getElementById('progress-wrap').classList.remove('d-none');
  document.getElementById('btn-importar').disabled = true;
  document.getElementById('progress-bar').style.width = '30%';
  document.getElementById('progress-label').textContent = 'Enviando al servidor...';

  try {
    const resp = await fetch(BASE + '/productos/importar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(normalizado),
    });

    document.getElementById('progress-bar').style.width = '100%';
    document.getElementById('progress-bar').classList.remove('progress-bar-animated', 'progress-bar-striped');
    document.getElementById('progress-pct').textContent = '100%';
    document.getElementById('progress-label').textContent = 'Completado';
    
    // Ocultar la zona de progreso después de 1 segundo
    setTimeout(() => {
        document.getElementById('progress-wrap').classList.add('d-none');
    }, 1500);

    const json = await resp.json();
    mostrarResultado(json);
  } catch (err) {
    mostrarResultado({ ok: false, error: 'Error de red: ' + err.message });
  }
});

// ── Mostrar resultado ────────────────────────────────────
function mostrarResultado(json) {
  const div = document.getElementById('resultado');
  div.classList.remove('d-none');

  if (!json.ok) {
    div.innerHTML = `<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>${json.error}</div>`;
    return;
  }

  let html = `<div class="alert alert-success">
    <i class="bi bi-check-circle-fill me-2"></i>
    <strong>${json.importados} productos importados exitosamente.</strong>
  </div>`;

  if (json.errores && json.errores.length) {
    html += `<div class="card border-warning">
      <div class="card-header text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Filas con problemas (${json.errores.length})</div>
      <ul class="list-group list-group-flush small">
        ${json.errores.map(e => `<li class="list-group-item py-1">${e}</li>`).join('')}
      </ul>
    </div>`;
  }

  html += `<div class="mt-3">
    <a href="${BASE}/productos" class="btn btn-success me-2">
      <i class="bi bi-eye me-1"></i>Ver productos importados
    </a>
    <button class="btn btn-outline-secondary" onclick="location.reload()">
      <i class="bi bi-arrow-repeat me-1"></i>Importar otro archivo
    </button>
  </div>`;

  document.getElementById('resultado-inner').innerHTML = html;
}

document.getElementById('btn-download-template').addEventListener('click', (e) => {
  e.preventDefault();

  const datos = [
    { nombre: 'Bravo 720 1L', marca: 'Syngenta', unidad: '', precio_publico: 130.50, precio_mayorista: 115.00 },
    { nombre: 'Abono 15-15-15', marca: 'Yara', unidad: 'Qq', precio_publico: 250.00, precio_mayorista: 235.00 },
    { nombre: 'Machete 20"', marca: 'Bellota', unidad: 'Und', precio_publico: 65.00, precio_mayorista: 52.00 },
  ];

  const ws = XLSX.utils.json_to_sheet(datos);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Plantilla');

  // Ancho de columnas
  ws['!cols'] = [{ wch: 30 }, { wch: 15 }, { wch: 10 }, { wch: 18 }, { wch: 15 }, { wch: 15 }];

  XLSX.writeFile(wb, 'plantilla_productos_agroflorsa.xlsx');
});
</script>
