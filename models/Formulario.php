<?php

namespace Model;

class Formulario extends ActiveRecord
{
    protected static $tabla = 'smm_reporte'; //nombre de la tablaX
    protected static $columnasDB = [
      
        'NOMBRE',
        'COMISION',
        'CIVIL',
        'MOTIVO',
        'CANTIDAD',
        'FECHA',
        'HORAIN',
        'HORAFIN',
        'SITUACION'
        
    ];

    protected static $idTabla = 'id';
    public $id;
    public $nombre;
    public $comision;
    public $civil;
    public $motivo;
    public $cantidad;
    public $fecha;
    public $horain;
    public $horafin;
    public $situacion;
   
    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->comision = $args['comision'] ?? '';
        $this->civil = $args['civil'] ?? '';
        $this->motivo = $args['motivo'] ?? '';
        $this->cantidad = $args['cantidad'] ?? '';
        $this->fecha = $args['fecha'] ?? '';
        $this->horain = $args['horain'] ?? '';
        $this->horafin = $args['horafin'] ?? '';
        $this->situacion = $args['situacion'] ?? '1';
     
    }

}