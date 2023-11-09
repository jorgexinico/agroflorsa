<?php

namespace Controllers;

use Model\Bandas;
use MVC\Router;
use Exception;

class BandasController
{
    public static function index(Router $router)
    {
        $router->render('Bandas/index');
    }

    public static function guardarAPI()
    {
        getHeadersApi();




        try {
            $Bandas = new Bandas($_POST);

            $resultado = $Bandas->guardar();

            //    echo json_encode($Bandas);
            //         exit;

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

    public static function buscarAPI()
    {
        getHeadersApi();



        try {
            $Bandas = Bandas::where('situacion', '1');

            echo json_encode($Bandas);
            // return;


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
        $Bandas = new Bandas($_POST);

        $resultado = $Bandas->guardar();

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

        $Bandas = Bandas::find($_POST['id']);

        $Bandas->situacion = 0;

        $resultado = $Bandas->actualizar();



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