<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Inventario — tabla `inventario_existencias_producto`
 * Gestiona el stock actual por sucursal y producto.
 */
class Inventario extends ActiveRecord {

    protected static $tabla      = 'inventario_existencias_producto';
    protected static $idTabla    = 'id';
    protected static $columnasDB = ['id','sucursal_id','producto_id','cantidad'];

    public ?int   $id          = null;
    public ?int   $sucursal_id = null;
    public ?int   $producto_id = null;
    public float  $cantidad    = 0;

    // Joins
    public ?string $producto_nombre    = null;
    public ?string $unidad_abreviatura = null;
    public ?string $sucursal_nombre    = null;

    /**
     * Retorna el stock actual de un producto en una sucursal.
     */
    public static function getStock(int $sucursal_id, int $producto_id): float {
        $row = self::fetchFirstRaw(
            "SELECT cantidad FROM inventario_existencias_producto
             WHERE sucursal_id = :suc AND producto_id = :prod",
            [':suc' => $sucursal_id, ':prod' => $producto_id]
        );
        return (float)($row['cantidad'] ?? 0);
    }

    /**
     * Agrega (positivo) o descuenta (negativo) stock.
     * Inserta o actualiza el registro de existencias.
     * Devuelve false si el desembolso provocaría stock negativo.
     */
    public static function ajustarStock(int $sucursal_id, int $producto_id, float $delta): bool {
        $stockActual = self::getStock($sucursal_id, $producto_id);

        if ($stockActual + $delta < 0) {
            return false; // Stock insuficiente
        }

        $existe = self::fetchFirstRaw(
            "SELECT id FROM inventario_existencias_producto
             WHERE sucursal_id = :suc AND producto_id = :prod",
            [':suc' => $sucursal_id, ':prod' => $producto_id]
        );

        if ($existe) {
            self::ejecutar(
                "UPDATE inventario_existencias_producto
                 SET cantidad = cantidad + :delta
                 WHERE sucursal_id = :suc AND producto_id = :prod",
                [':delta' => $delta, ':suc' => $sucursal_id, ':prod' => $producto_id]
            );
        } else {
            self::ejecutar(
                "INSERT INTO inventario_existencias_producto (sucursal_id, producto_id, cantidad)
                 VALUES (:suc, :prod, :cant)",
                [':suc' => $sucursal_id, ':prod' => $producto_id, ':cant' => $delta]
            );
        }

        return true;
    }

    /**
     * Agrega (positivo) o descuenta (negativo) stock de un lote específico.
     */
    public static function ajustarStockLote(int $sucursal_id, int $lote_id, float $delta): bool {
        $existe = self::fetchFirstRaw(
            "SELECT id, cantidad FROM inventario_existencias_lote
             WHERE sucursal_id = :suc AND lote_id = :lote",
            [':suc' => $sucursal_id, ':lote' => $lote_id]
        );

        if ($existe) {
            if ($existe['cantidad'] + $delta < 0) return false;
            
            self::ejecutar(
                "UPDATE inventario_existencias_lote
                 SET cantidad = cantidad + :delta
                 WHERE id = :id",
                [':delta' => $delta, ':id' => $existe['id']]
            );
        } else {
            if ($delta < 0) return false;
            self::ejecutar(
                "INSERT INTO inventario_existencias_lote (sucursal_id, lote_id, cantidad)
                 VALUES (:suc, :lote, :cant)",
                [':suc' => $sucursal_id, ':lote' => $lote_id, ':cant' => $delta]
            );
        }
        return true;
    }

    /**
     * Retorna todo el stock de una sucursal con nombre del producto y unidad.
     */
    public static function getStockSucursal(int $sucursal_id): array {
        return self::fetchRaw(
            "SELECT iep.*, p.nombre AS producto_nombre, p.sku, p.maneja_vencimiento,
                    u.nombre AS unidad_nombre, u.abreviatura AS unidad_abreviatura,
                    p.precio_publico, p.precio_mayorista,
                    p.precio_compra as ultimo_costo
             FROM inventario_existencias_producto iep
             JOIN productos p ON p.id = iep.producto_id
             JOIN unidades_medida u ON u.id = p.unidad_id
             WHERE iep.sucursal_id = :suc AND p.activo = 1
             ORDER BY p.nombre",
            [':suc' => $sucursal_id]
        );
    }

    public static function getCostoUnitario(int $producto_id): float {
        $row = self::fetchFirstRaw(
            "SELECT precio_compra
             FROM productos
             WHERE id = :prod
             LIMIT 1",
            [':prod' => $producto_id]
        );
        return (float)($row['precio_compra'] ?? 0);
    }

    /**
     * Registra un movimiento de inventario.
     */
    public static function registrarMovimiento(array $datos): bool {
        return self::ejecutar(
            "INSERT INTO inventario_movimientos
             (sucursal_id, producto_id, lote_id, tipo, referencia_tipo, referencia_id, signo, cantidad, costo_unitario)
             VALUES (:suc, :prod, :lote, :tipo, :ref_tipo, :ref_id, :signo, :cantidad, :costo)",
            [
                ':suc'      => $datos['sucursal_id'],
                ':prod'     => $datos['producto_id'],
                ':lote'     => $datos['lote_id']     ?? null,
                ':tipo'     => $datos['tipo'],
                ':ref_tipo' => $datos['referencia_tipo'] ?? null,
                ':ref_id'   => $datos['referencia_id']  ?? null,
                ':signo'    => $datos['signo'],
                ':cantidad' => $datos['cantidad'],
                ':costo'    => $datos['costo_unitario']  ?? null,
            ]
        );
    }
}
