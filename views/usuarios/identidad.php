<h1>Identidades de Login</h1>
<p>Selecciona la cuenta existente de Agroflorsa y su ID central en Login. Esta operación no cambia su rol ni historial.</p>
<?php if($message): ?><p role="status"><?= s($message) ?></p><?php endif; ?>
<form method="post">
<input type="hidden" name="csrf" value="<?= s($_SESSION['identity_csrf']) ?>">
<label for="local_id">Usuario de Agroflorsa</label>
<select class="form-select" name="local_id" id="local_id" required>
<?php foreach($users as $u): ?><option value="<?= (int)$u['id'] ?>"><?= s($u['nombre'].' ('.$u['usuario'].') — '.$u['rol']) ?></option><?php endforeach; ?>
</select>
<label for="subject">ID del usuario en Login</label>
<input class="form-control" name="subject" id="subject" inputmode="numeric" pattern="[1-9][0-9]*" required>
<button class="btn btn-primary mt-3">Guardar identidad</button>
</form>
<h2 class="mt-4">Identidades actuales</h2>
<?php foreach($users as $u): ?><p><?= s($u['usuario']) ?>: <?= s($u['portal_subject'] ?? 'Sin identidad central') ?> · Rol: <?= s($u['rol']) ?></p><?php endforeach; ?>
