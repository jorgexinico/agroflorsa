<?php // views/clientes/index.php ?>
<?php
$ok = (int)($_GET['ok'] ?? 0);
$msgs = [1 => 'Cliente creado.', 2 => 'Cliente actualizado.', 3 => 'Cliente desactivado.', 4 => 'Cliente reactivado.'];
if ($ok && isset($msgs[$ok])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?= $msgs[$ok] ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0"><?= count($clientes) ?> clientes</p>
  <a href="<?= $base ?>/clientes/crear" class="btn btn-success btn-sm">
    <i class="bi bi-plus-circle me-1"></i>Nuevo cliente
  </a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Nombre</th><th>NIT</th><th>Teléfono</th><th>Estado</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($clientes as $c): ?>
        <tr data-id="<?= $c->id ?>">
          <!-- Editar Nombre -->
          <td>
            <input type="text" class="form-control form-control-sm border-0 bg-transparent fw-semibold p-0 w-100" 
                   value="<?= s($c->nombre) ?>"
                   onchange="actualizarInline(<?= $c->id ?>, 'nombre', this.value)">
          </td>
          <!-- Editar NIT -->
          <td>
            <input type="text" class="form-control form-control-sm border-0 bg-transparent text-muted p-0 w-100" 
                   value="<?= s($c->nit ?? '') ?>" placeholder="—"
                   onchange="actualizarInline(<?= $c->id ?>, 'nit', this.value)">
          </td>
          <!-- Editar Teléfono -->
          <td>
            <input type="text" class="form-control form-control-sm border-0 bg-transparent p-0 w-100" 
                   value="<?= s($c->telefono ?? '') ?>" placeholder="—"
                   onchange="actualizarInline(<?= $c->id ?>, 'telefono', this.value)">
          </td>
          <td><?= $c->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?></td>
          <td class="text-end text-nowrap">
            <a href="<?= $base ?>/clientes/editar?id=<?= $c->id ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
            <?php if ($c->activo): ?>
            <form method="POST" action="<?= $base ?>/clientes/eliminar" class="d-inline ag-confirm-form">
              <input type="hidden" name="id" value="<?= $c->id ?>">
              <button type="button" class="btn btn-sm btn-outline-danger ag-confirm-btn"
                      data-titulo="¿Desactivar cliente?" data-nombre="<?= s($c->nombre) ?>">
                <i class="bi bi-power"></i>
              </button>
            </form>
            <?php else: ?>
            <form method="POST" action="<?= $base ?>/clientes/activar" class="d-inline ag-confirm-form">
              <input type="hidden" name="id" value="<?= $c->id ?>">
              <button type="button" class="btn btn-sm btn-outline-success ag-confirm-btn"
                      data-titulo="¿Reactivar cliente?" data-nombre="<?= s($c->nombre) ?>">
                <i class="bi bi-check-circle"></i>
              </button>
            </form>
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

        const response = await fetch('<?= $base ?>/clientes/actualizar-inline', {
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
