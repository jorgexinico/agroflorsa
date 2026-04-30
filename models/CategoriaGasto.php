<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo CategoriaGasto — tabla `categorias_gastos`
 */
class CategoriaGasto extends ActiveRecord {

    protected static $tabla      = 'categorias_gastos';
    protected static $idTabla    = 'id';
    protected static $columnasDB = ['id', 'nombre', 'descripcion'];

    public ?int    $id          = null;
    public string  $nombre      = '';
    public ?string $descripcion = null;

    /** Obtiene todas las categorias de gastos activas */
    public static function allCategorias(): array {
        return self::fetchRaw("SELECT * FROM categorias_gastos ORDER BY nombre ASC");
    }
}
