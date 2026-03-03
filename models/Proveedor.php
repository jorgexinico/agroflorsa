<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Proveedor — tabla `proveedores`
 */
class Proveedor extends ActiveRecord {

    protected static $tabla      = 'proveedores';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','nombre','nit','telefono','direccion','activo','creado_en'
    ];

    public ?int    $id        = null;
    public string  $nombre    = '';
    public ?string $nit       = null;
    public ?string $telefono  = null;
    public ?string $direccion = null;
    public int     $activo    = 1;
    public ?string $creado_en = null;

    public static function getActivos(): array {
        return self::where('activo', 1, '=');
    }

    public function validar(): array {
        static::$alertas = [];
        if (empty($this->nombre)) self::setAlerta('danger', 'El nombre del proveedor es obligatorio');
        return static::$alertas;
    }
}
