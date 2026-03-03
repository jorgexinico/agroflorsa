<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Producto — tabla `productos`
 */
class Producto extends ActiveRecord {

    protected static $tabla      = 'productos';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id','nombre','sku','unidad_id','tipo','maneja_vencimiento','activo','creado_en'
    ];

    public ?int    $id                 = null;
    public string  $nombre             = '';
    public ?string $sku                = null;
    public ?int    $unidad_id          = null;
    public string  $tipo               = 'mercaderia';
    public int     $maneja_vencimiento = 0;
    public int     $activo             = 1;
    public ?string $creado_en          = null;

    // Campos JOIN (no van a BD)
    public ?string $unidad_nombre       = null;
    public ?string $unidad_abreviatura  = null;

    /**
     * Retorna todos los productos activos con su unidad de medida.
     */
    public static function allConUnidad(): array {
        $sql = "SELECT p.*, u.nombre AS unidad_nombre, u.abreviatura AS unidad_abreviatura
                FROM productos p
                JOIN unidades_medida u ON u.id = p.unidad_id
                WHERE p.activo = 1
                ORDER BY p.nombre";
        return self::queryPrep($sql);
    }

    /**
     * Busca producto por ID con datos de unidad.
     */
    public static function findConUnidad(int $id): ?self {
        $sql = "SELECT p.*, u.nombre AS unidad_nombre, u.abreviatura AS unidad_abreviatura
                FROM productos p
                JOIN unidades_medida u ON u.id = p.unidad_id
                WHERE p.id = :id
                LIMIT 1";
        $resultado = self::queryPrep($sql, [':id' => $id]);
        return array_shift($resultado) ?: null;
    }

    public function validar(): array {
        static::$alertas = [];
        if (empty($this->nombre))    self::setAlerta('danger', 'El nombre del producto es obligatorio');
        if (empty($this->unidad_id)) self::setAlerta('danger', 'Selecciona una unidad de medida');
        return static::$alertas;
    }
}
