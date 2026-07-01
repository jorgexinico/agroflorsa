<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['nombre'] = 'Prueba Error Dos';
$_POST['sku'] = '';
$_POST['precio_publico'] = 100;
$_POST['precio_mayorista'] = 80;
$_POST['precio_compra'] = 50;
$_POST['marca_id'] = '';
$_POST['categoria_id'] = '';
$_POST['unidad_id'] = 1;
$_POST['tipo'] = 'mercaderia';
$_POST['toma_id'] = 1;

$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_PORT'] = '3306';

require 'includes/app.php';

// Mock isAuth() and session
$_SESSION['usuario_id'] = 1;
$_SESSION['sucursal_id'] = 1;

if (!function_exists('isAuth')) {
    function isAuth() {}
}

ob_start();
Controllers\TomasInventarioController::crearProductoRapido();
$out = ob_get_clean();
echo "OUTPUT:\n$out\n";
