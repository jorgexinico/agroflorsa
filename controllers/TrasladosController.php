<?php
namespace Controllers;

use MVC\Router;
use Models\Traslado;
use Models\TrasladoDetalle;
use Models\Sucursal;
use Models\Producto;
use Models\Inventario;
use Model\ActiveRecord;

class TrasladosController {

    public static function index(Router $router): void {
        isAuth();
        $traslados = Traslado::fetchRaw(
            "SELECT t.*, so.nombre AS origen, sd.nombre AS destino 
             FROM traslados t
             JOIN sucursales so ON so.id = t.sucursal_origen_id
             JOIN sucursales sd ON sd.id = t.sucursal_destino_id
             ORDER BY t.fecha DESC"
        );
        
        $usuarioRol = $_SESSION['usuario_rol'] ?? '';
        $sucursalId = (int)($_SESSION['sucursal_id'] ?? 0);

        $router->render('traslados/index', [
            'titulo'     => 'Traslados de Mercadería',
            'traslados'  => $traslados,
            'usuarioRol' => $usuarioRol,
            'sucursalId' => $sucursalId
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        $sucursales = Sucursal::getActivas();
        $productos  = Producto::allConUnidad();
        $alertas    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $origen_id  = (int)$_POST['sucursal_origen_id'];
            $destino_id = (int)$_POST['sucursal_destino_id'];
            $nota       = htmlspecialchars(trim($_POST['nota'] ?? ''));
            $items_prod = $_POST['producto_id'] ?? [];
            $items_cant = $_POST['cantidad']    ?? [];

            if ($origen_id === $destino_id) {
                Traslado::setAlerta('danger', 'La sucursal de origen y destino no pueden ser la misma');
                $alertas = Traslado::getAlertas();
            } elseif (empty($items_prod)) {
                Traslado::setAlerta('danger', 'Agrega al menos un producto al traslado');
                $alertas = Traslado::getAlertas();
            } else {
                $db = ActiveRecord::getDB();
                $db->beginTransaction();

                try {
                    // Validar stock en origen
                    foreach ($items_prod as $i => $pid) {
                        $pid = (int)$pid;
                        $cant = (float)$items_cant[$i];
                        if ($pid <= 0 || $cant <= 0) continue;

                        $stock = Inventario::getStock($origen_id, $pid);
                        if ($stock < $cant) {
                            $p = Producto::find($pid);
                            throw new \Exception("Stock insuficiente en origen para {$p->nombre} (Disponible: $stock)");
                        }
                    }

                    // Crear traslado
                    $traslado = new Traslado();
                    $traslado->sucursal_origen_id  = $origen_id;
                    $traslado->sucursal_destino_id = $destino_id;
                    $traslado->fecha              = date('Y-m-d H:i:s');
                    $traslado->estado             = 'enviado'; // Paso 1: Enviado
                    $traslado->nota               = $nota;
                    $res = $traslado->crear();
                    $traslado_id = (int)($res['id'] ?? 0);

                    foreach ($items_prod as $i => $pid) {
                        $pid  = (int)$pid;
                        $cant = (float)$items_cant[$i];
                        if ($pid <= 0 || $cant <= 0) continue;

                        $costoPosible = Inventario::getCostoUnitario($pid);

                        // Detalle
                        $detalle = new TrasladoDetalle();
                        $detalle->traslado_id    = $traslado_id;
                        $detalle->producto_id    = $pid;
                        $detalle->cantidad       = $cant;
                        $detalle->costo_unitario = $costoPosible;
                        $detalle->crear();

                        // 1. SOLO Restar en origen al momento de enviar
                        Inventario::ajustarStock($origen_id, $pid, -$cant);
                        Inventario::registrarMovimiento([
                            'sucursal_id'    => $origen_id,
                            'producto_id'    => $pid,
                            'tipo'           => 'traslado_salida',
                            'referencia_tipo'=> 'traslados',
                            'referencia_id'  => $traslado_id,
                            'signo'          => -1,
                            'cantidad'       => $cant,
                            'costo_unitario' => $costoPosible
                        ]);
                        
                        // NOTA: No sumamos en destino todavía. Eso se hace en el método recibir().
                    }

                    $db->commit();
                    redirectTo('/traslados?ok=1');
                    exit;

                } catch (\Exception $e) {
                    $db->rollBack();
                    Traslado::setAlerta('danger', $e->getMessage());
                    $alertas = Traslado::getAlertas();
                    $datos = $_POST; // Preservar datos para la vista
                }
            }
        }

        $router->render('traslados/crear', [
            'titulo'     => 'Nuevo Traslado (Envío)',
            'sucursales' => $sucursales,
            'productos'  => $productos,
            'alertas'    => $alertas,
            'datos'      => $datos ?? []
        ]);
    }

    /**
     * Paso 2: Recibir el traslado en la sucursal de destino.
     */
    public static function recibir(Router $router): void {
        isAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('/traslados');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $traslado = Traslado::find($id);

        if (!$traslado || !in_array($traslado->estado, ['enviado', 'pendiente'])) {
            redirectTo('/traslados?err=invalid');
            exit;
        }

        // Validación: Solo el personal de la sucursal de destino, admin o supervisor puede recibir
        $mi_sucursal = (int)($_SESSION['sucursal_id'] ?? 0);
        $mi_rol = $_SESSION['usuario_rol'] ?? '';
        $esAdminOSupervisor = in_array($mi_rol, ['admin', 'supervisor']);
        
        if (!$esAdminOSupervisor && $mi_sucursal !== (int)$traslado->sucursal_destino_id) {
             redirectTo('/traslados?err=not_authorized');
             exit;
        }

        $db = ActiveRecord::getDB();
        $db->beginTransaction();

        try {
            $detalles = TrasladoDetalle::getByTraslado($id);

            foreach ($detalles as $item) {
                $pid  = (int)$item['producto_id'];
                $cant = (float)$item['cantidad'];
                $costo = (float)$item['costo_unitario'];

                // Sumar en destino
                Inventario::ajustarStock($traslado->sucursal_destino_id, $pid, $cant);
                
                // Registrar movimiento de entrada
                Inventario::registrarMovimiento([
                    'sucursal_id'    => $traslado->sucursal_destino_id,
                    'producto_id'    => $pid,
                    'tipo'           => 'traslado_entrada',
                    'referencia_tipo'=> 'traslados',
                    'referencia_id'  => $id,
                    'signo'          => 1,
                    'cantidad'       => $cant,
                    'costo_unitario' => $costo
                ]);
            }

            // Actualizar estado
            $traslado->estado = 'recibido';
            $traslado->actualizar();

            $db->commit();
            redirectTo('/traslados?ok=2&type=traslado_recibido');
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            redirectTo('/traslados?err=db');
            exit;
        }
    }

    public static function rechazar(Router $router): void {
        isAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('/traslados');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $traslado = Traslado::find($id);

        if (!$traslado || !in_array($traslado->estado, ['enviado', 'pendiente'])) {
            redirectTo('/traslados?err=invalid');
            exit;
        }

        // Validación: Solo el personal de la sucursal de destino, admin o supervisor puede rechazar
        $mi_sucursal = (int)($_SESSION['sucursal_id'] ?? 0);
        $mi_rol = $_SESSION['usuario_rol'] ?? '';
        $esAdminOSupervisor = in_array($mi_rol, ['admin', 'supervisor']);
        
        if (!$esAdminOSupervisor && $mi_sucursal !== (int)$traslado->sucursal_destino_id) {
             redirectTo('/traslados?err=not_authorized');
             exit;
        }

        $db = ActiveRecord::getDB();
        $db->beginTransaction();

        try {
            $detalles = TrasladoDetalle::getByTraslado($id);

            foreach ($detalles as $item) {
                $pid   = (int)$item['producto_id'];
                $cant  = (float)$item['cantidad'];
                $costo = (float)$item['costo_unitario'];

                // Como fue rechazado, DEVOLVEMOS el stock a la sucursal de ORIGEN
                Inventario::ajustarStock($traslado->sucursal_origen_id, $pid, $cant);
                
                // Registrar movimiento de retorno a origen
                Inventario::registrarMovimiento([
                    'sucursal_id'    => $traslado->sucursal_origen_id,
                    'producto_id'    => $pid,
                    'tipo'           => 'traslado_entrada', // Revertimos usando traslado_entrada
                    'referencia_tipo'=> 'traslados',
                    'referencia_id'  => $id,
                    'signo'          => 1,
                    'cantidad'       => $cant,
                    'costo_unitario' => $costo
                ]);
            }

            // Actualizar estado a rechazado
            $traslado->estado = 'rechazado';
            $traslado->actualizar();

            $db->commit();
            redirectTo('/traslados?ok=2&type=traslado_rechazado');
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            file_put_contents('c:/proyectos/agroflorsa/error_rechazo.log', $e->getMessage() . PHP_EOL, FILE_APPEND);
            redirectTo('/traslados?err=db');
            exit;
        }
    }

    public static function detalleAjax(Router $router): void {
        isAuth();
        header('Content-Type: application/json');

        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            return;
        }

        $traslado = Traslado::fetchFirstRaw(
            "SELECT t.*, so.nombre AS origen, sd.nombre AS destino 
             FROM traslados t
             JOIN sucursales so ON so.id = t.sucursal_origen_id
             JOIN sucursales sd ON sd.id = t.sucursal_destino_id
             WHERE t.id = :id",
            [':id' => $id]
        );

        if (!$traslado) {
            echo json_encode(['ok' => false, 'error' => 'Traslado no encontrado']);
            return;
        }

        // Obtener detalles con el nombre y unidad del producto
        $detalle = TrasladoDetalle::fetchRaw(
            "SELECT d.*, p.nombre AS producto_nombre, u.abreviatura AS unidad_abreviatura
             FROM traslado_detalle d
             JOIN productos p ON p.id = d.producto_id
             LEFT JOIN unidades_medida u ON u.id = p.unidad_id
             WHERE d.traslado_id = :id",
            [':id' => $id]
        );

        echo json_encode([
            'ok'       => true,
            'traslado' => $traslado,
            'detalle'  => $detalle
        ]);
        exit;
    }
}
