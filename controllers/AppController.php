<?php
namespace Controllers;

use MVC\Router;
use Model\ActiveRecord;
use Models\Turno;

/**
 * AppController — Dashboard principal
 */
class AppController {

    public static function index(Router $router): void {
        isAuth();

        $sucursal_id = (int)($_SESSION['sucursal_id'] ?? 0);
        $rol         = $_SESSION['usuario_rol'] ?? '';
        $usuario_id  = (int)($_SESSION['usuario_id'] ?? 0);
        $hoy         = date('Y-m-d');

        // ── Turno activo del usuario ────────────────────────────────
        $turnoActivo = Turno::fetchFirstRaw(
            "SELECT t.*, s.nombre AS sucursal_nombre
             FROM turnos t JOIN sucursales s ON s.id = t.sucursal_id
             WHERE t.usuario_id = :uid AND t.estado = 'abierto' LIMIT 1",
            [':uid' => $usuario_id]
        );

        // ── KPIs de ventas ──────────────────────────────────────────
        if ($rol !== 'admin') {
            // Vendedor: solo sus ventas
            $ventasHoy = ActiveRecord::fetchFirstRaw(
                "SELECT COALESCE(SUM(v.total),0) AS monto, COUNT(v.id) AS cantidad
                 FROM ventas v JOIN turnos t ON t.id = v.turno_id
                 WHERE DATE(v.fecha) = :hoy AND v.estado = 'emitida' AND t.usuario_id = :uid",
                [':hoy' => $hoy, ':uid' => $usuario_id]
            );
        } else {
            // Admin: global de hoy (opcionalmente filtrado por sucursal si tiene una asignada)
            $where = "WHERE DATE(fecha) = :hoy AND estado = 'emitida'";
            $p     = [':hoy' => $hoy];
            if ($sucursal_id > 0) {
                $where .= " AND sucursal_id = :suc";
                $p[':suc'] = $sucursal_id;
            }
            $ventasHoy = ActiveRecord::fetchFirstRaw(
                "SELECT COALESCE(SUM(total),0) AS monto, COUNT(id) AS cantidad
                 FROM ventas $where",
                $p
            );
        }
        $ventasHoy = $ventasHoy ?? ['monto' => 0, 'cantidad' => 0];

        // ── Productos activos ───────────────────────────────────────
        $totalProductos = (int)(ActiveRecord::fetchFirstRaw(
            "SELECT COUNT(id) AS n FROM productos WHERE activo = 1"
        )['n'] ?? 0);

        // ── Cuentas por cobrar pendientes (saldo global) ────────────
        $cxcPendiente = (float)(ActiveRecord::fetchFirstRaw(
            "SELECT COALESCE(SUM(saldo),0) AS saldo FROM cuentas_por_cobrar WHERE estado IN ('pendiente','parcial')"
        )['saldo'] ?? 0);

        // ── Últimas ventas ──────────────────────────────────────────
        if ($rol !== 'admin') {
            // Vendedor: solo sus ventas
            $ultimasVentas = ActiveRecord::fetchRaw(
                "SELECT v.id, v.total, v.fecha, v.tipo_pago, c.nombre AS cliente, s.nombre AS sucursal_nombre
                 FROM ventas v 
                 JOIN turnos t ON t.id = v.turno_id
                 JOIN sucursales s ON s.id = v.sucursal_id
                 LEFT JOIN clientes c ON c.id = v.cliente_id
                 WHERE v.estado = 'emitida' AND t.usuario_id = :uid
                 ORDER BY v.fecha DESC LIMIT 8",
                [':uid' => $usuario_id]
            );
        } else {
            // Admin: global de hoy
            $where = "WHERE v.estado = 'emitida'";
            $p     = [];
            if ($sucursal_id > 0) {
                $where .= " AND v.sucursal_id = :suc";
                $p[':suc'] = $sucursal_id;
            } else {
                $where .= " AND DATE(v.fecha) = :hoy";
                $p[':hoy'] = $hoy;
            }

            $ultimasVentas = ActiveRecord::fetchRaw(
                "SELECT v.id, v.total, v.fecha, v.tipo_pago, c.nombre AS cliente, s.nombre AS sucursal_nombre
                 FROM ventas v
                 LEFT JOIN clientes c ON c.id = v.cliente_id
                 LEFT JOIN sucursales s ON s.id = v.sucursal_id
                 $where
                 ORDER BY v.fecha DESC LIMIT 8",
                $p
            );
        }

        // ── Cuentas por pagar pendientes (saldo global) ─────────────
        $cxpPendiente = (float)(ActiveRecord::fetchFirstRaw(
            "SELECT COALESCE(SUM(saldo),0) AS saldo FROM cuentas_por_pagar WHERE estado IN ('pendiente','parcial')"
        )['saldo'] ?? 0);

        // ── Productos por Agotarse (Low Stock) pero con actividad reciente ─────────────
        $limite_bajo_stock = 10;
        $dias_actividad = 60; // Mostrar alerta SOLO si el producto se ha vendido en los últimos 60 días
        if ($rol !== 'admin' || $sucursal_id > 0) {
            $suc_id = $sucursal_id;
            
            // Si es vendedor y no tiene turno activo, buscamos la sucursal de su último turno
            if ($suc_id === 0 && $rol !== 'admin') {
                $ultimoTurno = Turno::fetchFirstRaw(
                    "SELECT sucursal_id FROM turnos WHERE usuario_id = :uid ORDER BY id DESC LIMIT 1",
                    [':uid' => $usuario_id]
                );
                $suc_id = (int)($ultimoTurno['sucursal_id'] ?? 0);
            }

            if ($suc_id > 0) {
                $lowStock = ActiveRecord::fetchRaw(
                    "SELECT iep.cantidad, p.nombre, p.sku, s.nombre AS sucursal_nombre
                     FROM inventario_existencias_producto iep
                     JOIN productos p ON p.id = iep.producto_id
                     JOIN sucursales s ON s.id = iep.sucursal_id
                     WHERE iep.sucursal_id = :suc AND p.activo = 1 AND iep.cantidad <= :limite
                       AND p.id IN (
                           SELECT vd.producto_id FROM venta_detalle vd 
                           JOIN ventas v ON vd.venta_id = v.id 
                           WHERE v.fecha >= DATE_SUB(CURDATE(), INTERVAL :dias_actividad DAY) AND v.sucursal_id = :suc
                       )
                     ORDER BY iep.cantidad ASC LIMIT 15",
                    [':suc' => $suc_id, ':limite' => $limite_bajo_stock, ':dias_actividad' => $dias_actividad]
                );
            } else {
                $lowStock = [];
            }
        } else {
            $lowStock = ActiveRecord::fetchRaw(
                "SELECT iep.cantidad, p.nombre, p.sku, s.nombre AS sucursal_nombre
                 FROM inventario_existencias_producto iep
                 JOIN productos p ON p.id = iep.producto_id
                 JOIN sucursales s ON s.id = iep.sucursal_id
                 WHERE p.activo = 1 AND iep.cantidad <= :limite
                   AND p.id IN (
                       SELECT vd.producto_id FROM venta_detalle vd 
                       JOIN ventas v ON vd.venta_id = v.id 
                       WHERE v.fecha >= DATE_SUB(CURDATE(), INTERVAL :dias_actividad DAY)
                   )
                 ORDER BY iep.cantidad ASC LIMIT 20",
                [':limite' => $limite_bajo_stock, ':dias_actividad' => $dias_actividad]
            );
        }

        $router->render('pages/index', [
            'titulo'         => 'Dashboard',
            'ventasHoy'      => $ventasHoy,
            'turnoActivo'    => $turnoActivo,
            'totalProductos' => $totalProductos,
            'cxcPendiente'   => $cxcPendiente,
            'cxpPendiente'   => $cxpPendiente,
            'ultimasVentas'  => $ultimasVentas,
            'lowStock'       => $lowStock,
            'esAdmin'        => ($rol === 'admin' && $sucursal_id === 0),
        ]);
    }
}