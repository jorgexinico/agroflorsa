<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo AjusteInventario — tabla `ajustes_inventario`
 */
class AjusteInventario extends ActiveRecord {
    protected static $tabla      = 'ajustes_inventario';
    protected static $idTabla    = 'id';
    protected static $columnasDB = ['id','sucursal_id','fecha','motivo','usuario_id'];

    public ?int    $id          = null;
    public ?int    $sucursal_id = null;
    public ?string $fecha       = null;
    public string  $motivo      = '';
    public ?int    $usuario_id  = null;

    public function validar(): array {
        static::$alertas = [];
        if (!$this->sucursal_id) self::setAlerta('danger', 'La sucursal es obligatoria');
        if (empty($this->motivo))   self::setAlerta('danger', 'El motivo es obligatorio');
        return static::$alertas;
    }
}
