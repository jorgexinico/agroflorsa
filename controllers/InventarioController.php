<?php
namespace Controllers;

use MVC\Router;
use Models\AjusteInventario;
use Models\AjusteDetalle;
use Models\Inventario;
use Models\Sucursal;
use Models\Producto;
use Models\Lote;
use Model\ActiveRecord;

class InventarioController {

    public static function index(Router $router): void {
        isAuth();
        $sucursalId = (int)($_GET['sucursal_id'] ?? $_SESSION['sucursal_id'] ?? 0);
        $sucursales = Sucursal::getActivas();
        $stock      = $sucursalId ? Inventario::getStockSucursal($sucursalId) : [];
        $productos  = Producto::allConUnidad(); // Todos los productos para el buscador

        $router->render('inventario/index', [
            'titulo'      => 'Inventario — Stock Actual',
            'sucursales'  => $sucursales,
            'stock'       => $stock,
            'sucursalId'  => $sucursalId,
            'productos'   => $productos,
        ]);
    }

    /**
     * Ajuste manual de inventario (agregar/quitar stock).
     */
    public static function ajuste(Router $router): void {
        isAuth();
        isRole(['admin']);
        $sucursales = Sucursal::getActivas();
        $productos  = Producto::allConUnidad();
        $alertas    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sucursal_id = (int)$_POST['sucursal_id'];
            $motivo      = htmlspecialchars(trim($_POST['motivo'] ?? ''));
            $usuario_id  = (int)($_SESSION['usuario_id'] ?? 0);

            // Determinar si es carga masiva (vienen arreglos) o simple
            $producto_ids = $_POST['producto_id'] ?? [];
            if (!is_array($producto_ids)) {
                $producto_ids = [$producto_ids];
            }

            if (!$sucursal_id || empty($producto_ids)) {
                Inventario::setAlerta('danger', 'Completa todos los campos requeridos y selecciona al menos un producto');
                $alertas = Inventario::getAlertas();
            } else {
                $db = ActiveRecord::getDB();
                $db->beginTransaction();

                try {
                    // Crear el encabezado del ajuste
                    $ajuste = new AjusteInventario();
                    $ajuste->sucursal_id = $sucursal_id;
                    $ajuste->fecha       = date('Y-m-d H:i:s');
                    $ajuste->motivo      = $motivo ?: 'Ajuste masivo/Carga inicial';
                    $ajuste->usuario_id  = $usuario_id;
                    $res_ajuste = $ajuste->crear();
                    $ajuste_id  = (int)$res_ajuste['id'];

                    foreach ($producto_ids as $i => $pid) {
                        $pid      = (int)$pid;
                        $cantidad = (float)($_POST['cantidad'][$i] ?? 0);
                        if ($pid <= 0) continue;

                        // Solo registrar si hay cantidad o si se están actualizando precios
                        // Si la cantidad es 0, no ajustamos stock pero quizás se subieron precios.
                        // El usuario dijo "solo agregar si y el precio", asumimos que si envían la fila es porque quieren procesarla.

                        // 0. Crear Lote si aplica
                        $lote_id = null;
                        $fv = trim($_POST['fecha_vencimiento'][$i] ?? '');
                        if (!empty($fv) && $cantidad != 0) {
                            $lote = new Lote();
                            $lote->producto_id = $pid;
                            $lote->codigo_lote = 'AJUS-'.$ajuste_id.'-PROD-'.$pid;
                            $lote->fecha_ingreso = date('Y-m-d');
                            $lote->fecha_vencimiento = $fv;
                            $lote->costo_unitario = (float)($_POST['precio_compra'][$i] ?? 0);
                            $resLote = $lote->crear();
                            $lote_id = (int)$resLote['id'];
                        }

                        // 1. Registrar detalle del ajuste
                        $detalle = new AjusteDetalle();
                        $detalle->ajuste_id  = $ajuste_id;
                        $detalle->producto_id = $pid;
                        $detalle->lote_id     = $lote_id;
                        $detalle->cantidad    = $cantidad;
                        $detalle->crear();

                        // 2. Ajustar stock en la sucursal
                        if ($cantidad != 0) {
                            $ok = Inventario::ajustarStock($sucursal_id, $pid, $cantidad);
                            if (!$ok) {
                                throw new \Exception("Stock insuficiente para el producto ID $pid");
                            }
                            if ($lote_id) {
                                Inventario::ajustarStockLote($sucursal_id, $lote_id, $cantidad);
                            }

                            // 3. Registrar movimiento de inventario
                            $costo_unitario = (float)($_POST['precio_compra'][$i] ?? 0);
                            Inventario::registrarMovimiento([
                                'sucursal_id'    => $sucursal_id,
                                'producto_id'    => $pid,
                                'lote_id'        => $lote_id,
                                'tipo'           => 'ajuste',
                                'signo'          => $cantidad > 0 ? 1 : -1,
                                'cantidad'       => abs($cantidad),
                                'referencia_tipo'=> 'ajuste',
                                'referencia_id'  => $ajuste_id,
                                'costo_unitario' => $costo_unitario > 0 ? $costo_unitario : null,
                            ]);
                        }

                        // 4. Actualizar precios del producto (Global)
                        $p_pub = (float)($_POST['precio_publico'][$i] ?? 0);
                        $p_may = (float)($_POST['precio_mayorista'][$i] ?? 0);

                        if ($p_pub > 0 || $p_may > 0) {
                            $p_update = Producto::find($pid);
                            if ($p_update) {
                                if ($p_pub > 0) $p_update->precio_publico   = $p_pub;
                                if ($p_may > 0) $p_update->precio_mayorista = $p_may;
                                $p_update->actualizar();
                            }
                        }
                    }

                    $db->commit();
                    redirectTo('/inventario?ok=1&sucursal_id=' . $sucursal_id);
                    exit;

                } catch (\Exception $e) {
                    $db->rollBack();
                    Inventario::setAlerta('danger', $e->getMessage());
                    $alertas = Inventario::getAlertas();
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

    /**
     * Kardex de producto (historial de movimientos).
     */
    public static function kardex(Router $router): void {
        isAuth();
        $sucursal_id = (int)($_GET['sucursal_id'] ?? $_SESSION['sucursal_id'] ?? 0);
        $producto_id = (int)($_GET['producto_id'] ?? 0);

        if (!$producto_id || !$sucursal_id) {
            redirectTo('/inventario');
            exit;
        }

        $producto  = Producto::find($producto_id);
        $sucursal  = Sucursal::find($sucursal_id);
        
        // Obtener movimientos registrados
        $movimientos = ActiveRecord::fetchRaw(
            "SELECT m.*, u.nombre AS usuario_nombre
             FROM inventario_movimientos m
             LEFT JOIN ajustes_inventario a ON a.id = m.referencia_id AND m.referencia_tipo = 'ajuste'
             LEFT JOIN usuarios u ON u.id = a.usuario_id
             WHERE m.sucursal_id = :suc AND m.producto_id = :prod
             ORDER BY m.creado_en DESC, m.id DESC",
            [':suc' => $sucursal_id, ':prod' => $producto_id]
        );

        $router->render('inventario/kardex', [
            'titulo'      => "Kardex: {$producto->nombre} — {$sucursal->nombre}",
            'movimientos' => $movimientos,
            'producto'    => $producto,
            'sucursal'    => $sucursal
        ]);
    }

    /**
     * AJAX: Retorna productos con stock > 0 para una sucursal específica.
     */
    public static function productosPorSucursalAjax(): void {
        isAuth();
        $sucursal_id = (int)($_GET['sucursal_id'] ?? 0);

        if (!$sucursal_id) {
            echo json_encode(['ok' => false, 'error' => 'Sucursal no válida']);
            return;
        }

        $productos = ActiveRecord::fetchRaw(
            "SELECT p.id, p.nombre, p.sku, u.abreviatura AS unidad, iep.cantidad AS stock
             FROM productos p
             JOIN inventario_existencias_producto iep ON iep.producto_id = p.id
             JOIN unidades_medida u ON u.id = p.unidad_id
             WHERE iep.sucursal_id = :suc AND iep.cantidad > 0 AND p.activo = 1
             ORDER BY p.nombre ASC",
            [':suc' => $sucursal_id]
        );

        echo json_encode([
            'ok' => true,
            'productos' => $productos
        ]);
    }

    /**
     * AJAX: Registra un ingreso individual de stock y actualiza precios.
     */
    public static function ingresoRapidoAjax(): void {
        isAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);

        if (!$datos || empty($datos['producto_id']) || empty($datos['sucursal_id'])) {
            echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
            return;
        }

        $sucursal_id = (int)$datos['sucursal_id'];
        $producto_id = (int)$datos['producto_id'];
        $cantidad    = (float)($datos['cantidad'] ?? 0);
        $costo       = (float)($datos['costo'] ?? 0);
        $p_publico   = (float)($datos['precio_publico'] ?? 0);
        $p_mayorista = (float)($datos['precio_mayorista'] ?? 0);
        $fv          = trim($datos['fecha_vencimiento'] ?? '');
        $usuario_id  = (int)($_SESSION['usuario_id'] ?? 0);

        $db = ActiveRecord::getDB();
        $db->beginTransaction();

        try {
            // 1. Crear Ajuste (Encabezado) para trazabilidad
            $ajuste = new AjusteInventario();
            $ajuste->sucursal_id = $sucursal_id;
            $ajuste->fecha       = date('Y-m-d H:i:s');
            $ajuste->motivo      = $datos['motivo'] ?? 'Ingreso rápido/Carga inicial';
            $ajuste->usuario_id  = $usuario_id;
            $res = $ajuste->crear();
            $ajuste_id = (int)$res['id'];
            
            // 1.5 Crear Lote si aplica
            $lote_id = null;
            if (!empty($fv) && $cantidad != 0) {
                $lote = new Lote();
                $lote->producto_id       = $producto_id;
                $lote->codigo_lote       = 'AJUS-'.$ajuste_id.'-PROD-'.$producto_id;
                $lote->fecha_ingreso     = date('Y-m-d');
                $lote->fecha_vencimiento = $fv;
                $lote->costo_unitario    = $costo;
                $resLote = $lote->crear();
                $lote_id = (int)$resLote['id'];
            }

            // 2. Detalle
            $detalle = new AjusteDetalle();
            $detalle->ajuste_id   = $ajuste_id;
            $detalle->producto_id = $producto_id;
            $detalle->lote_id     = $lote_id;
            $detalle->cantidad    = $cantidad;
            $detalle->crear();

            // 3. Ajustar Stock
            if ($cantidad != 0) {
                $ok = Inventario::ajustarStock($sucursal_id, $producto_id, $cantidad);
                if (!$ok) throw new \Exception("Error al ajustar stock");
                if ($lote_id) Inventario::ajustarStockLote($sucursal_id, $lote_id, $cantidad);

                // 4. Registrar Movimiento
                Inventario::registrarMovimiento([
                    'sucursal_id'     => $sucursal_id,
                    'producto_id'     => $producto_id,
                    'lote_id'         => $lote_id,
                    'tipo'            => 'ajuste',
                    'signo'           => $cantidad > 0 ? 1 : -1,
                    'cantidad'        => abs($cantidad),
                    'referencia_tipo' => 'ajuste',
                    'referencia_id'   => $ajuste_id,
                    'costo_unitario'  => $costo > 0 ? $costo : null
                ]);
            }

            // 5. Actualizar precios del producto
            $producto = Producto::find($producto_id);
            if ($producto) {
                $hubo_cambio = false;
                if ($p_publico > 0) {
                    $producto->precio_publico = $p_publico;
                    $hubo_cambio = true;
                }
                if ($p_mayorista > 0) {
                    $producto->precio_mayorista = $p_mayorista;
                    $hubo_cambio = true;
                }
                // Conservadurismo contable: Solo sobreescribir precio_compra si el nuevo ingreso es más caro
                if ($costo > 0 && $costo > (float)$producto->precio_compra) {
                    $producto->precio_compra = $costo;
                    $hubo_cambio = true;
                }
                if ($hubo_cambio) {
                    $producto->actualizar();
                }
            }

            $db->commit();
            
            error_reporting(0);
            if (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'mensaje' => 'Ingreso procesado correctamente']);
            exit;

        } catch (\Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_reporting(0);
            if (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Movimientos de Inventario (Kardex General).
     */
    public static function movimientos(Router $router): void {
        isAuth();
        
        $mi_rol       = $_SESSION['usuario_rol'] ?? '';
        $mi_sucursal  = (int)($_SESSION['sucursal_id'] ?? 0);

        // Si es vendedor, forzar su sucursal y prohibir ver otras
        if ($mi_rol === 'vendedor') {
            $sucursal_id = $mi_sucursal;
        } else {
            $sucursal_id  = (int)($_GET['sucursal_id'] ?? 0);
        }

        $producto_id  = (int)($_GET['producto_id'] ?? 0);
        $tipo        = $_GET['tipo'] ?? '';
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin    = $_GET['fecha_fin']    ?? date('Y-m-d');

        $where = ["DATE(m.creado_en) BETWEEN :f1 AND :f2"];
        $params = [':f1' => $fecha_inicio, ':f2' => $fecha_fin];

        if ($sucursal_id) {
            $where[] = "m.sucursal_id = :suc";
            $params[':suc'] = $sucursal_id;
        }
        if ($producto_id) {
            $where[] = "m.producto_id = :prod";
            $params[':prod'] = $producto_id;
        }
        if ($tipo) {
            $where[] = "m.tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $query = "SELECT m.*, p.nombre AS producto_nombre, p.sku, s.nombre AS sucursal_nombre, um.abreviatura AS unidad
                  FROM inventario_movimientos m
                  JOIN productos p ON p.id = m.producto_id
                  JOIN sucursales s ON s.id = m.sucursal_id
                  JOIN unidades_medida um ON um.id = p.unidad_id
                  WHERE " . implode(' AND ', $where) . "
                  ORDER BY m.creado_en DESC, m.id DESC";

        $movimientos = ActiveRecord::fetchRaw($query, $params);
        
        $sucursales = Sucursal::getActivas();
        $productos  = Producto::all();

        $router->render('inventario/movimientos', [
            'titulo'       => 'Movimientos de Inventario',
            'movimientos'  => $movimientos,
            'sucursales'   => $sucursales,
            'productos'    => $productos,
            'filtros'      => [
                'sucursal_id'  => $sucursal_id,
                'producto_id'  => $producto_id,
                'tipo'         => $tipo,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin'    => $fecha_fin
            ]
        ]);
    }

    /**
     * AJAX: Obtiene los lotes activos de un producto en una sucursal específica.
     */
    public static function verLotesActivosAjax(): void {
        header('Content-Type: application/json');
        isAuth();
        $sucursal_id = (int)($_GET['sucursal_id'] ?? 0);
        $producto_id = (int)($_GET['producto_id'] ?? 0);

        if (!$sucursal_id || !$producto_id) {
            echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
            return;
        }

        $lotes = ActiveRecord::fetchRaw(
            "SELECT l.codigo_lote, l.fecha_vencimiento, l.costo_unitario, iel.cantidad
             FROM inventario_existencias_lote iel
             JOIN lotes l ON l.id = iel.lote_id
             WHERE iel.sucursal_id = :suc AND l.producto_id = :prod AND iel.cantidad > 0
             ORDER BY l.fecha_vencimiento ASC",
            [':suc' => $sucursal_id, ':prod' => $producto_id]
        );

        echo json_encode([
            'ok'    => true,
            'lotes' => $lotes
        ]);
        exit;
    }

    /**
     * AJAX: Regulariza stock huérfano asignándolo a un lote para fragmentar stock.
     */
    public static function asignarVencimientoExistenteAjax(): void {
        header('Content-Type: application/json');
        isAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);

        if (!$datos || empty($datos['sucursal_id']) || empty($datos['producto_id']) || empty($datos['cantidad']) || empty($datos['fecha_vencimiento'])) {
            echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
            return;
        }

        $sucursal_id = (int)$datos['sucursal_id'];
        $producto_id = (int)$datos['producto_id'];
        $cantidad    = (float)$datos['cantidad'];
        $fecha_venc  = trim($datos['fecha_vencimiento']);

        try {
            // 1. Obtener stock global real de ese producto
            $stock_global = Inventario::getStock($sucursal_id, $producto_id);

            // 2. Obtener lo que ya está amarrado a lotes
            $stock_en_lotes = 0;
            $resLotes = ActiveRecord::fetchRaw(
                "SELECT SUM(iel.cantidad) as total 
                 FROM inventario_existencias_lote iel
                 JOIN lotes l ON l.id = iel.lote_id
                 WHERE iel.sucursal_id = :suc AND l.producto_id = :prod",
                [':suc' => $sucursal_id, ':prod' => $producto_id]
            );
            if ($resLotes && isset($resLotes[0]['total'])) {
                $stock_en_lotes = (float)$resLotes[0]['total'];
            }

            // 3. El stock huérfano es la diferencia
            $stock_huerfano = round($stock_global - $stock_en_lotes, 3);

            if ($cantidad > $stock_huerfano) {
                echo json_encode(['ok' => false, 'error' => "La cantidad ($cantidad) excede tu stock huérfano disponible ($stock_huerfano)."]);
                return;
            }

            $db = ActiveRecord::getDB();
            $db->beginTransaction();

            // 4. Crear el lote (o buscar si ya existe uno igual? Es mejor crear uno nuevo de ajuste)
            $lote = new Lote();
            $lote->producto_id       = $producto_id;
            $lote->codigo_lote       = 'REG-'.time().'-PROD-'.$producto_id; // Código único al vuelo
            $lote->fecha_ingreso     = date('Y-m-d');
            $lote->fecha_vencimiento = $fecha_venc;
            $lote->costo_unitario    = 0;
            $resLote                 = $lote->crear();
            $lote_id                 = (int)$resLote['id'];

            // 5. Asignar ese pedazo de stock al lote (No movemos el stock global, ya que ya estaba ahí)
            Inventario::ajustarStockLote($sucursal_id, $lote_id, $cantidad);

            // Notas: No insertamos inventario_movimientos porque el stock global y contablemente se mantiene en cero suma y cero resta.
            // Si el usuario quiere trazabilidad del lote se podría agregar, pero no afecta costos ni global quantity.

            $db->commit();
            echo json_encode(['ok' => true]);

        } catch(\Exception $e) {
            $db = ActiveRecord::getDB();
            if ($db->inTransaction()) $db->rollback();
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
