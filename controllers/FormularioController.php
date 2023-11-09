<?php

namespace Controllers;

use DateTime;
use Model\Formulario;
use MVC\Router;
use Exception;

class FormularioController
{
    public static function index(Router $router)
    {
        $actividad = Formulario::fetchArray("SELECT * from smm_actividad where situacion = 1 ");
        $banda = Formulario::fetchArray("SELECT * from smm_banda where situacion = 1 ");
        $comando = Formulario::fetchArray("SELECT * FROM mdep where dep_llave between 2010 and 4030 order by dep_desc_md asc;");

        $dependencias = Formulario::fetchArray("SELECT * FROM MPER FULL OUTER JOIN morg ON per_plaza = org_plaza FULL OUTER JOIN mdep ON dep_llave = org_dependencia WHERE per_catalogo = user");
        foreach ($dependencias as $key => $value) {

            $dependencia = $value['dep_desc_ct'];
            $org_dep = $value['org_dependencia'];
            // var_dump($org_dep);
        }

        $router->render('Formulario/index', [


            'org_dep' => $org_dep,
            'actividad' => $actividad,
            'banda' => $banda,
            'comando' => $comando,
        ]);
    }

    public static function guardarAPI()
    {
        getHeadersApi();
        $actividad = $_POST['actividad'];
        $banda = $_POST['banda'];
        $cantidad = $_POST['cantidad'];
        $checkcivil = $_POST['checkcivil'];
        $comision1Civil = $_POST['comision1Civil'];
        $comisionMilitar = $_POST['comisionMilitar'];
        $fecha = $_POST['fecha'];

        $horain = new DateTime($_POST['horain']);
        $horafin =$_POST['horafin'];
        $horavalidar = $_POST['horavalidar'];


       $fechainicial = $horain->format('d-m-Y H:i:s');

        echo json_encode($fechainicial);
        exit;
        
        if ($checkcivil == null) {
            try {
                

                $sql="INSERT INTO smm_reporte VALUES (0, $banda, $comisionMilitar, '$comision1Civil', $actividad, $cantidad, '$fecha', '$horain', '$horafin', 1)";

                
                // $resultado = Formulario::sql($insertHistorial);
                
                echo json_encode($sql);
                exit;
                

                if ($resultado['resultado'] == 1) {
                    echo json_encode([
                        "resultado" => 1
                    ]);

                } else {
                    echo json_encode([
                        "resultado" => 0
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    "detalle" => $e->getMessage(),
                    "mensaje" => "Ocurrió  un error en base de datos.",

                    "codigo" => 4,
                ]);
            }

        } else {
            try {

                echo json_encode($checkcivil);
                exit;

                $insertHistorial = Formulario::sql("INSERT INTO smm_reporte VALUES (0, $banda, $comisionMiliar, $comision1Civil, $actividad, $fecha, $horain, $horafin)");

                $resultado = Formulario::sql($insertHistorial);


                if ($resultado['resultado'] == 1) {
                    echo json_encode([
                        "resultado" => 1
                    ]);

                } else {
                    echo json_encode([
                        "resultado" => 0
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    "detalle" => $e->getMessage(),
                    "mensaje" => "Ocurrió  un error en base de datos.",

                    "codigo" => 4,
                ]);
            }
        }



    }

    public static function buscarAPI()
    {
        getHeadersApi();



        try {
            $reporte = "SELECT smm_reporte.id as id,
            smm_reporte.nombre as idnombre,
            smm_banda.banda as nombrebanda,
            smm_reporte.comision as idcomision,
            mdep.dep_desc_md as comisionnombre,
            smm_reporte.civil as civil,
            smm_reporte.motivo as idmotivo,
            smm_actividad.actividad as motivo,
            smm_reporte.cantidad as cantidad,
            smm_reporte.fecha as fecha,
            smm_reporte.horain as horain,
            smm_reporte.horafin as horafin,
            smm_reporte.situacion as situacion
            from smm_reporte, smm_banda, mdep, smm_actividad
            where smm_reporte.nombre = smm_banda.id 
            and smm_reporte.comision=mdep.dep_llave 
            and smm_reporte.motivo=smm_actividad.id
            
            and smm_reporte.situacion=1
              and smm_reporte.fecha = date(current)";


            $resultado = Formulario::sql($reporte);
            echo json_encode($resultado);
            // exit;


        } catch (Exception $e) {
            echo json_encode([
                "detalle" => $e->getMessage(),
                "mensaje" => "Ocurrió  un error en base de datos.",

                "codigo" => 4,
            ]);
        }



    }

    public static function modificarAPI()
    {
        getHeadersApi();
        $Formulario = new Formulario($_POST);

        $resultado = $Formulario->guardar();

        if ($resultado['resultado'] == 1) {
            echo json_encode([
                "resultado" => 1
            ]);

        } else {
            echo json_encode([
                "resultado" => 0
            ]);

        }
    }

    public static function eliminarAPI()
    {

        getHeadersApi();

        $Formulario = Formulario::find($_POST['id']);

        $Formulario->situacion = 0;

        $resultado = $Formulario->actualizar();



        if ($resultado['resultado'] == 1) {
            echo json_encode([
                "resultado" => 1
            ]);

        } else {
            echo json_encode([
                "resultado" => 0
            ]);

        }
    }
}