<?php

namespace Model;

class Bandas extends ActiveRecord
{
    protected static $tabla = 'smm_banda'; //nombre de la tablaX
    protected static $columnasDB = [
      
        'BANDA',
        'SITUACION'
        
    ];

    protected static $idTabla = 'id';
    public $id;
    public $banda;
    public $situacion;
   
    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->banda = $args['banda'] ?? '';
        $this->situacion = $args['situacion'] ?? '1';
     
    }

}