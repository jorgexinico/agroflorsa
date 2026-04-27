<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Produccion — tabla `producciones`
 */
class Produccion extends ActiveRecord {

    protected static $tabla      = 'producciones';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','sucursal_id','usuario_id','fecha','total','estado','observacion'
    ];

    public ?int    $id           = null;
    public ?int    $sucursal_id  = null;
    public ?int    $usuario_id   = null;
    public ?string $fecha        = null;
    public float   $total        = 0;
    public string  $estado       = 'terminada';
    public ?string $observacion  = null;

    // Joins
    public ?string $usuario_nombre  = null;
    public ?string $sucursal_nombre = null;

    public static function allConDetalle(int $sucursal_id = 0): array {
        $where  = $sucursal_id > 0 ? "WHERE p.sucursal_id = :suc" : "WHERE 1=1";
        $params = $sucursal_id > 0 ? [':suc' => $sucursal_id] : [];
        return self::fetchRaw(
            "SELECT p.*, u.nombre AS usuario_nombre, s.nombre AS sucursal_nombre
             FROM producciones p
             LEFT JOIN usuarios u ON u.id = p.usuario_id
             JOIN sucursales s ON s.id = p.sucursal_id
             $where
             ORDER BY p.fecha DESC",
            $params
        );
    }

    public function validar(): array {
        static::$alertas = [];
        if (empty($this->sucursal_id)) self::setAlerta('danger', 'Selecciona la sucursal destino');
        return static::$alertas;
    }
}
