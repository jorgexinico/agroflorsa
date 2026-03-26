<?php
namespace Models;

use Model\ActiveRecord;

class Traslado extends ActiveRecord {
    protected static $tabla      = 'traslados';
    protected static $idTabla    = 'id';
    protected static $columnasDB = ['id','sucursal_origen_id','sucursal_destino_id','fecha','estado','nota'];

    public ?int    $id                  = null;
    public ?int    $sucursal_origen_id   = null;
    public ?int    $sucursal_destino_id  = null;
    public string  $fecha               = '';
    public string  $estado              = 'pendiente';
    public ?string $nota                = null;

    public function __construct($args = []) {
        $this->id                  = $args['id']                  ?? null;
        $this->sucursal_origen_id   = $args['sucursal_origen_id']   ?? null;
        $this->sucursal_destino_id  = $args['sucursal_destino_id']  ?? null;
        $this->fecha               = $args['fecha']               ?? date('Y-m-d H:i:s');
        $this->estado              = $args['estado']              ?? 'pendiente';
        $this->nota                = $args['nota']                ?? '';
    }
}
