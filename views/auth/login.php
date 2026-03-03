<?php
// views/auth/login.php
if (!empty($alertas)) {
    foreach ($alertas as $tipo => $msgs) {
        foreach ($msgs as $msg) {
            echo "<div class=\"alert alert-{$tipo} alert-dismissible fade show\">{$msg}<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>";
        }
    }
}
?>
<div class="ag-login-card">
  <div class="ag-login-card__logo">
    <i class="bi bi-flower3"></i>
    <h2>Agroflorsa</h2>
    <p>Sistema de control administrativo</p>
  </div>

  <?php if (!empty($alertas)): foreach ($alertas as $tipo => $msgs): foreach ($msgs as $msg): ?>
  <div class="alert alert-<?= s($tipo) ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= s($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endforeach; endforeach; endif; ?>

  <form method="POST" action="">
    <div class="mb-3">
      <label for="usuario" class="form-label fw-semibold">Usuario</label>
      <div class="input-group">
        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
        <input type="text" name="usuario" id="usuario"
               class="form-control" placeholder="Ingresa tu usuario"
               autocomplete="username" autofocus required>
      </div>
    </div>
    <div class="mb-4">
      <label for="password" class="form-label fw-semibold">Contraseña</label>
      <div class="input-group">
        <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
        <input type="password" name="password" id="password"
               class="form-control" placeholder="••••••••"
               autocomplete="current-password" required>
      </div>
    </div>
    <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
      <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sesión
    </button>
  </form>

  <p class="text-center text-muted mt-4 mb-0" style="font-size:.75rem">
    Agroflorsa &copy; <?= date('Y') ?>
  </p>
</div>
