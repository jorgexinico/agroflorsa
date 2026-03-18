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
        $hoy         = date('Y-m-d');

        // ── Turno activo del usuario ────────────────────────────────
        $turnoActivo = Turno::fetchFirstRaw(
            "SELECT t.*, s.nombre AS sucursal_nombre
             FROM turnos t JOIN sucursales s ON s.id = t.sucursal_id
             WHERE t.usuario_id = :uid AND t.estado = 'abierto' LIMIT 1",
            [':uid' => $_SESSION['usuario_id']]
        );

        // ── KPIs de ventas ──────────────────────────────────────────
        if ($sucursal_id > 0) {
            // Usuario con sucursal asignada: filtra por sucursal
            $ventasHoy = ActiveRecord::fetchFirstRaw(
                "SELECT COALESCE(SUM(total),0) AS monto, COUNT(id) AS cantidad
                 FROM ventas WHERE DATE(fecha) = :hoy AND estado = 'emitida' AND sucursal_id = :suc",
                [':hoy' => $hoy, ':suc' => $sucursal_id]
            );
        } else {
            // Admin sin turno: ve global de hoy
            $ventasHoy = ActiveRecord::fetchFirstRaw(
                "SELECT COALESCE(SUM(total),0) AS monto, COUNT(id) AS cantidad
                 FROM ventas WHERE DATE(fecha) = :hoy AND estado = 'emitida'",
                [':hoy' => $hoy]
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
        if ($sucursal_id > 0) {
            $ultimasVentas = ActiveRecord::fetchRaw(
                "SELECT v.id, v.total, v.fecha, v.tipo_pago, c.nombre AS cliente,
                        '' AS sucursal_nombre
                 FROM ventas v LEFT JOIN clientes c ON c.id = v.cliente_id
                 WHERE v.estado = 'emitida' AND v.sucursal_id = :suc
                 ORDER BY v.fecha DESC LIMIT 8",
                [':suc' => $sucursal_id]
            );
        } else {
            $ultimasVentas = ActiveRecord::fetchRaw(
                "SELECT v.id, v.total, v.fecha, v.tipo_pago, c.nombre AS cliente,
                        s.nombre AS sucursal_nombre
                 FROM ventas v
                 LEFT JOIN clientes c ON c.id = v.cliente_id
                 LEFT JOIN sucursales s ON s.id = v.sucursal_id
                 WHERE v.estado = 'emitida' AND DATE(v.fecha) = :hoy
                 ORDER BY v.fecha DESC LIMIT 8",
                [':hoy' => $hoy]
            );
        }

        // ── Cuentas por pagar pendientes (saldo global) ─────────────
        $cxpPendiente = (float)(ActiveRecord::fetchFirstRaw(
            "SELECT COALESCE(SUM(saldo),0) AS saldo FROM cuentas_por_pagar WHERE estado IN ('pendiente','parcial')"
        )['saldo'] ?? 0);

        $router->render('pages/index', [
            'titulo'         => 'Dashboard',
            'ventasHoy'      => $ventasHoy,
            'turnoActivo'    => $turnoActivo,
            'totalProductos' => $totalProductos,
            'cxcPendiente'   => $cxcPendiente,
            'cxpPendiente'   => $cxpPendiente,
            'ultimasVentas'  => $ultimasVentas,
            'esAdmin'        => ($rol === 'admin' && $sucursal_id === 0),
        ]);
    }
}