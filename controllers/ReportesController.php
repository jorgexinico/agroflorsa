<?php
namespace Controllers;

use MVC\Router;
use Models\VentaDetalle;
use Models\Lote;
use Model\ActiveRecord;

class ReportesController {

    public static function utilidades(Router $router): void {
        isAuth();
        isRole(['admin']);

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin    = $_GET['fecha_fin']    ?? date('Y-m-d');

        // Consulta de utilidades por venta
        $utilidades = ActiveRecord::fetchRaw(
            "SELECT v.id AS venta_id, v.fecha, c.nombre AS cliente, 
                    SUM(vd.subtotal) AS total_venta,
                    SUM(vd.costo_unitario * vd.cantidad) AS total_costo,
                    SUM(vd.subtotal - (vd.costo_unitario * vd.cantidad)) AS utilidad
             FROM ventas v
             JOIN venta_detalle vd ON vd.venta_id = v.id
             LEFT JOIN clientes c ON c.id = v.cliente_id
             WHERE DATE(v.fecha) BETWEEN :f1 AND :f2 AND v.estado != 'anulada'
             GROUP BY v.id
             ORDER BY v.fecha DESC",
            [':f1' => $fecha_inicio, ':f2' => $fecha_fin]
        );

        $router->render('reportes/utilidades', [
            'titulo'       => 'Reporte de Utilidades',
            'utilidades'   => $utilidades,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin'    => $fecha_fin
        ]);
    }

    public static function vencimientos(Router $router): void {
        isAuth();
        
        $filtro = $_GET['estado'] ?? 'todos';

        // Lotes que vencen en los próximos 90 días
        $where = "1=1";
        if ($filtro === 'proximos') {
            $where .= " AND l.fecha_vencimiento IS NOT NULL AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND l.fecha_vencimiento >= CURDATE()";
        } elseif ($filtro === 'vencidos') {
            $where .= " AND l.fecha_vencimiento IS NOT NULL AND l.fecha_vencimiento < CURDATE()";
        } elseif ($filtro === 'vigentes') {
            $where .= " AND (l.fecha_vencimiento IS NULL OR l.fecha_vencimiento > DATE_ADD(CURDATE(), INTERVAL 90 DAY))";
        }

        $vencimientos = ActiveRecord::fetchRaw(
            "SELECT l.*, p.nombre AS producto, p.sku, s.nombre AS sucursal, IFNULL(iel.cantidad, 0) AS cantidad
             FROM lotes l
             JOIN productos p ON p.id = l.producto_id
             LEFT JOIN inventario_existencias_lote iel ON iel.lote_id = l.id
             LEFT JOIN sucursales s ON s.id = iel.sucursal_id
             WHERE $where
             ORDER BY 
                CASE 
                    WHEN l.fecha_vencimiento IS NULL THEN 1 
                    ELSE 0 
                END, 
                l.fecha_vencimiento ASC"
        );

        $router->render('reportes/vencimientos', [
            'titulo'       => 'Lotes y Vencimientos',
            'vencimientos' => $vencimientos,
            'estado'       => $filtro
        ]);
    }

    /**
     * AJAX: Retorna el detalle de una venta para el acordeón del reporte de utilidades.
     */
    public static function detalleVentaAjax(): void {
        header('Content-Type: application/json');
        isAuth();
        isRole(['admin']);
        
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['ok' => false, 'error' => 'ID de venta no válido']);
            exit;
        }

        try {
            $venta = ActiveRecord::fetchRaw(
                "SELECT v.*, c.nombre AS cliente_nombre, u.nombre AS usuario_nombre, s.nombre AS sucursal_nombre
                 FROM ventas v
                 LEFT JOIN clientes c ON c.id = v.cliente_id
                 LEFT JOIN sucursales s ON s.id = v.sucursal_id
                 LEFT JOIN turnos t ON t.id = v.turno_id
                 LEFT JOIN usuarios u ON u.id = t.usuario_id
                 WHERE v.id = :id",
                [':id' => $id]
            );

            if (empty($venta)) {
                echo json_encode(['ok' => false, 'error' => 'Venta no encontrada']);
                exit;
            }

            $detalle = ActiveRecord::fetchRaw(
                "SELECT vd.*, p.nombre AS producto_nombre, um.abreviatura AS unidad
                 FROM venta_detalle vd
                 JOIN productos p ON p.id = vd.producto_id
                 JOIN unidades_medida um ON um.id = p.unidad_id
                 WHERE vd.venta_id = :id",
                [':id' => $id]
            );

            echo json_encode([
                'ok'      => true,
                'venta'   => $venta[0],
                'detalle' => $detalle
            ]);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
            exit;
        }
    }
}
