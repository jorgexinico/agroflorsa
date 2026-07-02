<?php
require_once __DIR__ . '/includes/app.php';

use Model\ActiveRecord;
use Models\TomaInventario;
use Models\TomaInventarioDetalle;
use Models\AjusteInventario;
use Models\AjusteDetalle;
use Models\Inventario;

echo "Iniciando corrección de Toma de Inventario...\n";

try {
    ActiveRecord::getDB()->beginTransaction();

    // 1. Obtener la última toma de inventario completada
    $toma = ActiveRecord::fetchFirstRaw("SELECT * FROM tomas_inventario WHERE estado = 'completada' ORDER BY id DESC LIMIT 1");
    
    if (!$toma) {
        echo "No se encontró ninguna toma completada.\n";
        exit;
    }

    $toma_id = (int)$toma['id'];
    $sucursal_id = (int)$toma['sucursal_id'];
    $usuario_id = (int)$toma['usuario_id'];

    echo "Toma a corregir: ID $toma_id (Sucursal: $sucursal_id)\n";

    // Buscar productos pendientes
    $pendientes = ActiveRecord::fetchRaw("SELECT id, producto_id FROM tomas_inventario_detalle WHERE toma_id = :tid AND estado = 'pendiente'", [':tid' => $toma_id]);
    
    if (empty($pendientes)) {
        echo "No hay productos en estado 'pendiente' en esta toma. Ya se procesó todo.\n";
        exit;
    }
    
    echo "Encontrados " . count($pendientes) . " productos pendientes.\n";

    // Crear un ajuste maestro
    $ajuste = new AjusteInventario();
    $ajuste->sucursal_id = $sucursal_id;
    $ajuste->usuario_id = $usuario_id;
    $ajuste->fecha = date('Y-m-d H:i:s');
    $ajuste->motivo = 'Ajuste CORRECTIVO por productos no contados en Toma de Inventario #' . $toma_id;
    
    $resAjuste = $ajuste->crear();
    $ajuste_id = (int)$resAjuste['id'];
    
    $ajustados = 0;

    foreach ($pendientes as $p) {
        $producto_id = (int)$p['producto_id'];
        $detalle_id = (int)$p['id'];

        $existencia = ActiveRecord::fetchFirstRaw(
            "SELECT cantidad FROM inventario_existencias_producto WHERE producto_id = :pid AND sucursal_id = :sid",
            [':pid' => $producto_id, ':sid' => $sucursal_id]
        );
        
        $stockRealTime = $existencia ? (float)$existencia['cantidad'] : 0;
        
        if ($stockRealTime != 0) {
            $diferencia = 0 - $stockRealTime;
            
            // Actualizar detalle toma
            ActiveRecord::fetchRaw(
                "UPDATE tomas_inventario_detalle SET stock_sistema = :stock, conteo_fisico = 0, diferencia = :dif, estado = 'no_contado' WHERE id = :id",
                [':stock' => $stockRealTime, ':dif' => $diferencia, ':id' => $detalle_id]
            );

            // Crear detalle de ajuste
            $detAjuste = new AjusteDetalle();
            $detAjuste->ajuste_id = $ajuste_id;
            $detAjuste->producto_id = $producto_id;
            $detAjuste->cantidad = $diferencia;
            $detAjuste->crear();

            // Ajustar Stock
            $ok = Inventario::ajustarStock($sucursal_id, $producto_id, $diferencia);
            
            if ($ok) {
                Inventario::registrarMovimiento([
                    'sucursal_id'     => $sucursal_id,
                    'producto_id'     => $producto_id,
                    'lote_id'         => null,
                    'tipo'            => 'ajuste',
                    'signo'           => $diferencia > 0 ? 1 : -1,
                    'cantidad'        => abs($diferencia),
                    'referencia_tipo' => 'ajuste',
                    'referencia_id'   => $ajuste_id,
                    'costo_unitario'  => null
                ]);
            }
            $ajustados++;
        } else {
            // Si no habia stock, simplemente lo marcamos no contado con dif 0
            ActiveRecord::fetchRaw(
                "UPDATE tomas_inventario_detalle SET stock_sistema = 0, conteo_fisico = 0, diferencia = 0, estado = 'no_contado' WHERE id = :id",
                [':id' => $detalle_id]
            );
        }
    }

    ActiveRecord::getDB()->commit();
    echo "¡Completado! Se ajustaron a 0 un total de $ajustados productos.\n";

} catch (\Exception $e) {
    ActiveRecord::getDB()->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
