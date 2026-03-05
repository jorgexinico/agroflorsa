<?php
namespace Controllers;

use MVC\Router;
use Models\Compra;
use Models\CompraDetalle;
use Models\Sucursal;
use Models\Proveedor;
use Models\Producto;
use Models\Inventario;
use Model\ActiveRecord;

class ComprasController {

    public static function index(Router $router): void {
        isAuth();
        $sucursalId = (int)($_SESSION['sucursal_id'] ?? 0);
        $compras    = Compra::allConDetalle($sucursalId);

        $router->render('compras/index', [
            'titulo'  => 'Compras',
            'compras' => $compras,
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        $sucursales  = Sucursal::getActivas();
        $proveedores = Proveedor::getActivos();
        $productos   = Producto::allConUnidad();
        $alertas     = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Datos del encabezado
            $sucursal_id  = (int)$_POST['sucursal_id'];
            $proveedor_id = !empty($_POST['proveedor_id']) ? (int)$_POST['proveedor_id'] : null;
            $observacion  = htmlspecialchars(trim($_POST['observacion'] ?? ''));

            // Items del detalle enviados como arrays
            $items_producto = $_POST['producto_id'] ?? [];
            $items_cantidad  = $_POST['cantidad']     ?? [];
            $items_costo     = $_POST['costo_unitario'] ?? [];

            if (!$sucursal_id || empty($items_producto)) {
                Compra::setAlerta('danger', 'Selecciona una sucursal y agrega al menos un producto');
                $alertas = Compra::getAlertas();
            } else {
                $db = ActiveRecord::getDB();
                $db->beginTransaction();

                try {
                    // Crear encabezado
                    $compra              = new Compra();
                    $compra->sucursal_id  = $sucursal_id;
                    $compra->proveedor_id = $proveedor_id;
                    $compra->observacion  = $observacion;
                    $compra->estado       = 'recibida';
                    $compra->fecha        = date('Y-m-d');
                    $compra->total        = 0;
                    $resultado            = $compra->crear();
                    $compra_id            = (int)$resultado['id'];

                    $totalCompra = 0;

                    foreach ($items_producto as $i => $prod_id) {
                        $prod_id  = (int)$prod_id;
                        $cantidad = (float)($items_cantidad[$i] ?? 0);
                        $costo    = (float)($items_costo[$i]    ?? 0);

                        if ($prod_id <= 0 || $cantidad <= 0) continue;

                        $subtotal = round($cantidad * $costo, 2);
                        $totalCompra += $subtotal;

                        // Insertar detalle
                        $detalle               = new CompraDetalle();
                        $detalle->compra_id    = $compra_id;
                        $detalle->producto_id  = $prod_id;
                        $detalle->cantidad     = $cantidad;
                        $detalle->costo_unitario = $costo;
                        $detalle->subtotal     = $subtotal;
                        $detalle->crear();

                        // Actualizar stock
                        Inventario::ajustarStock($sucursal_id, $prod_id, $cantidad);

                        // Registrar movimiento
                        Inventario::registrarMovimiento([
                            'sucursal_id'    => $sucursal_id,
                            'producto_id'    => $prod_id,
                            'tipo'           => 'compra',
                            'referencia_tipo'=> 'compras',
                            'referencia_id'  => $compra_id,
                            'signo'          => 1,
                            'cantidad'       => $cantidad,
                            'costo_unitario' => $costo,
                        ]);
                    }

                    // Actualizar total en la compra
                    ActiveRecord::ejecutar(
                        "UPDATE compras SET total = :total WHERE id = :id",
                        [':total' => $totalCompra, ':id' => $compra_id]
                    );

                    $db->commit();
                    header('Location: /' . $_ENV['APP_NAME'] . '/compras?ok=1');
                    exit;

                } catch (\Exception $e) {
                    $db->rollBack();
                    Compra::setAlerta('danger', 'Error al registrar la compra: ' . $e->getMessage());
                    $alertas = Compra::getAlertas();
                }
            }
        }

        $router->render('compras/form', [
            'titulo'      => 'Nueva Compra',
            'sucursales'  => $sucursales,
            'proveedores' => $proveedores,
            'productos'   => $productos,
            'alertas'     => $alertas,
        ]);
    }
}
