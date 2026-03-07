<?php
/**
 * Layout principal de Agroflorsa — Sidebar + Topbar
 * La variable $contenido se inyecta desde Router::render()
 * La variable $layout puede ser 'auth' para páginas sin sidebar
 */
$appName = $_ENV['APP_NAME'] ?? 'agroflorsa';
$base    = '/' . $appName;

$usuarioNombre = $_SESSION['usuario_nombre'] ?? '';
$usuarioRol    = $_SESSION['usuario_rol']    ?? '';
$turnoId       = $_SESSION['turno_id']       ?? null;
$sucursalId    = $_SESSION['sucursal_id']    ?? null;

// Detectar ruta actual para marcar ítem activo
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function isActive(string $path): string {
    global $currentUri;
    return str_starts_with($currentUri, $path) ? 'active' : '';
}

// Si el layout es 'auth', mostrar pantalla sin sidebar
if (($layout ?? '') === 'auth') : ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= s($titulo ?? 'Agroflorsa') ?> — Agroflorsa</title>
  <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
</head>
<body class="ag-auth-body">
  <?= $contenido ?>
  <script src="<?= asset('build/js/app.js') ?>"></script>
</body>
</html>
<?php return; endif; ?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= s($titulo ?? 'Dashboard') ?> — Agroflorsa</title>
  <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
</head>
<body class="ag-body">

<!-- ═══════════════════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════════════════ -->
<aside class="ag-sidebar" id="sidebar">
  <!-- Logo -->
  <div class="ag-sidebar__brand">
    <i class="bi bi-flower3 text-success"></i>
    <span>Agroflorsa</span>
  </div>

  <!-- Turno activo badge -->
  <?php if ($turnoId): ?>
  <div class="ag-sidebar__turno">
    <span class="badge bg-success w-100 text-start px-3 py-2" style="font-size:.75rem">
      <i class="bi bi-circle-fill blink me-1" style="font-size:.5rem"></i>
      Turno #<?= $turnoId ?> activo
    </span>
  </div>
  <?php endif; ?>

  <nav class="ag-sidebar__nav">

    <!-- Dashboard -->
    <a href="<?= $base ?>/dashboard" class="ag-nav-item <?= isActive($base . '/dashboard') ?>">
      <i class="bi bi-speedometer2"></i>
      <span>Dashboard</span>
    </a>

    <!-- OPERACIONES -->
    <div class="ag-nav-section">Operaciones</div>

    <?php if ($turnoId): ?>
    <a href="<?= $base ?>/ventas/nueva" class="ag-nav-item <?= isActive($base . '/ventas/nueva') ?>">
      <i class="bi bi-cart-plus-fill text-success"></i>
      <span>Nueva Venta</span>
    </a>
    <?php endif; ?>

    <a href="<?= $base ?>/ventas" class="ag-nav-item <?= isActive($base . '/ventas') ?>">
      <i class="bi bi-receipt"></i>
      <span>Ventas</span>
    </a>

    <a href="<?= $base ?>/turnos" class="ag-nav-item <?= isActive($base . '/turnos') ?>">
      <i class="bi bi-clock-history"></i>
      <span>Turnos</span>
    </a>

    <a href="<?= $base ?>/compras" class="ag-nav-item <?= isActive($base . '/compras') ?>">
      <i class="bi bi-truck"></i>
      <span>Compras</span>
    </a>

    <a href="<?= $base ?>/cuentas-cobrar" class="ag-nav-item <?= isActive($base . '/cuentas-cobrar') ?>">
      <i class="bi bi-file-earmark-text"></i>
      <span>Cuentas x Cobrar</span>
    </a>

    <!-- INVENTARIO -->
    <div class="ag-nav-section">Inventario</div>

    <a href="<?= $base ?>/inventario" class="ag-nav-item <?= isActive($base . '/inventario') ?>">
      <i class="bi bi-boxes"></i>
      <span>Stock Actual</span>
    </a>

    <a href="<?= $base ?>/inventario/ajuste" class="ag-nav-item <?= isActive($base . '/inventario/ajuste') ?>">
      <i class="bi bi-pencil-square"></i>
      <span>Ajuste Manual</span>
    </a>

    <!-- CATÁLOGOS -->
    <div class="ag-nav-section">Catálogos</div>

    <a href="<?= $base ?>/productos" class="ag-nav-item <?= isActive($base . '/productos') ?>">
      <i class="bi bi-box-seam"></i>
      <span>Productos</span>
    </a>

    <a href="<?= $base ?>/clientes" class="ag-nav-item <?= isActive($base . '/clientes') ?>">
      <i class="bi bi-people"></i>
      <span>Clientes</span>
    </a>

    <a href="<?= $base ?>/proveedores" class="ag-nav-item <?= isActive($base . '/proveedores') ?>">
      <i class="bi bi-building"></i>
      <span>Proveedores</span>
    </a>

    <a href="<?= $base ?>/unidades" class="ag-nav-item <?= isActive($base . '/unidades') ?>">
      <i class="bi bi-rulers"></i>
      <span>Unidades</span>
    </a>

    <!-- ADMIN -->
    <?php if (in_array($usuarioRol, ['admin', 'supervisor'])): ?>
    <div class="ag-nav-section">Administración</div>

    <a href="<?= $base ?>/sucursales" class="ag-nav-item <?= isActive($base . '/sucursales') ?>">
      <i class="bi bi-shop-window"></i>
      <span>Sucursales</span>
    </a>

    <a href="<?= $base ?>/usuarios" class="ag-nav-item <?= isActive($base . '/usuarios') ?>">
      <i class="bi bi-people-fill"></i>
      <span>Usuarios</span>
    </a>
    <?php endif; ?>

  </nav>

  <!-- Usuario en sidebar inferior -->
  <div class="ag-sidebar__footer">
    <div class="ag-user-info">
      <div class="ag-user-avatar"><i class="bi bi-person-circle"></i></div>
      <div>
        <div class="ag-user-name"><?= s($usuarioNombre) ?></div>
        <div class="ag-user-role"><?= s(ucfirst($usuarioRol)) ?></div>
      </div>
    </div>
    <a href="<?= $base ?>/logout" class="ag-logout-btn" title="Cerrar sesión">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</aside>

