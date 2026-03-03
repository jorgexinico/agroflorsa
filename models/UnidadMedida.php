<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo UnidadMedida — tabla `unidades_medida`
 */
class UnidadMedida extends ActiveRecord {

    protected static $tabla      = 'unidades_medida';
    protected static $idTabla    = 'id';
    protected static $columnasDB = ['id','nombre','abreviatura','creado_en'];

    public ?int    $id           = null;
    public string  $nombre       = '';
    public string  $abreviatura  = '';
    public ?string $creado_en    = null;

    public function validar(): array {
        static::$alertas = [];
        if (empty($this->nombre))      self::setAlerta('danger', 'El nombre es obligatorio');
        if (empty($this->abreviatura)) self::setAlerta('danger', 'La abreviatura es obligatoria');
        return static::$alertas;
    }
}
