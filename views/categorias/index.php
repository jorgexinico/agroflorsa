<?php // views/categorias/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0"><?= count($categorias) ?> categorías</p>
  <a href="/<?= $_ENV['APP_NAME'] ?>/categorias/crear" class="btn btn-success btn-sm">
    <i class="bi bi-plus-circle me-1"></i>Nueva categoría
  </a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Nombre</th><th>Descripción</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($categorias as $c): ?>
        <tr data-id="<?= $c->id ?>">
          <td>
            <input type="text" class="form-control form-control-sm border-0 bg-transparent fw-semibold p-0 w-100" 
                   value="<?= s($c->nombre) ?>"
                   onchange="actualizarInline(<?= $c->id ?>, 'nombre', this.value)">
          </td>
          <td>
            <input type="text" class="form-control form-control-sm border-0 bg-transparent text-muted p-0 w-100" 
                   value="<?= s($c->descripcion ?? '') ?>" placeholder="—"
                   onchange="actualizarInline(<?= $c->id ?>, 'descripcion', this.value)">
          </td>
          <td class="text-end text-nowrap">
            <a href="/<?= $_ENV['APP_NAME'] ?>/categorias/editar?id=<?= $c->id ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
async function actualizarInline(id, campo, valor) {
    try {
        const bodyData = { id: id };
        bodyData[campo] = valor;

        const response = await fetch('/<?= $_ENV['APP_NAME'] ?>/categorias/actualizar-inline', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(bodyData)
        });

        const data = await response.json();
        
        if (!data.ok) {
            Swal.fire('Error', data.error || 'No se pudo actualizar el valor', 'error');
        } else {
            const Toast = Swal.mixin({
                toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500, timerProgressBar: true
            });
            Toast.fire({ icon: 'success', title: 'Actualizado' });
        }
    } catch (error) {
        console.error("Error al actualizar: ", error);
        Swal.fire('Error de Red', 'Hubo un problema de conexión al guardar.', 'error');
    }
}
</script>
