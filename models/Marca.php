<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Marca — tabla `marcas`
 */
class Marca extends ActiveRecord {

    protected static $tabla      = 'marcas';
    protected static $idTabla    = 'id';
    protected static $columnasDB = ['id','nombre'];

    public ?int    $id     = null;
    public string  $nombre = '';

    public function validar(): array {
        static::$alertas = [];
        if (empty($this->nombre)) self::setAlerta('danger', 'El nombre de la marca es obligatorio');
        return static::$alertas;
    }
}
