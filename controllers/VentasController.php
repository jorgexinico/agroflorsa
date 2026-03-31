<?php
namespace Controllers;

use MVC\Router;
use Models\Venta;
use Models\VentaDetalle;
use Models\PagoVenta;
use Models\Turno;
use Models\Producto;
use Models\Cliente;
use Models\Inventario;
use Models\CuentaPorCobrar;
use Model\ActiveRecord;


/**
 * VentasController — Módulo de ventas con control de inventario y turno.
 */
class VentasController {

    public static function index(Router $router): void {
        isAuth();
        $sucursal_id = (int)($_SESSION['sucursal_id'] ?? 0);
        $fecha       = $_GET['fecha'] ?? date('Y-m-d');
        $rol         = $_SESSION['usuario_rol'] ?? '';

        if ($rol === 'admin') {
            // Admin: ve todas las sucursales si no tiene sucursal_id, o filtra por la que tenga
            if ($sucursal_id > 0) {
                $ventas = Venta::getBySucursa($sucursal_id, $fecha);
            } else {
                $ventas = Venta::fetchRaw(
                    "SELECT v.*, c.nombre AS cliente_nombre, s.nombre AS sucursal_nombre
                     FROM ventas v
                     LEFT JOIN clientes c ON c.id = v.cliente_id
                     LEFT JOIN sucursales s ON s.id = v.sucursal_id
                     WHERE v.estado != 'anulada' AND DATE(v.fecha) = :fecha
                     ORDER BY v.fecha DESC",
                    [':fecha' => $fecha]
                );
            }
        } else {
            // Vendedor: ve sus ventas (con o sin turno activo)
            $usuario_id = (int)($_SESSION['usuario_id'] ?? 0);
            $ventas = Venta::getByUsuario($usuario_id, $fecha);
        }

        // Obtener detalles para el acordeón
        $detallesPorVenta = [];
        if (!empty($ventas)) {
            $ids = array_column($ventas, 'id');
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            
            $todosLosDetalles = ActiveRecord::fetchRaw(
                "SELECT vd.*, p.nombre AS producto_nombre, u.abreviatura AS unidad
                 FROM venta_detalle vd
                 JOIN productos p ON p.id = vd.producto_id
                 JOIN unidades_medida u ON u.id = p.unidad_id
                 WHERE vd.venta_id IN ($placeholders)",
                $ids
            );

            foreach ($todosLosDetalles as $det) {
                $detallesPorVenta[$det['venta_id']][] = $det;
            }
        }

        $router->render('ventas/index', [
            'titulo'           => 'Historial de Ventas',
            'ventas'           => $ventas,
            'detallesPorVenta' => $detallesPorVenta,
            'fecha'            => $fecha,
        ]);
    }

