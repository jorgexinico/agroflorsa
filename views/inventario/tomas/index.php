<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0"><i class="bi bi-clipboard2-check-fill text-success me-2"></i>Sesiones de Toma de Inventario</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaToma">
        <i class="bi bi-plus-lg me-1"></i> Nueva Toma Físico
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th># ID</th>
                    <th>Sucursal</th>
                    <th>Iniciada Por</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($tomas)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">No hay sesiones de inventario registradas.</td>
                </tr>
                <?php else: ?>
                    <?php foreach($tomas as $t): ?>
                    <tr>
                        <td class="fw-bold">#<?= $t['id'] ?></td>
                        <td><?= s($t['sucursal_nombre']) ?></td>
                        <td><i class="bi bi-person-circle me-1 text-muted"></i><?= s($t['usuario_nombre']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($t['fecha_inicio'])) ?></td>
                        <td><?= $t['fecha_fin'] ? date('d/m/Y H:i', strtotime($t['fecha_fin'])) : '<span class="text-muted">-</span>' ?></td>
                        <td>
                            <?php if($t['estado'] === 'en_progreso'): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>En Progreso</span>
                            <?php elseif($t['estado'] === 'completada'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Completada</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Cancelada</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= s($t['observacion']) ?></td>
                        <td class="text-end">
                            <?php if($t['estado'] === 'en_progreso'): ?>
                            <a href="<?= $base ?>/inventario/tomas/conteo?id=<?= $t['id'] ?>" class="btn btn-sm btn-success shadow-sm">
                                <i class="bi bi-play-circle me-1"></i>Continuar
                            </a>
                            <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary" disabled>Finalizado</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nueva Toma -->
<div class="modal fade" id="modalNuevaToma" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="form-nueva-toma" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Iniciar Toma de Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small">
                    <i class="bi bi-info-circle me-1"></i> Se creará una plantilla con todos los productos activos de la sucursal seleccionada.
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Sucursal a inventariar</label>
                    <select name="sucursal_id" class="form-select" required>
                        <option value="">-- Seleccione Sucursal --</option>
                        <?php foreach($sucursales as $s): ?>
                        <option value="<?= $s->id ?>" <?= $sucursal_sesion == $s->id ? 'selected' : '' ?>><?= s($s->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Observación / Título (Opcional)</label>
                    <input type="text" name="observacion" class="form-control" placeholder="Ej: Conteo Semestral Junio">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btn-crear">
                    <i class="bi bi-play-fill"></i> Iniciar Sesión
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('form-nueva-toma').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-crear');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Iniciando...';
    
    try {
        const formData = new FormData(this);
        const res = await fetch('<?= $base ?>/inventario/tomas/crear', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if(data.ok) {
            window.location.href = '<?= $base ?>/inventario/tomas/conteo?id=' + data.id;
        } else {
            Swal.fire('Error', data.error || 'No se pudo crear', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-fill"></i> Iniciar Sesión';
        }
    } catch (err) {
        Swal.fire('Error', 'Problema de conexión', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-play-fill"></i> Iniciar Sesión';
    }
});
</script>
