<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Sucursal — tabla `sucursales`
 */
class Sucursal extends ActiveRecord {

    protected static $tabla      = 'sucursales';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','nombre','tipo','direccion','telefono','activa','creado_en'
    ];

    public ?int    $id         = null;
    public string  $nombre     = '';
    public string  $tipo       = 'agroservicio';
    public ?string $direccion  = null;
    public ?string $telefono   = null;
    public int     $activa     = 1;
    public ?string $creado_en  = null;

    public static function getActivas(): array {
        return self::where('activa', 1);
    }

    public function validar(): array {
        static::$alertas = [];
        if (empty($this->nombre)) {
            self::setAlerta('danger', 'El nombre de la sucursal es obligatorio');
        }
        return static::$alertas;
    }
}