    /**
     * Nueva venta — formulario para agregar productos al carrito.
     * Requiere turno activo en sesión.
     */
    public static function nueva(Router $router): void {
        isAuth();

        if (empty($_SESSION['turno_id'])) {
            redirectTo('/turnos/abrir');
        }

        $rol = $_SESSION['usuario_rol'] ?? '';
        $sucursal_id = (int)($_SESSION['sucursal_id'] ?? 0);
        
        // Si es admin, puede elegir otra sucursal vía GET para ver stock
        if ($rol === 'admin' && isset($_GET['sucursal_id'])) {
            $sucursal_id = (int)$_GET['sucursal_id'];
        }

        $productos = Producto::fetchRaw(
            "SELECT p.*, um.abreviatura AS unidad_abreviatura, iep.cantidad AS stock
             FROM productos p
             JOIN unidades_medida um ON um.id = p.unidad_id
             JOIN inventario_existencias_producto iep ON iep.producto_id = p.id
             WHERE iep.sucursal_id = :suc AND iep.cantidad > 0 AND p.activo = 1
             ORDER BY p.nombre ASC",
            [':suc' => $sucursal_id]
        );
        $clientes   = Cliente::getActivos();
        $sucursales = ($rol === 'admin') ? Sucursal::getActivas() : [];
        $alertas    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $turno_id    = (int)$_SESSION['turno_id'];
            $sucursal_id = (int)$_SESSION['sucursal_id'];
            $cliente_id  = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
            $tipo_pago   = $_POST['tipo_pago']   ?? 'contado';
            $observacion = htmlspecialchars(trim($_POST['observacion'] ?? ''));

            $items_producto = $_POST['producto_id']     ?? [];
            $items_cantidad  = $_POST['cantidad']         ?? [];
            $items_precio    = $_POST['precio_unitario']  ?? [];

            if (empty($items_producto)) {
                Venta::setAlerta('danger', 'Agrega al menos un producto a la venta');
                $alertas = Venta::getAlertas();
            } else {
                $db = ActiveRecord::getDB();
                $db->beginTransaction();

                try {
                    // ─── Validar stock de todos los items antes de proceder ───
                    foreach ($items_producto as $i => $prod_id) {
                        $prod_id  = (int)$prod_id;
                        $cantidad = (float)($items_cantidad[$i] ?? 0);
                        if ($prod_id <= 0 || $cantidad <= 0) continue;

                        $stock = Inventario::getStock($sucursal_id, $prod_id);
                        if ($stock < $cantidad) {
                            $prod = Producto::find($prod_id);
                            throw new \Exception(
                                "Stock insuficiente para «{$prod->nombre}»: disponible {$stock}, solicitado {$cantidad}"
                            );
                        }
                    }

                    // ─── Crear encabezado de venta ───
                    $venta              = new Venta();
                    $venta->turno_id    = $turno_id;
                    $venta->sucursal_id = $sucursal_id;
                    $venta->cliente_id  = $cliente_id;
                    $venta->tipo_pago   = $tipo_pago;
                    $venta->observacion = $observacion;
                    $venta->estado      = 'emitida';
                    $venta->fecha       = date('Y-m-d H:i:s');
                    $venta->total       = 0;
                    $resultado          = $venta->crear();
                    $venta_id           = (int)$resultado['id'];

                    $totalVenta = 0;

                    // ─── Crear detalle y descontar inventario ───
                    foreach ($items_producto as $i => $prod_id) {
                        $prod_id  = (int)$prod_id;
                        $cantidad = (float)($items_cantidad[$i] ?? 0);
                        $precio   = (float)($items_precio[$i]   ?? 0);

                        if ($prod_id <= 0 || $cantidad <= 0) continue;

                        $subtotal    = round($cantidad * $precio, 2);
                        $totalVenta += $subtotal;

                        // Costo unitario del último movimiento de compra
                        $costoUnitario = Inventario::getCostoUnitario($prod_id);

                        // ─── VALIDACIÓN: No vender por debajo del costo ───
                        if ($precio < $costoUnitario) {
                            $prod = Producto::find($prod_id);
                            throw new \Exception(
                                "Precio insuficiente para «{$prod->nombre}»: el costo es Q" . number_format($costoUnitario, 2) . " y el precio de venta es Q" . number_format($precio, 2)
                            );
                        }

                        // Detalle
                        $detalle                  = new VentaDetalle();
                        $detalle->venta_id        = $venta_id;
                        $detalle->producto_id     = $prod_id;
                        $detalle->cantidad        = $cantidad;
                        $detalle->precio_unitario  = $precio;
                        $detalle->costo_unitario  = $costoUnitario;
                        $detalle->subtotal        = $subtotal;
                        $detalle->crear();

                        // Descontar stock
                        Inventario::ajustarStock($sucursal_id, $prod_id, -$cantidad);

                        // Movimiento
                        Inventario::registrarMovimiento([
                            'sucursal_id'    => $sucursal_id,
                            'producto_id'    => $prod_id,
                            'tipo'           => 'venta',
                            'referencia_tipo'=> 'ventas',
                            'referencia_id'  => $venta_id,
                            'signo'          => -1,
                            'cantidad'       => $cantidad,
                        ]);
                    }

                    // ─── Actualizar total ───
                    ActiveRecord::ejecutar(
                        "UPDATE ventas SET total = :total WHERE id = :id",
                        [':total' => $totalVenta, ':id' => $venta_id]
                    );

                    // ─── Registrar pago de contado ───
                    if ($tipo_pago === 'contado') {
                        $metodo = $_POST['metodo_pago'] ?? 'efectivo';
                        $pago           = new PagoVenta();
                        $pago->venta_id = $venta_id;
                        $pago->metodo   = $metodo;
                        $pago->monto    = $totalVenta;
                        $pago->fecha    = date('Y-m-d H:i:s');
                        $pago->crear();
                    } else {
                        // Crédito: crear cuenta por cobrar si hay cliente
                        if ($cliente_id) {
                            ActiveRecord::ejecutar(
                                "INSERT INTO cuentas_por_cobrar
                                 (cliente_id, venta_id, total, pagado, saldo, estado)
                                 VALUES (:cli, :vid, :total, 0, :total, 'pendiente')",
                                [':cli' => $cliente_id, ':vid' => $venta_id, ':total' => $totalVenta]
                            );
                        }
                    }

                    $db->commit();
                    redirectTo('/ventas/detalle?id=' . $venta_id);

                } catch (\Exception $e) {
                    $db->rollBack();
                    Venta::setAlerta('danger', $e->getMessage());
                    $alertas = Venta::getAlertas();
                }
            }
        }

