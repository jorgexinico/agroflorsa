<?php

namespace Model;

class Actividad extends ActiveRecord
{
    protected static $tabla = 'smm_actividad'; //nombre de la tablaX
    protected static $columnasDB = [
        'ID',
        'ACTIVIDAD',
        'SITUACION'
        
    ];

    public $id;
    public $actividad;
    public $situacion;
   
    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->actividad = $args['actividad'] ?? '';
        $this->situacion = $args['situacion'] ?? '1';
     
    }


}