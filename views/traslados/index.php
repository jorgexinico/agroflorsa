<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0"><?= $titulo ?></h2>
    <a href="/<?= $_ENV['APP_NAME'] ?>/traslados/crear" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nuevo Traslado
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($traslados)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No hay traslados registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($traslados as $t): ?>
                    <tr>
                        <td>#<?= $t['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($t['fecha'])) ?></td>
                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary border px-2"><?= s($t['origen']) ?></span></td>
                        <td><span class="badge bg-success bg-opacity-10 text-success border px-2"><?= s($t['destino']) ?></span></td>
                        <td>
                            <?php if ($t['estado'] === 'enviado'): ?>
                                <span class="badge bg-warning text-dark">ENVIADO (PENDIENTE)</span>
                            <?php else: ?>
                                <span class="badge bg-success">RECIBIDO</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <?php 
                                    $mi_suc = (int)($_SESSION['sucursal_id'] ?? 0);
                                    $mi_rol = $_SESSION['usuario_rol'] ?? '';
                                    if ($t['estado'] === 'enviado' && ($mi_rol === 'admin' || $mi_suc === (int)$t['sucursal_destino_id'])): 
                                ?>
                                <form action="/<?= $_ENV['APP_NAME'] ?>/traslados/recibir" method="POST" onsubmit="return confirm('¿Confirmas que has recibido estos productos en tu sucursal?')">
                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm" title="Recibir Mercadería">
                                        <i class="bi bi-box-seam me-1"></i>Recibir
                                    </button>
                                </form>
                                <?php endif; ?>

                                <a href="/<?= $_ENV['APP_NAME'] ?>/traslados/detalle?id=<?= $t['id'] ?>" class="btn btn-outline-dark btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
