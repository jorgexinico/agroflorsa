<?php
/**
 * public/index.php — Punto de entrada único de Agroflorsa ERP
 * Todas las rutas se registran aquí.
 */
require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AppController;
use Controllers\AuthController;
use Controllers\SucursalesController;
use Controllers\ClientesController;
use Controllers\ProveedoresController;
use Controllers\ProductosController;
use Controllers\UnidadesController;
use Controllers\InventarioController;
use Controllers\ComprasController;
use Controllers\TurnosController;
use Controllers\VentasController;
use Controllers\UsuariosController;

$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

// ── AUTH ─────────────────────────────────────────────
$router->get('/login',   [AuthController::class, 'login']);
$router->post('/login',  [AuthController::class, 'login']);
$router->get('/logout',  [AuthController::class, 'logout']);

// ── DASHBOARD ────────────────────────────────────────
$router->get('/',         [AppController::class, 'index']);
$router->get('',          [AppController::class, 'index']);
$router->get('/dashboard',[AppController::class, 'index']);

// ── SUCURSALES ───────────────────────────────────────
$router->get('/sucursales',         [SucursalesController::class, 'index']);
$router->get('/sucursales/crear',   [SucursalesController::class, 'crear']);
$router->post('/sucursales/crear',  [SucursalesController::class, 'crear']);
$router->get('/sucursales/editar',  [SucursalesController::class, 'editar']);
$router->post('/sucursales/editar', [SucursalesController::class, 'editar']);
$router->post('/sucursales/eliminar',[SucursalesController::class, 'eliminar']);

// ── CLIENTES ─────────────────────────────────────────
$router->get('/clientes',         [ClientesController::class, 'index']);
$router->get('/clientes/crear',   [ClientesController::class, 'crear']);
$router->post('/clientes/crear',  [ClientesController::class, 'crear']);
$router->get('/clientes/editar',  [ClientesController::class, 'editar']);
$router->post('/clientes/editar', [ClientesController::class, 'editar']);
$router->post('/clientes/eliminar',[ClientesController::class, 'eliminar']);

// ── PROVEEDORES ──────────────────────────────────────
$router->get('/proveedores',          [ProveedoresController::class, 'index']);
$router->get('/proveedores/crear',    [ProveedoresController::class, 'crear']);
$router->post('/proveedores/crear',   [ProveedoresController::class, 'crear']);
$router->get('/proveedores/editar',   [ProveedoresController::class, 'editar']);
$router->post('/proveedores/editar',  [ProveedoresController::class, 'editar']);
$router->post('/proveedores/eliminar',[ProveedoresController::class, 'eliminar']);

// ── PRODUCTOS ────────────────────────────────────────
$router->get('/productos',            [ProductosController::class, 'index']);
$router->get('/productos/crear',      [ProductosController::class, 'crear']);
$router->post('/productos/crear',     [ProductosController::class, 'crear']);
$router->get('/productos/editar',     [ProductosController::class, 'editar']);
$router->post('/productos/editar',    [ProductosController::class, 'editar']);
$router->post('/productos/eliminar',  [ProductosController::class, 'eliminar']);
$router->get('/productos/importar',   [ProductosController::class, 'importar']);
$router->post('/productos/importar',  [ProductosController::class, 'importar']);

// ── UNIDADES DE MEDIDA ───────────────────────────────
$router->get('/unidades',          [UnidadesController::class, 'index']);
$router->get('/unidades/crear',    [UnidadesController::class, 'crear']);
$router->post('/unidades/crear',   [UnidadesController::class, 'crear']);
$router->get('/unidades/editar',   [UnidadesController::class, 'editar']);
$router->post('/unidades/editar',  [UnidadesController::class, 'editar']);
$router->post('/unidades/eliminar',[UnidadesController::class, 'eliminar']);

// ── INVENTARIO ───────────────────────────────────────
$router->get('/inventario',         [InventarioController::class, 'index']);
$router->get('/inventario/ajuste',  [InventarioController::class, 'ajuste']);
$router->post('/inventario/ajuste', [InventarioController::class, 'ajuste']);

// ── COMPRAS ──────────────────────────────────────────
$router->get('/compras',        [ComprasController::class, 'index']);
$router->get('/compras/crear',  [ComprasController::class, 'crear']);
$router->post('/compras/crear', [ComprasController::class, 'crear']);

// ── TURNOS ───────────────────────────────────────────
$router->get('/turnos',          [TurnosController::class, 'index']);
$router->get('/turnos/abrir',    [TurnosController::class, 'abrir']);
$router->post('/turnos/abrir',   [TurnosController::class, 'abrir']);
$router->get('/turnos/detalle',  [TurnosController::class, 'detalle']);
$router->get('/turnos/cerrar',   [TurnosController::class, 'cerrar']);
$router->post('/turnos/cerrar',  [TurnosController::class, 'cerrar']);
$router->get('/turnos/reporte',  [TurnosController::class, 'reporte']);

// ── VENTAS ───────────────────────────────────────────
$router->get('/ventas',          [VentasController::class, 'index']);
$router->get('/ventas/nueva',    [VentasController::class, 'nueva']);
$router->post('/ventas/nueva',   [VentasController::class, 'nueva']);
$router->get('/ventas/detalle',  [VentasController::class, 'detalle']);

// ── USUARIOS ─────────────────────────────────────────
$router->get('/usuarios',         [UsuariosController::class, 'index']);
$router->get('/usuarios/crear',   [UsuariosController::class, 'crear']);
$router->post('/usuarios/crear',  [UsuariosController::class, 'crear']);
$router->get('/usuarios/editar',  [UsuariosController::class, 'editar']);
$router->post('/usuarios/editar', [UsuariosController::class, 'editar']);
$router->post('/usuarios/toggle', [UsuariosController::class, 'eliminar']);

// ── COMPRUEBA RUTAS ──────────────────────────────────
$router->comprobarRutas();
