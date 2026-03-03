<?php
namespace Controllers;

use MVC\Router;
use Models\Inventario;
use Models\Sucursal;
use Models\Producto;

class InventarioController {

    public static function index(Router $router): void {
        isAuth();
        $sucursalId = (int)($_GET['sucursal_id'] ?? $_SESSION['sucursal_id'] ?? 0);
        $sucursales = Sucursal::getActivas();
        $stock      = $sucursalId ? Inventario::getStockSucursal($sucursalId) : [];

        $router->render('inventario/index', [
            'titulo'      => 'Inventario — Stock Actual',
            'sucursales'  => $sucursales,
            'stock'       => $stock,
            'sucursalId'  => $sucursalId,
        ]);
    }

    /**
     * Ajuste manual de inventario (agregar/quitar stock).
     */
    public static function ajuste(Router $router): void {
        isAuth();
        isRole(['admin', 'supervisor']);
        $sucursales = Sucursal::getActivas();
        $productos  = Producto::allConUnidad();
        $alertas    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sucursal_id = (int)$_POST['sucursal_id'];
            $producto_id = (int)$_POST['producto_id'];
            $cantidad    = (float)$_POST['cantidad'];
            $motivo      = htmlspecialchars(trim($_POST['motivo'] ?? ''));

            if (!$sucursal_id || !$producto_id || $cantidad == 0) {
                Inventario::setAlerta('danger', 'Completa todos los campos requeridos');
                $alertas = Inventario::getAlertas();
            } else {
                $ok = Inventario::ajustarStock($sucursal_id, $producto_id, $cantidad);
                if (!$ok) {
                    Inventario::setAlerta('danger', 'No hay inventario suficiente para descontar esa cantidad');
                    $alertas = Inventario::getAlertas();
                } else {
                    // Registrar movimiento
                    Inventario::registrarMovimiento([
                        'sucursal_id'    => $sucursal_id,
                        'producto_id'    => $producto_id,
                        'tipo'           => 'ajuste',
                        'signo'          => $cantidad > 0 ? 1 : -1,
                        'cantidad'       => abs($cantidad),
                        'referencia_tipo'=> 'ajuste_manual',
                    ]);
                    header('Location: /' . $_ENV['APP_NAME'] . '/inventario?ok=1&sucursal_id=' . $sucursal_id);
                    exit;
                }
            }
        }

        $router->render('inventario/ajuste', [
            'titulo'     => 'Ajuste de Inventario',
            'sucursales' => $sucursales,
            'productos'  => $productos,
            'alertas'    => $alertas,
        ]);
    }
}
