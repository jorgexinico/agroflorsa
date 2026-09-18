<?php
// Ejecutado por app.php después de iniciar la sesión y conectar la BD local.
$path=rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/',PHP_URL_PATH),'/');
$publicPaths=array_map(fn($route)=>rtrim(url($route),'/'), ['/login','/sso','/sso/start','/logout']);
// Limpiar restos de sesiones anteriores; ya no se usan para autenticar.
unset($_SESSION['portal_refresh'], $_SESSION['portal_refresh_token'], $_SESSION['portal_expires'],
    $_SESSION['portal_exp'], $_SESSION['portal_checked'], $_SESSION['portal_last_check'], $_SESSION['activity_token']);
if (!in_array($path,$publicPaths,true)) {
    if (!isset($_SESSION['usuario_id'])) {
        // Conservar state: otra petición no debe interrumpir un SSO en curso.
        unset($_SESSION['usuario_nombre'], $_SESSION['usuario_rol'], $_SESSION['portal_subject'],
            $_SESSION['portal_issuer'], $_SESSION['turno_id'], $_SESSION['sucursal_id']);
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            http_response_code(401);
            exit('Debes iniciar sesión en Agroflorsa antes de realizar esta operación.');
        }
        header('Location: '.url('/sso/start'));
        exit;
    }
    $query=$db->prepare('SELECT id,nombre,rol FROM usuarios WHERE id=? AND activo=1');
    $query->execute([$_SESSION['usuario_id']]);
    $localUser=$query->fetch(PDO::FETCH_ASSOC);
    if (!$localUser) {
        $_SESSION=[];
        http_response_code(403);
        exit('Usuario local deshabilitado o inexistente.');
    }
    $_SESSION['usuario_nombre']=$localUser['nombre'];
    $_SESSION['usuario_rol']=$localUser['rol'];
}
