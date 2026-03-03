<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Turno — tabla `turnos`
 */
class Turno extends ActiveRecord {

    protected static $tabla      = 'turnos';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','sucursal_id','usuario_id','abierto_en','cerrado_en',
        'monto_inicial','monto_esperado','monto_entregado','diferencia','nota','estado'
    ];

    public ?int    $id               = null;
    public ?int    $sucursal_id      = null;
    public ?int    $usuario_id       = null;
    public ?string $abierto_en       = null;
    public ?string $cerrado_en       = null;
    public float   $monto_inicial    = 0;
    public ?float  $monto_esperado   = null;
    public ?float  $monto_entregado  = null;
    public ?float  $diferencia       = null;
    public ?string $nota             = null;
    public string  $estado           = 'abierto';

    // Joins (no van a BD)
    public ?string $sucursal_nombre  = null;
    public ?string $usuario_nombre   = null;

    /**
     * Retorna el turno activo para un usuario+sucursal, o null si no existe.
     */
    public static function getTurnoActivo(int $sucursal_id, int $usuario_id): ?self {
        $sql = "SELECT t.*, s.nombre AS sucursal_nombre, u.nombre AS usuario_nombre
                FROM turnos t
                JOIN sucursales s ON s.id = t.sucursal_id
                JOIN usuarios u ON u.id = t.usuario_id
                WHERE t.sucursal_id = :suc AND t.usuario_id = :usr AND t.estado = 'abierto'
                LIMIT 1";
        $resultado = self::queryPrep($sql, [':suc' => $sucursal_id, ':usr' => $usuario_id]);
        return array_shift($resultado) ?: null;
    }

    /**
     * Verifica si ya existe un turno abierto en la sucursal (cualquier usuario).
     */
    public static function existeTurnoAbierto(int $sucursal_id): bool {
        $resultado = self::fetchRaw(
            "SELECT id FROM turnos WHERE sucursal_id = :suc AND estado = 'abierto' LIMIT 1",
            [':suc' => $sucursal_id]
        );
        return !empty($resultado);
    }

    /**
     * Calcula totales del turno: ventas confirmadas, pagos efectivo.
     */
    public static function calcularTotales(int $turno_id): array {
        $totales = self::fetchFirstRaw(
            "SELECT 
                COALESCE(SUM(v.total), 0)  AS total_ventas,
                COUNT(v.id)                 AS num_ventas
             FROM ventas v
             WHERE v.turno_id = :tid AND v.estado = 'emitida'",
            [':tid' => $turno_id]
        );

        $efectivo = self::fetchFirstRaw(
            "SELECT COALESCE(SUM(pv.monto), 0) AS total_efectivo
             FROM pagos_venta pv
             JOIN ventas v ON v.id = pv.venta_id
             WHERE v.turno_id = :tid AND v.estado = 'emitida' AND pv.metodo = 'efectivo'",
            [':tid' => $turno_id]
        );

        return [
            'total_ventas'    => (float)($totales['total_ventas'] ?? 0),
            'num_ventas'      => (int)($totales['num_ventas']     ?? 0),
            'total_efectivo'  => (float)($efectivo['total_efectivo'] ?? 0),
        ];
    }

    public function validarApertura(): array {
        static::$alertas = [];
        if (empty($this->sucursal_id)) self::setAlerta('danger', 'Selecciona la sucursal');
        if ($this->monto_inicial < 0)  self::setAlerta('danger', 'El monto inicial no puede ser negativo');
        return static::$alertas;
    }
}
