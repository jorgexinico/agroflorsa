<?php
namespace Controllers;

use MVC\Router;
use Models\Produccion;
use Models\ProduccionDetalle;
use Models\Sucursal;
use Models\Producto;
use Models\Inventario;
use Model\ActiveRecord;

class ProduccionesController {

    public static function index(Router $router): void {
        isAuth();
        isRole(['admin', 'supervisor']);
        
        $sucursalId = (int)($_SESSION['sucursal_id'] ?? 0);
        $producciones = Produccion::allConDetalle($sucursalId);
        $sucursal_actual = $sucursalId > 0
            ? (Sucursal::find($sucursalId)?->nombre ?? '')
            : 'Todas las sucursales';

        $router->render('producciones/index', [
            'titulo'          => 'Producciones',
            'producciones'    => $producciones,
            'sucursal_actual' => $sucursal_actual,
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        isRole(['admin', 'supervisor']);
        
        $sucursales  = Sucursal::getActivas();
        // Filtrar solo productos terminados (y tal vez mercaderia si lo desean, pero estrictamente terminados)
        $productos = Producto::queryPrep(
            "SELECT p.*, u.nombre AS unidad_nombre, u.abreviatura AS unidad_abreviatura,
                    m.nombre AS marca_nombre, c.nombre AS categoria_nombre
             FROM productos p
             JOIN unidades_medida u ON u.id = p.unidad_id
             LEFT JOIN marcas m ON m.id = p.marca_id
             LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE p.activo = 1 AND p.tipo = 'terminado'
             ORDER BY p.nombre"
        );
        
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sucursal_id  = (int)$_POST['sucursal_id'];
            $observacion  = htmlspecialchars(trim($_POST['observacion'] ?? ''));
            $usuario_id   = (int)($_SESSION['usuario_id'] ?? null);

            $items_producto   = $_POST['producto_id']      ?? [];
            $items_cantidad   = $_POST['cantidad']         ?? [];
            $items_costo      = $_POST['costo_unitario']   ?? [];
            $items_fecha_venc = $_POST['fecha_vencimiento'] ?? [];

            if (!$sucursal_id || empty($items_producto)) {
                Produccion::setAlerta('danger', 'Selecciona una sucursal y agrega al menos un producto');
                $alertas = Produccion::getAlertas();
            } else {
                $db = ActiveRecord::getDB();
                $db->beginTransaction();

                try {
                    // Crear encabezado de producción
                    $produccion              = new Produccion();
                    $produccion->sucursal_id = $sucursal_id;
                    $produccion->usuario_id  = $usuario_id;
                    $produccion->observacion = $observacion;
                    $produccion->estado      = 'terminada';
                    $produccion->fecha       = date('Y-m-d H:i:s');
                    $produccion->total       = 0;
                    $resultado               = $produccion->crear();
                    $produccion_id           = (int)$resultado['id'];

                    $totalProduccion = 0;

                    foreach ($items_producto as $i => $prod_id) {
                        $prod_id  = (int)$prod_id;
                        $cantidad = (float)($items_cantidad[$i] ?? 0);
                        $costo    = (float)($items_costo[$i]    ?? 0);

                        if ($prod_id <= 0 || $cantidad <= 0) continue;

                        $subtotal = round($cantidad * $costo, 2);
                        $totalProduccion += $subtotal;

                        $fv = trim($items_fecha_venc[$i] ?? '');
                        $fecha_venc = (!empty($fv)) ? $fv : null;

                        // ─── Lógica de Lote para Producción ───
                        $lote = new \Models\Lote();
                        $lote->producto_id       = $prod_id;
                        $lote->codigo_lote       = 'PROD-'.$produccion_id.'-P-'.$prod_id;
                        $lote->fecha_ingreso     = date('Y-m-d');
                        $lote->fecha_vencimiento = $fecha_venc;
                        $lote->costo_unitario    = $costo;
                        $resLote                 = $lote->crear();
                        $lote_id                 = (int)$resLote['id'];

                        // Insertar detalle
                        $detalle                   = new ProduccionDetalle();
                        $detalle->produccion_id    = $produccion_id;
                        $detalle->producto_id      = $prod_id;
                        $detalle->lote_id          = $lote_id;
                        $detalle->cantidad         = $cantidad;
                        $detalle->costo_unitario   = $costo;
                        $detalle->subtotal         = $subtotal;
                        $detalle->crear();

                        // Actualizar stock de sucursal destino
                        Inventario::ajustarStock($sucursal_id, $prod_id, $cantidad);
                        Inventario::ajustarStockLote($sucursal_id, $lote_id, $cantidad);

                        // Registrar movimiento de ingreso
                        Inventario::registrarMovimiento([
                            'sucursal_id'    => $sucursal_id,
                            'producto_id'    => $prod_id,
                            'lote_id'        => $lote_id,
                            'tipo'           => 'produccion_ingreso',
                            'referencia_tipo'=> 'producciones',
                            'referencia_id'  => $produccion_id,
                            'signo'          => 1,
                            'cantidad'       => $cantidad,
                            'costo_unitario' => $costo,
                        ]);

                        // ─── Actualizar precios de venta globales (igual que en compras) ───
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
                                
                                // Regla: Solo actualizar precio_compra (que aquí sirve como costo de producción base) si el nuevo costo es MAYOR.
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

                    // Actualizar total en la producción
                    ActiveRecord::ejecutar(
                        "UPDATE producciones SET total = :total WHERE id = :id",
                        [':total' => $totalProduccion, ':id' => $produccion_id]
                    );

                    $db->commit();
                    redirectTo('/producciones?ok=1');

                } catch (\Exception $e) {
                    $db->rollBack();
                    Produccion::setAlerta('danger', 'Error al registrar la producción: ' . $e->getMessage());
                    $alertas = Produccion::getAlertas();
                }
            }
        }

        $router->render('producciones/form', [
            'titulo'      => 'Nueva Producción',
            'sucursales'  => $sucursales,
            'productos'   => $productos,
            'alertas'     => $alertas,
        ]);
    }

    public static function detalleAjax(Router $router): void {
        isAuth();
        isRole(['admin', 'supervisor']);
        header('Content-Type: application/json');

        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            return;
        }

        $produccion = Produccion::fetchFirstRaw(
            "SELECT p.*, s.nombre AS sucursal_nombre, u.nombre AS usuario_nombre
             FROM producciones p
             JOIN sucursales s ON s.id = p.sucursal_id
             LEFT JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.id = :id",
            [':id' => $id]
        );

        if (!$produccion) {
            echo json_encode(['ok' => false, 'error' => 'Producción no encontrada']);
            return;
        }

        $detalle = ProduccionDetalle::getByProduccion($id);

        echo json_encode([
            'ok'         => true,
            'produccion' => $produccion,
            'detalle'    => $detalle
        ]);
        exit;
    }
}
