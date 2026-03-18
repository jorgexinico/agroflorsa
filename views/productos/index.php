<?php // views/productos/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0"><?= count($productos) ?> productos activos</p>
  <div class="d-flex gap-2">
    <a href="/<?= $_ENV['APP_NAME'] ?>/productos/importar" class="btn btn-outline-success btn-sm">
      <i class="bi bi-file-earmark-excel me-1"></i>Importar Excel
    </a>
    <a href="/<?= $_ENV['APP_NAME'] ?>/productos/crear" class="btn btn-success btn-sm">
      <i class="bi bi-plus-circle me-1"></i>Nuevo producto
    </a>
  </div>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>SKU</th><th>Nombre</th><th>Tipo</th><th>Unidad</th><th>Categoría</th><th>P. Público</th><th>P. Mayorista</th><th>Vencim.</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($productos as $p): ?>
        <tr data-id="<?= $p->id ?>">
          <td class="text-muted small sku-cell"><?= s($p->sku ?? '—') ?></td>
          <td class="fw-semibold"><?= s($p->nombre) ?></td>
          <td><span class="badge bg-secondary"><?= s($p->tipo) ?></span></td>
          <td><?= s($p->unidad_nombre ?? '') ?> <span class="text-muted">(<?= s($p->unidad_abreviatura ?? '') ?>)</span></td>
          
          <!-- Edit Categoría -->
          <td>
            <select class="form-select form-select-sm border-0 bg-transparent text-primary fw-semibold" 
                    onchange="actualizarInline(<?= $p->id ?>, 'categoria_id', this.value)" 
                    style="min-width: 120px; cursor: pointer;">
                <option value="">-- Sin asignar --</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat->id ?>" <?= $p->categoria_id === $cat->id ? 'selected' : '' ?>>
                        <?= s($cat->nombre) ?>
                    </option>
                <?php endforeach; ?>
            </select>
          </td>

          <!-- Edit Precio Público -->
          <td>
            <div class="input-group input-group-sm flex-nowrap" style="width: 100px;">
                <span class="input-group-text border-0 bg-transparent text-success fw-semibold px-1">Q</span>
                <input type="number" step="0.01" min="0" 
                       class="form-control form-control-sm border-0 bg-transparent text-success fw-semibold p-0" 
                       value="<?= number_format($p->precio_publico ?? 0, 2, '.', '') ?>"
                       onchange="actualizarInline(<?= $p->id ?>, 'precio_publico', this.value)">
            </div>
          </td>

          <!-- Edit Precio Mayorista -->
          <td>
            <div class="input-group input-group-sm flex-nowrap" style="width: 100px;">
                <span class="input-group-text border-0 bg-transparent text-primary fw-semibold px-1">Q</span>
                <input type="number" step="0.01" min="0" 
                       class="form-control form-control-sm border-0 bg-transparent text-primary fw-semibold p-0" 
                       value="<?= number_format($p->precio_mayorista ?? 0, 2, '.', '') ?>"
                       onchange="actualizarInline(<?= $p->id ?>, 'precio_mayorista', this.value)">
            </div>
          </td>

          <td><?= $p->maneja_vencimiento ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-dash text-muted"></i>' ?></td>
          <td class="text-end text-nowrap">
            <a href="/<?= $_ENV['APP_NAME'] ?>/productos/editar?id=<?= $p->id ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
            <form method="POST" action="/<?= $_ENV['APP_NAME'] ?>/productos/eliminar" class="d-inline ag-confirm-form">
              <input type="hidden" name="id" value="<?= $p->id ?>">
              <button type="button" class="btn btn-sm btn-outline-danger ag-confirm-btn"
                      data-titulo="¿Desactivar producto?" data-nombre="<?= s($p->nombre) ?>">
                <i class="bi bi-power"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// Script para actualizar valores de la tabla en tiempo real
async function actualizarInline(id, campo, valor) {
    try {
        const bodyData = { id: id };
        bodyData[campo] = valor;

        const response = await fetch('/<?= $_ENV['APP_NAME'] ?>/productos/actualizar-inline', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(bodyData)
        });

        const data = await response.json();
        
        if (!data.ok) {
            Swal.fire('Error', data.error || 'No se pudo actualizar el valor', 'error');
        } else {
            // Actualizar visualmente el SKU si el servidor generó uno nuevo por el cambio de categoría
            if (data.nuevo_sku) {
                const fila = document.querySelector(`tr[data-id="${id}"]`);
                if (fila) {
                    const celdaSku = fila.querySelector('.sku-cell');
                    if (celdaSku) celdaSku.textContent = data.nuevo_sku;
                }
            }

            // Opcional: mostrar un minitostado (toast) de éxito muy discreto
            const Toast = Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: 'Actualizado'
            });
        }
    } catch (error) {
        console.error("Error al actualizar: ", error);
        Swal.fire('Error de Red', 'Hubo un problema de conexión al guardar.', 'error');
    }
}
</script>

<script src="<?= asset('build/js/productos.js') ?>"></script>
