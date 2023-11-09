<?php

namespace Controllers;

use Model\Actividad;
use MVC\Router;
use Exception;

class ActividadController
{

    public function index(Router $router)
    {
        $router->render('Actividad/index');
    }

    public function guardarAPI()
    {
        getHeadersApi();



        try {
            $Actividad = new Actividad($_POST);
            // echo json_encode($Actividad);
            // exit;
            $resultado = $Actividad->guardar();


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

    public function buscarAPI()
    {
        getHeadersApi();



        try {
            $Actividad = Actividad::where('situacion', '1');
            echo json_encode($Actividad);

        } catch (Exception $e) {
            echo json_encode([
                "detalle" => $e->getMessage(),
                "mensaje" => "Ocurrió  un error en base de datos.",

                "codigo" => 4,
            ]);
        }



    }

    public function modificarAPI()
    {
        getHeadersApi();

        // $Actividad = $_POST;
        
        $id = $_POST['id'];
        $actividad = $_POST['actividad'];
        $situacion = $_POST['situacion'];
        
        // echo json_encode($actividad);
        // exit;
        if ($id != '') {

            try {
                $insertar = "UPDATE smm_actividad set actividad = '$actividad' where id = $id";
                $resultado = Actividad::sql($insertar);
        //         echo json_encode($insertar);
        // exit;
                if ($resultado == 1) {
                    echo json_encode([
                        "resultado" => 1
                    ]);
        
                } else {
                    echo json_encode([
                        "resultado" => 0
                    ]);
        
                }
            }catch (Exception $e){
                echo json_encode([
                    "detalle" => $e->getMessage(),
                    "mensaje" => "Ocurrió  un error en base de datos.",

                    "codigo" => 4,
                ]);
            }

        }

        
    }

    public function eliminarAPI()
    {
        $id = $_POST['id'];
        // $actividad = $_POST['actividad'];
        // $situacion = $_POST['situacion'];
        
        // echo json_encode($id);
        // exit;
        if ($id != '') {

            try {
                $insertar = "UPDATE smm_actividad set situacion = 0 where id = $id";
                $resultado = Actividad::sql($insertar);
        //         echo json_encode($insertar);
        // exit;
                if ($resultado == 1) {
                    echo json_encode([
                        "resultado" => 1
                    ]);
        
                } else {
                    echo json_encode([
                        "resultado" => 0
                    ]);
        
                }
            }catch (Exception $e){
                echo json_encode([
                    "detalle" => $e->getMessage(),
                    "mensaje" => "Ocurrió  un error en base de datos.",

                    "codigo" => 4,
                ]);
            }

        }
    }
}