<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Categoria — tabla `categorias`
 */
class Categoria extends ActiveRecord {

    protected static $tabla      = 'categorias';
    protected static $idTabla    = 'id';
    protected static $columnasDB = ['id','nombre','descripcion','activo','creado_en'];

    public ?int    $id          = null;
    public string  $nombre      = '';
    public ?string $descripcion = null;
    public int     $activo      = 1;
    public ?string $creado_en   = null;

    public function validar(): array {
        static::$alertas = [];
        if (empty($this->nombre)) self::setAlerta('danger', 'El nombre de la categoría es obligatorio');
        return static::$alertas;
    }
}
