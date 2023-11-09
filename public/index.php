<?php
require_once __DIR__ . '/../includes/app.php';


use MVC\Router;
use Controllers\AppController;
use Controllers\BandasController;
use Controllers\ActividadController;
use Controllers\FormularioController;

$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

$router->get('/', [AppController::class, 'index']);

//bandas
$router->get('/Bandas', [BandasController::class, 'index']);
$router->post('/API/Bandas/guardar', [BandasController::class, 'guardarAPI']);
$router->get('/API/Bandas/buscar', [BandasController::class, 'buscarAPI']);
$router->post('/API/Bandas/modificar', [BandasController::class, 'modificarAPI']);
$router->post('/API/Bandas/eliminar', [BandasController::class, 'eliminarAPI']);
//Actividad
$router->get('/Actividad', [ActividadController::class, 'index']);
$router->post('/API/Actividad/guardar', [ActividadController::class, 'guardarAPI']);
$router->get('/API/Actividad/buscar', [ActividadController::class, 'buscarAPI']);
$router->post('/API/Actividad/modificar', [ActividadController::class, 'modificarAPI']);
$router->post('/API/Actividad/eliminar', [ActividadController::class, 'eliminarAPI']);
//Formulario
$router->get('/Formulario', [FormularioController::class, 'index']);
$router->post('/API/Formulario/guardar', [FormularioController::class, 'guardarAPI']);
$router->get('/API/Formulario/buscar', [FormularioController::class, 'buscarAPI']);
$router->post('/API/Formulario/modificar', [FormularioController::class, 'modificarAPI']);
$router->post('/API/Formulario/eliminar', [FormularioController::class, 'eliminarAPI']);

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();