        $router->render('ventas/nueva', [
            'titulo'    => 'Nueva Venta',
            'productos' => $productos,
            'clientes'  => $clientes,
            'alertas'   => $alertas,
        ]);
    }

    public static function anular(Router $router): void {
        isAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('/ventas');
        }

        $id    = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $venta = Venta::find($id);

        if (!$venta || $venta->estado === 'anulada') {
            redirectTo('/ventas?err=invalid');
        }

        $db = ActiveRecord::getDB();
        $db->beginTransaction();

        try {
            // ─── Revertir stock de cada item ───
            $items = VentaDetalle::getByVenta($id);
            foreach ($items as $item) {
                $prod_id  = (int)$item['producto_id'];
                $cantidad = (float)$item['cantidad'];

                Inventario::ajustarStock($venta->sucursal_id, $prod_id, $cantidad);

                Inventario::registrarMovimiento([
                    'sucursal_id'    => $venta->sucursal_id,
                    'producto_id'    => $prod_id,
                    'tipo'           => 'anulacion',
                    'referencia_tipo'=> 'ventas',
                    'referencia_id'  => $id,
                    'signo'          => 1,
                    'cantidad'       => $cantidad,
                ]);
            }

            // ─── Marcar la venta como anulada ───
            ActiveRecord::ejecutar(
                "UPDATE ventas SET estado = 'anulada' WHERE id = :id",
                [':id' => $id]
            );

            // ─── Anular cuenta por cobrar si aplica ───
            ActiveRecord::ejecutar(
                "UPDATE cuentas_por_cobrar SET estado = 'anulada' WHERE venta_id = :vid",
                [':vid' => $id]
            );

            $db->commit();
            redirectTo('/ventas/detalle?id=' . $id . '&ok=anulada');

        } catch (\Exception $e) {
            $db->rollBack();
            redirectTo('/ventas/detalle?id=' . $id . '&err=db');
        }
    }

    public static function detalle(Router $router): void {
        isAuth();
        $id    = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $venta = Venta::fetchFirstRaw(
            "SELECT v.*, c.nombre AS cliente_nombre, s.nombre AS sucursal_nombre
             FROM ventas v
             LEFT JOIN clientes c ON c.id = v.cliente_id
             JOIN sucursales s ON s.id = v.sucursal_id
             WHERE v.id = :id",
            [':id' => $id]
        );

        if (!$venta) {
            redirectTo('/ventas');
        }

        $detalle = VentaDetalle::getByVenta($id);
        $pagos   = PagoVenta::getByVenta($id);
        $cxc     = CuentaPorCobrar::getByVenta($id);

        $router->render('ventas/detalle', [
            'titulo'  => 'Detalle de Venta #' . $id,
            'venta'   => $venta,
            'detalle' => $detalle,
            'pagos'   => $pagos,
            'cxc'     => $cxc,
        ]);
    }

    public static function envio(Router $router): void {
        isAuth();
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $venta = Venta::fetchFirstRaw(
            "SELECT v.*, c.nombre AS cliente_nombre, c.nit AS cliente_nit, 
                    c.direccion AS cliente_direccion, c.telefono AS cliente_telefono
             FROM ventas v
             LEFT JOIN clientes c ON c.id = v.cliente_id
             WHERE v.id = :id",
            [':id' => $id]
        );

        if (!$venta) {
            redirectTo('/ventas');
        }

        $detalle = VentaDetalle::getByVenta($id);

        $router->render('ventas/envio', [
            'venta'   => $venta,
            'detalle' => $detalle,
            'layout'  => 'auth' // Utiliza auth o uno vacío para evitar el sidebar en la impresión
        ]);
    }
}
