<?php // views/sucursales/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0"><?= count($sucursales) ?> sucursales registradas</p>
  <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
  <a href="<?= $base ?>/sucursales/crear" class="btn btn-success btn-sm">
    <i class="bi bi-plus-circle me-1"></i>Nueva sucursal
  </a>
  <?php endif; ?>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>#</th><th>Nombre</th><th>Tipo</th><th>Dirección</th><th>Estado</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($sucursales as $s): ?>
        <tr data-id="<?= $s->id ?>">
          <td><?= $s->id ?></td>
          <td>
            <input type="text" class="form-control form-control-sm border-0 bg-transparent fw-semibold p-0 w-100" 
                   value="<?= s($s->nombre) ?>"
                   onchange="actualizarInline(<?= $s->id ?>, 'nombre', this.value)"
                   <?= $_SESSION['usuario_rol'] !== 'admin' ? 'disabled' : '' ?>>
          </td>
          <td><span class="badge <?= $s->tipo==='agroservicio'?'bg-success':'bg-info' ?>"><?= s($s->tipo) ?></span></td>
          <td>
            <input type="text" class="form-control form-control-sm border-0 bg-transparent text-muted p-0 w-100" 
                   value="<?= s($s->direccion ?? '') ?>" placeholder="—"
                   onchange="actualizarInline(<?= $s->id ?>, 'direccion', this.value)"
                   <?= $_SESSION['usuario_rol'] !== 'admin' ? 'disabled' : '' ?>>
          </td>
          <td><?= $s->activa ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-secondary">Inactiva</span>' ?></td>
          <td class="text-end text-nowrap">
            <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
            <a href="<?= $base ?>/sucursales/editar?id=<?= $s->id ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
            <?php if ($s->activa): ?>
            <form method="POST" action="<?= $base ?>/sucursales/eliminar" class="d-inline ag-confirm-form">
              <input type="hidden" name="id" value="<?= $s->id ?>">
              <button type="button" class="btn btn-sm btn-outline-danger ag-confirm-btn"
                      data-titulo="¿Desactivar sucursal?" data-nombre="<?= s($s->nombre) ?>">
                <i class="bi bi-power"></i>
              </button>
            </form>
            <?php else: ?>
            <form method="POST" action="<?= $base ?>/sucursales/reactivar" class="d-inline ag-confirm-form">
              <input type="hidden" name="id" value="<?= $s->id ?>">
              <button type="button" class="btn btn-sm btn-outline-success ag-confirm-btn"
                      data-titulo="¿Reactivar sucursal?" data-nombre="<?= s($s->nombre) ?>">
                <i class="bi bi-arrow-counterclockwise"></i>
              </button>
            </form>
            <?php endif; ?>
            <?php endif; ?>
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

        const response = await fetch('<?= $base ?>/sucursales/actualizar-inline', {
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
