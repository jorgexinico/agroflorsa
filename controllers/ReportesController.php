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
        $sucursal_id  = (int)($_GET['sucursal_id'] ?? 0);

        $params = [':f1' => $fecha_inicio, ':f2' => $fecha_fin];
        $whereSucursal = '';
        if ($sucursal_id > 0) {
            $whereSucursal = " AND v.sucursal_id = :sid";
            $params[':sid'] = $sucursal_id;
        }

        // Consulta de utilidades por venta (Solo Contado, si quieres que refleje lo mismo, o la dejamos con todo? Le pondremos solo contado para ser coherente)
        $utilidades = ActiveRecord::fetchRaw(
            "SELECT v.id AS venta_id, v.fecha, c.nombre AS cliente, s.nombre AS sucursal_nombre,
                    SUM(vd.subtotal) AS total_venta,
                    SUM(vd.costo_unitario * vd.cantidad) AS total_costo,
                    SUM(vd.subtotal - (vd.costo_unitario * vd.cantidad)) AS utilidad
             FROM ventas v
             JOIN venta_detalle vd ON vd.venta_id = v.id
             LEFT JOIN clientes c ON c.id = v.cliente_id
             LEFT JOIN sucursales s ON s.id = v.sucursal_id
             WHERE DATE(v.fecha) BETWEEN :f1 AND :f2 AND v.estado != 'anulada' AND v.tipo_pago = 'contado' {$whereSucursal}
             GROUP BY v.id
             ORDER BY total_venta DESC, v.fecha DESC",
            $params
        );

        $sucursales = \Models\Sucursal::getActivas();

        $router->render('reportes/utilidades', [
            'titulo'       => 'Reporte de Utilidades',
            'utilidades'   => $utilidades,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin'    => $fecha_fin,
            'sucursal_id'  => $sucursal_id,
            'sucursales'   => $sucursales
        ]);
    }

    public static function financiero(Router $router): void {
        isAuth();
        isRole(['admin']);

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin    = $_GET['fecha_fin']    ?? date('Y-m-d');
        $sucursal_id  = (int)($_GET['sucursal_id'] ?? 0);

        $params = [':f1' => $fecha_inicio, ':f2' => $fecha_fin];
        $whereSucursalVentas = '';
        $whereSucursalGastos = '';
        
        if ($sucursal_id > 0) {
            $whereSucursalVentas = " AND v.sucursal_id = :sid";
            $whereSucursalGastos = " AND g.sucursal_id = :sid";
            $params[':sid'] = $sucursal_id;
        }

        // Ventas: calcular total ventas y costo de ventas (Solo Contado)
        $ventas = ActiveRecord::fetchFirstRaw(
            "SELECT 
                IFNULL(SUM(vd.subtotal), 0) AS total_venta,
                IFNULL(SUM(vd.costo_unitario * vd.cantidad), 0) AS total_costo
             FROM ventas v
             JOIN venta_detalle vd ON vd.venta_id = v.id
             WHERE DATE(v.fecha) BETWEEN :f1 AND :f2 AND v.estado != 'anulada' AND v.tipo_pago = 'contado' {$whereSucursalVentas}",
            $params
        );

        // Abonos (Pagos a Cuentas por Cobrar)
        $abonos = ActiveRecord::fetchFirstRaw(
            "SELECT IFNULL(SUM(p.monto), 0) AS total_abonos
             FROM pagos_cxc p
             JOIN cuentas_por_cobrar cxc ON cxc.id = p.cxc_id
             JOIN ventas v ON v.id = cxc.venta_id
             WHERE DATE(p.fecha) BETWEEN :f1 AND :f2 {$whereSucursalVentas}",
            $params
        );

        $total_ingresos_contado = (float)$ventas['total_venta'];
        $total_abonos   = (float)$abonos['total_abonos'];
        $total_ingresos = $total_ingresos_contado + $total_abonos;
        $total_costos   = (float)$ventas['total_costo']; // Costo solo de lo vendido al contado
        $utilidad_bruta = $total_ingresos - $total_costos;

        // Gastos Operativos: agrupados por categoría
        $gastos = ActiveRecord::fetchRaw(
            "SELECT cg.nombre as categoria, IFNULL(SUM(g.monto), 0) as total_gasto
             FROM gastos g
             JOIN categorias_gastos cg ON cg.id = g.categoria_id
             WHERE DATE(g.fecha) BETWEEN :f1 AND :f2 {$whereSucursalGastos} AND g.estado != 'anulado'
             GROUP BY cg.id, cg.nombre
             ORDER BY total_gasto DESC",
            $params
        );

        $total_gastos = 0;
        $categorias_gastos = [];
        $montos_gastos = [];

        foreach ($gastos as $g) {
            $total_gastos += (float)$g['total_gasto'];
            $categorias_gastos[] = $g['categoria'];
            $montos_gastos[] = (float)$g['total_gasto'];
        }

        $utilidad_neta = $utilidad_bruta - $total_gastos;

        $sucursales = \Models\Sucursal::getActivas();

        $router->render('reportes/financiero', [
            'titulo'            => 'Estado de Resultados',
            'fecha_inicio'      => $fecha_inicio,
            'fecha_fin'         => $fecha_fin,
            'sucursal_id'       => $sucursal_id,
            'sucursales'        => $sucursales,
            'total_ingresos_contado'=> $total_ingresos_contado,
            'total_abonos'      => $total_abonos,
            'total_ingresos'    => $total_ingresos,
            'total_costos'      => $total_costos,
            'utilidad_bruta'    => $utilidad_bruta,
            'total_gastos'      => $total_gastos,
            'utilidad_neta'     => $utilidad_neta,
            'categorias_gastos' => json_encode($categorias_gastos),
            'montos_gastos'     => json_encode($montos_gastos)
        ]);
    }

    public static function credito(Router $router): void {
        isAuth();
        isRole(['admin']);

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin    = $_GET['fecha_fin']    ?? date('Y-m-d');
        $sucursal_id  = (int)($_GET['sucursal_id'] ?? 0);

        $params = [':f1' => $fecha_inicio, ':f2' => $fecha_fin];
        $whereSucursal = '';
        if ($sucursal_id > 0) {
            $whereSucursal = " AND v.sucursal_id = :sid";
            $params[':sid'] = $sucursal_id;
        }

        // Consulta de ventas al crédito
        $ventas = ActiveRecord::fetchRaw(
            "SELECT v.id AS venta_id, v.fecha, c.nombre AS cliente, s.nombre AS sucursal_nombre,
                    v.total AS total_venta,
                    cxc.pagado, cxc.saldo, cxc.estado
             FROM ventas v
             LEFT JOIN clientes c ON c.id = v.cliente_id
             LEFT JOIN sucursales s ON s.id = v.sucursal_id
             JOIN cuentas_por_cobrar cxc ON cxc.venta_id = v.id
             WHERE DATE(v.fecha) BETWEEN :f1 AND :f2 AND v.estado != 'anulada' AND v.tipo_pago = 'credito' {$whereSucursal}
             ORDER BY v.fecha DESC",
            $params
        );

        $sucursales = \Models\Sucursal::getActivas();

        $router->render('reportes/credito', [
            'titulo'       => 'Reporte de Ventas al Crédito',
            'ventas'       => $ventas,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin'    => $fecha_fin,
            'sucursal_id'  => $sucursal_id,
            'sucursales'   => $sucursales
        ]);
    }

    public static function productos(Router $router): void {
        isAuth();
        isRole(['admin']);

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin    = $_GET['fecha_fin']    ?? date('Y-m-d');
        $sucursal_id  = (int)($_GET['sucursal_id'] ?? 0);

        $params = [':f1' => $fecha_inicio, ':f2' => $fecha_fin];
        $whereSucursal = '';
        
        if ($sucursal_id > 0) {
            $whereSucursal = " AND v.sucursal_id = :sid";
            $params[':sid'] = $sucursal_id;
        }

        // 1. Top Productos por Cantidad Vendida
        $top_vendidos = ActiveRecord::fetchRaw(
            "SELECT p.id, p.nombre, p.sku, u.abreviatura as unidad,
                    SUM(vd.cantidad) AS cantidad_total,
                    SUM(vd.subtotal) AS ingresos_totales
             FROM venta_detalle vd
             JOIN ventas v ON v.id = vd.venta_id
             JOIN productos p ON p.id = vd.producto_id
             JOIN unidades_medida u ON u.id = p.unidad_id
             WHERE DATE(v.fecha) BETWEEN :f1 AND :f2 AND v.estado != 'anulada' {$whereSucursal}
             GROUP BY p.id
             ORDER BY cantidad_total DESC",
            $params
        );

        // 2. Top Productos por Ganancia Generada
        $top_ganancias = ActiveRecord::fetchRaw(
            "SELECT p.id, p.nombre, p.sku,
                    SUM(vd.subtotal - (vd.costo_unitario * vd.cantidad)) AS utilidad_total,
                    SUM(vd.subtotal) as ingresos_totales
             FROM venta_detalle vd
             JOIN ventas v ON v.id = vd.venta_id
             JOIN productos p ON p.id = vd.producto_id
             WHERE DATE(v.fecha) BETWEEN :f1 AND :f2 AND v.estado != 'anulada' {$whereSucursal}
             GROUP BY p.id
             ORDER BY utilidad_total DESC",
            $params
        );

        // 3. Productos sin rotación (En inventario pero 0 ventas)
        $whereInventario = $sucursal_id > 0 ? " AND i.sucursal_id = {$sucursal_id}" : "";
        $productos_sin_rotacion = ActiveRecord::fetchRaw(
            "SELECT p.id, p.nombre, p.sku, SUM(i.cantidad) AS stock_total, u.abreviatura as unidad
             FROM productos p
             JOIN inventario_existencias_producto i ON i.producto_id = p.id
             JOIN unidades_medida u ON u.id = p.unidad_id
             WHERE p.activo = 1 {$whereInventario}
             GROUP BY p.id
             HAVING stock_total > 0
             AND p.id NOT IN (
                 SELECT DISTINCT vd.producto_id
                 FROM venta_detalle vd
                 JOIN ventas v ON v.id = vd.venta_id
                 WHERE DATE(v.fecha) BETWEEN :f1 AND :f2 AND v.estado != 'anulada' {$whereSucursal}
             )
             ORDER BY stock_total DESC
             LIMIT 20",
            $params
        );

        // 4. Ventas por Sucursal
        $paramsSuc = [':f1' => $fecha_inicio, ':f2' => $fecha_fin];
        $ventas_sucursal = ActiveRecord::fetchRaw(
            "SELECT s.nombre, SUM(v.total) as total_ventas
             FROM ventas v
             JOIN sucursales s ON s.id = v.sucursal_id
             WHERE DATE(v.fecha) BETWEEN :f1 AND :f2 AND v.estado != 'anulada'
             GROUP BY s.id
             ORDER BY total_ventas DESC",
            $paramsSuc
        );

        $sucursales = \Models\Sucursal::getActivas();

        $router->render('reportes/productos', [
            'titulo'                 => 'Rendimiento de Productos',
            'fecha_inicio'           => $fecha_inicio,
            'fecha_fin'              => $fecha_fin,
            'sucursal_id'            => $sucursal_id,
            'sucursales'             => $sucursales,
            'top_vendidos'           => $top_vendidos,
            'top_ganancias'          => $top_ganancias,
            'productos_sin_rotacion' => $productos_sin_rotacion,
            'ventas_sucursal'        => $ventas_sucursal
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

    public static function capitalEstancado(Router $router): void {
        isAuth();
        isRole(['admin']);

        $sucursal_id  = (int)($_GET['sucursal_id'] ?? 0);
        $dias_estancado = (int)($_GET['dias'] ?? 90);

        $params = [];
        $whereInventario = "";
        
        if ($sucursal_id > 0) {
            $whereInventario = " AND iep.sucursal_id = :sid";
            $params[':sid'] = $sucursal_id;
        }

        $params[':dias'] = $dias_estancado;

        // Se busca: productos con stock en la sucursal indicada y cuya última fecha de venta 
        // (específica para esa sucursal si se seleccionó) sea más antigua que $dias_estancado o nunca se haya vendido.
        $sql = "
            SELECT p.id, p.nombre, p.sku, 
                   SUM(iep.cantidad) AS stock_total, 
                   p.precio_compra,
                   (SUM(iep.cantidad) * p.precio_compra) AS valor_estancado,
                   s.nombre AS sucursal_nombre,
                   (
                       SELECT MAX(v.fecha)
                       FROM venta_detalle vd
                       JOIN ventas v ON v.id = vd.venta_id
                       WHERE vd.producto_id = p.id
                       " . ($sucursal_id > 0 ? " AND v.sucursal_id = {$sucursal_id} " : "") . "
                   ) AS ultima_venta
            FROM productos p
            JOIN inventario_existencias_producto iep ON iep.producto_id = p.id
            JOIN sucursales s ON s.id = iep.sucursal_id
            WHERE p.activo = 1 
              AND iep.cantidad > 0 
              {$whereInventario}
            GROUP BY p.id, s.id
            HAVING (ultima_venta IS NULL OR ultima_venta <= DATE_SUB(CURDATE(), INTERVAL :dias DAY))
            ORDER BY valor_estancado DESC
        ";

        $productos_estancados = ActiveRecord::fetchRaw($sql, $params);

        $sucursales = \Models\Sucursal::getActivas();

        $router->render('reportes/capital_estancado', [
            'titulo'               => 'Capital Estancado (Lento Movimiento)',
            'sucursal_id'          => $sucursal_id,
            'dias_estancado'       => $dias_estancado,
            'sucursales'           => $sucursales,
            'productos_estancados' => $productos_estancados
        ]);
    }
}
