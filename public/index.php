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
use Controllers\CuentasPorCobrarController;
use Controllers\MarcasController;
use Controllers\CategoriasController;

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
$router->post('/sucursales/actualizar-inline',[SucursalesController::class, 'actualizarInline']);

// ── CLIENTES ─────────────────────────────────────────
$router->get('/clientes',         [ClientesController::class, 'index']);
$router->get('/clientes/crear',   [ClientesController::class, 'crear']);
$router->post('/clientes/crear',  [ClientesController::class, 'crear']);
$router->get('/clientes/editar',  [ClientesController::class, 'editar']);
$router->post('/clientes/editar', [ClientesController::class, 'editar']);
$router->post('/clientes/eliminar',[ClientesController::class, 'eliminar']);
$router->post('/clientes/activar', [ClientesController::class, 'activar']);
$router->post('/clientes/actualizar-inline', [ClientesController::class, 'actualizarInline']);

// ── PROVEEDORES ──────────────────────────────────────
$router->get('/proveedores',          [ProveedoresController::class, 'index']);
$router->get('/proveedores/crear',    [ProveedoresController::class, 'crear']);
$router->post('/proveedores/crear',   [ProveedoresController::class, 'crear']);
$router->get('/proveedores/editar',   [ProveedoresController::class, 'editar']);
$router->post('/proveedores/editar',  [ProveedoresController::class, 'editar']);
$router->post('/proveedores/eliminar',[ProveedoresController::class, 'eliminar']);
$router->post('/proveedores/activar', [ProveedoresController::class, 'activar']);
$router->post('/proveedores/actualizar-inline', [ProveedoresController::class, 'actualizarInline']);

// ── PRODUCTOS ────────────────────────────────────────
$router->get('/productos',            [ProductosController::class, 'index']);
$router->get('/productos/crear',      [ProductosController::class, 'crear']);
$router->post('/productos/crear',     [ProductosController::class, 'crear']);
$router->get('/productos/editar',     [ProductosController::class, 'editar']);
$router->post('/productos/editar',    [ProductosController::class, 'editar']);
$router->post('/productos/eliminar',  [ProductosController::class, 'eliminar']);
$router->get('/productos/importar',   [ProductosController::class, 'importar']);
$router->post('/productos/importar',  [ProductosController::class, 'importar']);
$router->post('/productos/actualizar-inline', [ProductosController::class, 'actualizarInline']);

// ── UNIDADES DE MEDIDA ───────────────────────────────
$router->get('/unidades',          [UnidadesController::class, 'index']);
$router->get('/unidades/crear',    [UnidadesController::class, 'crear']);
$router->post('/unidades/crear',   [UnidadesController::class, 'crear']);
$router->get('/unidades/editar',   [UnidadesController::class, 'editar']);
$router->post('/unidades/editar',  [UnidadesController::class, 'editar']);
$router->post('/unidades/eliminar',[UnidadesController::class, 'eliminar']);
$router->post('/unidades/actualizar-inline',[UnidadesController::class, 'actualizarInline']);

// ── MARCAS ───────────────────────────────────────────
$router->get('/marcas',          [MarcasController::class, 'index']);
$router->get('/marcas/crear',    [MarcasController::class, 'crear']);
$router->post('/marcas/crear',   [MarcasController::class, 'crear']);
$router->get('/marcas/editar',   [MarcasController::class, 'editar']);
$router->post('/marcas/editar',  [MarcasController::class, 'editar']);
$router->post('/marcas/eliminar',[MarcasController::class, 'eliminar']);
$router->post('/marcas/actualizar-inline',[MarcasController::class, 'actualizarInline']);

// ── CATEGORÍAS ───────────────────────────────────────
$router->get('/categorias',          [CategoriasController::class, 'index']);
$router->get('/categorias/crear',    [CategoriasController::class, 'crear']);
$router->post('/categorias/crear',   [CategoriasController::class, 'crear']);
$router->get('/categorias/editar',   [CategoriasController::class, 'editar']);
$router->post('/categorias/editar',  [CategoriasController::class, 'editar']);
$router->post('/categorias/eliminar',[CategoriasController::class, 'eliminar']);
$router->post('/categorias/actualizar-inline',[CategoriasController::class, 'actualizarInline']);

// ── INVENTARIO ───────────────────────────────────────
$router->get('/inventario',         [InventarioController::class, 'index']);
$router->get('/inventario/ajuste',  [InventarioController::class, 'ajuste']);
$router->post('/inventario/ajuste', [InventarioController::class, 'ajuste']);

// ── COMPRAS ──────────────────────────────────────────
$router->get('/compras',        [ComprasController::class, 'index']);
$router->get('/compras/crear',  [ComprasController::class, 'crear']);
$router->post('/compras/crear', [ComprasController::class, 'crear']);
$router->get('/compras/detalle-ajax', [ComprasController::class, 'detalleAjax']);

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
$router->get('/ventas/envio',    [VentasController::class, 'envio']);
$router->post('/ventas/anular',  [VentasController::class, 'anular']);

// ── CUENTAS POR COBRAR ─────────────────────────────
$router->get('/cuentas-cobrar',          [CuentasPorCobrarController::class, 'index']);
$router->get('/cuentas-cobrar/detalle',  [CuentasPorCobrarController::class, 'detalle']);
$router->post('/cuentas-cobrar/abonar',  [CuentasPorCobrarController::class, 'abonar']);

// ── CUENTAS POR PAGAR ──────────────────────────────
$router->get('/cuentas-pagar',           [\Controllers\CuentasPorPagarController::class, 'index']);
$router->get('/cuentas-pagar/detalle',   [\Controllers\CuentasPorPagarController::class, 'detalle']);
$router->post('/cuentas-pagar/abonar',   [\Controllers\CuentasPorPagarController::class, 'abonar']);

// ── USUARIOS ─────────────────────────────────────────
$router->get('/usuarios',         [UsuariosController::class, 'index']);
$router->get('/usuarios/crear',   [UsuariosController::class, 'crear']);
$router->post('/usuarios/crear',  [UsuariosController::class, 'crear']);
$router->get('/usuarios/editar',  [UsuariosController::class, 'editar']);
$router->post('/usuarios/editar', [UsuariosController::class, 'editar']);
$router->post('/usuarios/toggle', [UsuariosController::class, 'eliminar']);

// ── COMPRUEBA RUTAS ──────────────────────────────────
$router->comprobarRutas();
