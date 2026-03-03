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
use Model\ActiveRecord;

/**
 * VentasController — Módulo de ventas con control de inventario y turno.
 */
class VentasController {

    public static function index(Router $router): void {
        isAuth();
        $sucursal_id = $_SESSION['sucursal_id'] ?? 0;
        $fecha       = $_GET['fecha'] ?? date('Y-m-d');
        $ventas      = $sucursal_id ? Venta::getBySucursa((int)$sucursal_id, $fecha) : [];

        $router->render('ventas/index', [
            'titulo'  => 'Ventas',
            'ventas'  => $ventas,
            'fecha'   => $fecha,
        ]);
    }

    /**
     * Nueva venta — formulario para agregar productos al carrito.
     * Requiere turno activo en sesión.
     */
    public static function nueva(Router $router): void {
        isAuth();

        if (empty($_SESSION['turno_id'])) {
            header('Location: /' . $_ENV['APP_NAME'] . '/turnos/abrir');
            exit;
        }

        $productos = Producto::allConUnidad();
        $clientes  = Cliente::getActivos();
        $alertas   = [];

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

                        // Detalle
                        $detalle                  = new VentaDetalle();
                        $detalle->venta_id        = $venta_id;
                        $detalle->producto_id     = $prod_id;
                        $detalle->cantidad        = $cantidad;
                        $detalle->precio_unitario  = $precio;
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
                    header('Location: /' . $_ENV['APP_NAME'] . '/ventas/detalle?id=' . $venta_id);
                    exit;

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
            header('Location: /' . $_ENV['APP_NAME'] . '/ventas');
            exit;
        }

        $detalle = VentaDetalle::getByVenta($id);
        $pagos   = PagoVenta::getByVenta($id);

        $router->render('ventas/detalle', [
            'titulo'  => 'Detalle de Venta #' . $id,
            'venta'   => $venta,
            'detalle' => $detalle,
            'pagos'   => $pagos,
        ]);
    }
}
