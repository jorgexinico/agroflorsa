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
        isRole(['admin']);
        // sucursal_id = 0 para admin sin turno → muestra todas las sucursales
        $sucursalId = (int)($_SESSION['sucursal_id'] ?? 0);
        $compras    = Compra::allConDetalle($sucursalId);
        $sucursal_actual = $sucursalId > 0
            ? (Sucursal::find($sucursalId)?->nombre ?? '')
            : 'Todas las sucursales';

        $router->render('compras/index', [
            'titulo'          => 'Compras',
            'compras'         => $compras,
            'sucursal_actual' => $sucursal_actual,
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        isRole(['admin']);
        $sucursales  = Sucursal::getActivas();
        $proveedores = Proveedor::getActivos();
        $productos   = Producto::allConUnidad();
        $alertas     = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Datos del encabezado
            $sucursal_id  = (int)$_POST['sucursal_id'];
            $proveedor_id = !empty($_POST['proveedor_id']) ? (int)$_POST['proveedor_id'] : null;
            $tipo_pago    = $_POST['tipo_pago'] ?? 'contado';
            $observacion  = htmlspecialchars(trim($_POST['observacion'] ?? ''));
            $factura      = htmlspecialchars(trim($_POST['factura'] ?? ''));

            // Items del detalle enviados como arrays
            $items_producto   = $_POST['producto_id']      ?? [];
            $items_cantidad   = $_POST['cantidad']         ?? [];
            $items_costo      = $_POST['costo_unitario']   ?? [];
            $items_fecha_venc = $_POST['fecha_vencimiento'] ?? [];

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
                    $compra->factura      = $factura ?: null;
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

                        // Fecha de vencimiento (solo si el producto la maneja)
                        $fv = trim($items_fecha_venc[$i] ?? '');
                        $fecha_venc = (!empty($fv)) ? $fv : null;

                        // ─── Lógica de Lote ───
                        $lote = new \Models\Lote();
                        $lote->producto_id       = $prod_id;
                        $lote->codigo_lote       = 'COMP-'.$compra_id.'-PROD-'.$prod_id;
                        $lote->fecha_ingreso     = date('Y-m-d');
                        $lote->fecha_vencimiento = $fecha_venc;
                        $lote->costo_unitario    = $costo;
                        $resLote                 = $lote->crear();
                        $lote_id                 = (int)$resLote['id'];

                        // Insertar detalle
                        $detalle                   = new CompraDetalle();
                        $detalle->compra_id         = $compra_id;
                        $detalle->producto_id       = $prod_id;
                        $detalle->lote_id           = $lote_id;
                        $detalle->cantidad          = $cantidad;
                        $detalle->costo_unitario    = $costo;
                        $detalle->subtotal          = $subtotal;
                        $detalle->fecha_vencimiento = $fecha_venc;
                        $detalle->crear();

                        // Actualizar stock
                        Inventario::ajustarStock($sucursal_id, $prod_id, $cantidad);
                        Inventario::ajustarStockLote($sucursal_id, $lote_id, $cantidad);

                        // Registrar movimiento
                        Inventario::registrarMovimiento([
                            'sucursal_id'    => $sucursal_id,
                            'producto_id'    => $prod_id,
                            'lote_id'        => $lote_id,
                            'tipo'           => 'compra',
                            'referencia_tipo'=> 'compras',
                            'referencia_id'  => $compra_id,
                            'signo'          => 1,
                            'cantidad'       => $cantidad,
                            'costo_unitario' => $costo,
                        ]);

                        // ─── Actualizar precios de venta globales ───
                        $p_publico   = (float)($_POST['precio_publico'][$i] ?? 0);
                        $p_mayorista = (float)($_POST['precio_mayorista'][$i] ?? 0);

                        if ($p_publico > 0 || $p_mayorista > 0 || $costo > 0) {
                            $producto_update = Producto::find($prod_id);
                            if ($producto_update) {
                                $hubo_cambio = false;
                                
                                if ($p_publico > 0) {
                                    $producto_update->precio_publico = $p_publico;
                                    $hubo_cambio = true;
                                }
                                if ($p_mayorista > 0) {
                                    $producto_update->precio_mayorista = $p_mayorista;
                                    $hubo_cambio = true;
                                }
                                
                                // Regla: Solo actualizar precio_compra si el nuevo costo es MAYOR al precio actual.
                                if ($costo > 0 && $costo > (float)$producto_update->precio_compra) {
                                    $producto_update->precio_compra = $costo;
                                    $hubo_cambio = true;
                                }
                                
                                if ($hubo_cambio) {
                                    $producto_update->actualizar();
                                }
                            }
                        }
                    }

                    // Actualizar total en la compra
                    ActiveRecord::ejecutar(
                        "UPDATE compras SET total = :total, tipo_pago = :tipo_p WHERE id = :id",
                        [':total' => $totalCompra, ':tipo_p' => $tipo_pago, ':id' => $compra_id]
                    );

                    // Registrar en CxP si es al crédito
                    if ($tipo_pago === 'credito' && $proveedor_id) {
                        ActiveRecord::ejecutar(
                            "INSERT INTO cuentas_por_pagar (proveedor_id, compra_id, total, pagado, saldo, estado) 
                             VALUES (:prov, :compra, :total, 0, :total, 'pendiente')",
                            [':prov' => $proveedor_id, ':compra' => $compra_id, ':total' => $totalCompra]
                        );
                    }

                    $db->commit();
                    redirectTo('/compras?ok=1');

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

    public static function detalleAjax(Router $router): void {
        isAuth();
        isRole(['admin']);
        header('Content-Type: application/json');

        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            return;
        }

        // 1. Cabecera
        $compra = Compra::fetchFirstRaw(
            "SELECT c.*, p.nombre AS proveedor_nombre, p.nit AS proveedor_nit, s.nombre AS sucursal_nombre
             FROM compras c
             LEFT JOIN proveedores p ON p.id = c.proveedor_id
             JOIN sucursales s ON s.id = c.sucursal_id
             WHERE c.id = :id",
            [':id' => $id]
        );

        if (!$compra) {
            echo json_encode(['ok' => false, 'error' => 'Compra no encontrada']);
            return;
        }

        // 2. Detalle de productos
        $detalle = CompraDetalle::getByCompra($id);

        // 3. CxP si es al crédito
        $cxp = null;
        if ($compra['tipo_pago'] === 'credito') {
            $cxpQuery = \Model\ActiveRecord::fetchRaw(
                "SELECT * FROM cuentas_por_pagar WHERE compra_id = :id LIMIT 1",
                [':id' => $id]
            );
            $cxp = $cxpQuery[0] ?? null;
        }

        echo json_encode([
            'ok'      => true,
            'compra'  => $compra,
            'detalle' => $detalle,
            'cxp'     => $cxp
        ]);
        exit;
    }
}
