<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Gasto — tabla `gastos`
 */
class Gasto extends ActiveRecord {

    protected static $tabla      = 'gastos';
    protected static $idTabla    = 'id';
    protected static $columnasDB = [
        'id', 'sucursal_id', 'usuario_id', 'categoria_id', 'monto', 
        'fecha', 'descripcion', 'comprobante', 'estado'
    ];

    public ?int    $id           = null;
    public ?int    $sucursal_id  = null;
    public ?int    $usuario_id   = null;
    public ?int    $categoria_id = null;
    public float   $monto        = 0;
    public string  $fecha        = '';
    public string  $descripcion  = '';
    public ?string $comprobante  = null;
    public string  $estado       = 'completado';

    // Joins
    public ?string $sucursal_nombre  = null;
    public ?string $usuario_nombre   = null;
    public ?string $categoria_nombre = null;

    /**
     * Valida los datos antes de guardar
     */
    public function validar(): array {
        if(!$this->categoria_id) {
            self::$alertas['error'][] = "Debes seleccionar una categoría de gasto";
        }
        if(!$this->descripcion) {
            self::$alertas['error'][] = "La descripción es obligatoria";
        }
        if($this->monto <= 0) {
            self::$alertas['error'][] = "El monto debe ser mayor a 0";
        }
        return self::$alertas;
    }
}