<!-- ══════════════════════════════════════════════════════
     MAIN CONTENT
═══════════════════════════════════════════════════════ -->
<div class="ag-main" id="main-content">

  <!-- Topbar -->
  <header class="ag-topbar">
    <button class="ag-topbar__toggle" id="sidebarToggle">
      <i class="bi bi-list fs-4"></i>
    </button>
    <h1 class="ag-topbar__title"><?= s($titulo ?? '') ?></h1>
    <div class="ag-topbar__actions">
      <?php if (!$turnoId): ?>
        <a href="<?= $base ?>/turnos/abrir" class="btn btn-sm btn-success">
          <i class="bi bi-play-circle me-1"></i>Abrir Turno
        </a>
      <?php else: ?>
        <a href="<?= $base ?>/turnos/detalle?id=<?= $turnoId ?>" class="btn btn-sm btn-outline-success">
          <i class="bi bi-eye me-1"></i>Mi Turno
        </a>
      <?php endif; ?>
    </div>
  </header>

  <!-- Flash messages: leídos por app.js via URLSearchParams -->
  <?php if (!empty($_GET['ok'])): ?>
  <span id="flash-ok-msg" class="d-none" data-msg="<?= s(mensajeOk((int)$_GET['ok'])) ?>"></span>
  <?php endif; ?>

  <!-- Contenido de la vista -->
  <main class="ag-content">
    <?= $contenido ?>
  </main>

  <footer class="ag-footer">
    Agroflorsa &copy; <?= date('Y') ?> &mdash; Sistema de Control Administrativo
  </footer>
</div><!-- /.ag-main -->

<script src="<?= asset('build/js/app.js') ?>"></script>
</body>
</html>