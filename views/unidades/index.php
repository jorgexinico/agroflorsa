<?php // views/unidades/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0"><?= count($unidades) ?> unidades</p>
  <a href="<?= $base ?>/unidades/crear" class="btn btn-success btn-sm">
    <i class="bi bi-plus-circle me-1"></i>Nueva unidad
  </a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Nombre</th><th>Abreviatura</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($unidades as $u): ?>
        <tr data-id="<?= $u->id ?>">
          <td>
            <input type="text" class="form-control form-control-sm border-0 bg-transparent fw-semibold p-0 w-100" 
                   value="<?= s($u->nombre) ?>"
                   onchange="actualizarInline(<?= $u->id ?>, 'nombre', this.value)">
          </td>
          <td>
            <div class="d-inline-flex align-items-center bg-secondary rounded px-2 py-1">
                <input type="text" class="form-control form-control-sm border-0 bg-transparent text-white fw-bold p-0 text-center" 
                       style="width: 50px;"
                       value="<?= s($u->abreviatura) ?>"
                       onchange="actualizarInline(<?= $u->id ?>, 'abreviatura', this.value)">
            </div>
          </td>
          <td class="text-end text-nowrap">
            <a href="<?= $base ?>/unidades/editar?id=<?= $u->id ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
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

        const response = await fetch('<?= $base ?>/unidades/actualizar-inline', {
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
