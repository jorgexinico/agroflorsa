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
        'id','nombre','sku','precio_publico','precio_mayorista','marca_id','categoria_id','unidad_id','tipo','maneja_vencimiento','activo','creado_en'
    ];

    public ?int    $id                 = null;
    public string  $nombre             = '';
    public ?string $sku                = null;
    public float   $precio_publico     = 0;
    public float   $precio_mayorista   = 0;
    public ?int    $marca_id           = null;
    public ?int    $categoria_id       = null;
    public ?int    $unidad_id          = null;
    public string  $tipo               = 'mercaderia';
    public int     $maneja_vencimiento = 0;
    public int     $activo             = 1;
    public ?string $creado_en          = null;

    // Campos JOIN (no van a BD)
    public ?string $unidad_nombre       = null;
    public ?string $unidad_abreviatura  = null;
    public ?string $marca_nombre        = null;
    public ?string $categoria_nombre    = null;

    /**
     * Retorna todos los productos activos con su unidad de medida.
     */
    public static function allConUnidad(): array {
        $sql = "SELECT p.*, u.nombre AS unidad_nombre, u.abreviatura AS unidad_abreviatura,
                       m.nombre AS marca_nombre, c.nombre AS categoria_nombre
                FROM productos p
                JOIN unidades_medida u ON u.id = p.unidad_id
                LEFT JOIN marcas m ON m.id = p.marca_id
                LEFT JOIN categorias c ON c.id = p.categoria_id
                WHERE p.activo = 1
                ORDER BY p.nombre";
        return self::queryPrep($sql);
    }

    /**
     * Busca producto por ID con datos de unidad.
     */
    public static function findConUnidad(int $id): ?self {
        $sql = "SELECT p.*, u.nombre AS unidad_nombre, u.abreviatura AS unidad_abreviatura,
                       m.nombre AS marca_nombre, c.nombre AS categoria_nombre
                FROM productos p
                JOIN unidades_medida u ON u.id = p.unidad_id
                LEFT JOIN marcas m ON m.id = p.marca_id
                LEFT JOIN categorias c ON c.id = p.categoria_id
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
