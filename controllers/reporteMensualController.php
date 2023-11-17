<?php

namespace Controllers;

use DateTime;
use Model\reporteMensual;
use MVC\Router;
use Model\ActiveRecord;
use Classes\ReportePDF;
use Exception;

class reporteMensualController
{
    public static function index(Router $router)
    {
        $actividad = reporteMensual::fetchArray("SELECT * from smm_actividad where situacion = 1 ");
        $banda = reporteMensual::fetchArray("SELECT * from smm_banda where situacion = 1 ");
        $comando = reporteMensual::fetchArray("SELECT * FROM mdep where dep_llave between 2010 and 4030 order by dep_desc_md asc;");

        $dependencias = reporteMensual::fetchArray("SELECT * FROM MPER FULL OUTER JOIN morg ON per_plaza = org_plaza FULL OUTER JOIN mdep ON dep_llave = org_dependencia WHERE per_catalogo = user");
        foreach ($dependencias as $key => $value) {

            $dependencia = $value['dep_desc_ct'];
            $org_dep = $value['org_dependencia'];
            // var_dump($org_dep);
        }

        $router->render('reporteMensual/index', [


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

        $horain = $_POST['horain'];
        $horafin = $_POST['horafin'];
        $horavalidar = $_POST['horavalidar'];

        //           echo json_encode($_POST);
        // exit;

        if ($checkcivil == null) {
            try {
                //   echo json_encode('ENTRO AQUI');
//         exit;

                $sql = "INSERT INTO smm_reporte VALUES (0, $banda, $comisionMilitar, '$comision1Civil', $actividad, $cantidad, '$fecha', '$horain', '$horafin', 1)";

                $resultado = reporteMensual::sql($sql);
                // echo json_encode($resultado);



                if ($resultado == 1) {
                    // echo json_encode('aquiesoty');
                    // exit;
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
        if ($checkcivil == 1) {
            try {

                //         echo json_encode('AQUI NO ENTRO AQUI');
                // exit;
                $insertHistorial = "INSERT INTO smm_reporte VALUES (0, $banda, 999, '$comision1Civil', $actividad, $cantidad, '$fecha', '$horain', '$horafin', 1)";

                $resultado = reporteMensual::sql($insertHistorial);

                // echo json_encode($resultado);
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
        date_default_timezone_set("America/Caracas");
        setlocale(LC_TIME, 'es_VE.UTF-8','esp');
        $mes = date("m");
        $ano = date("Y");


        $inicia = $ano.'-'.$mes.'-'. 01;
        $termina= $ano.'-'.$mes.'-'. 30;
        //     echo json_encode($termina);
        // exit;


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
            smm_reporte.situacion as situacion,
            TO_CHAR(CURRENT, '%H:%M') as horaactualizada
            from smm_reporte, smm_banda, mdep, smm_actividad
            where smm_reporte.nombre = smm_banda.id 
            and smm_reporte.comision=mdep.dep_llave 
            and smm_reporte.motivo=smm_actividad.id
            and smm_reporte.situacion=1
            and smm_reporte.fecha BETWEEN '$inicia' AND '$termina'";


            $resultado = reporteMensual::fetchArray($reporte);
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
        $reporteMensual = new reporteMensual($_POST);

        $resultado = $reporteMensual->guardar();

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

        $reporteMensual = reporteMensual::find($_POST['id']);

        $reporteMensual->situacion = 0;

        $resultado = $reporteMensual->actualizar();



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

    public static function pdf_busqueda(Router $router)
    {

        date_default_timezone_set("America/Caracas");
        setlocale(LC_TIME, 'es_VE.UTF-8','esp');
        $mes = date("m");
        $ano = date("Y");


        $inicia = $ano.'-'.$mes.'-'. 01;
        $termina= $ano.'-'.$mes.'-'. 30;
        try {
            getHeadersApi();

            $user = $_SESSION['auth_user'];

            $userInfo = ActiveRecord::fetchFirst("SELECT * from mper inner join morg on per_plaza = org_plaza inner join mdep on org_dependencia = dep_llave where per_catalogo = $user ");


            $sql = "SELECT smm_reporte.id as id,
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
            smm_reporte.situacion as situacion,
            TO_CHAR(CURRENT, '%H:%M') as horaactualizada
            from smm_reporte, smm_banda, mdep, smm_actividad
            where smm_reporte.nombre = smm_banda.id 
            and smm_reporte.comision=mdep.dep_llave 
            and smm_reporte.motivo=smm_actividad.id
            and smm_reporte.situacion=1
            and smm_reporte.fecha BETWEEN '$inicia' AND '$termina'";

            $dataOperaciones = reporteMensual::fetchArray($sql);
            //echo json_encode ($dataOperaciones);
            //exit;

            $operaciones = [];
            $total = 0;
            foreach ($dataOperaciones as $key => $registro) {
                $operaciones[] = [
                    'contador' => $key + 1,
                    'nombrebanda' => $registro['nombrebanda'],
                    'idcomision' => $registro['idcomision'],
                    'comisionnombre' => $registro['comisionnombre'],
                    'civil' => $registro['civil'],
                    'motivo' => $registro['motivo'],
                    'cantidad' => $registro['cantidad'],
                    'fecha' => $registro['fecha'],
                    'horain' => $registro['horain'],
                    'horafin' => $registro['horafin'],
                    'horaactualizada' => $registro['horaactualizada'],

                ];
                //$total += $registro['distancia'];
            }

            // echo json_encode ($operaciones);
            //              exit;

            // echo json_encode($catalogo);
// exit;
            $reporte = new ReportePDF($router, $userInfo);
            $pdf = $reporte->generatePDF();

            $contenido = $router->load('impresion/pdfbusquedaMensual', [
                /* 'g3' => $g3,
                 'totales' => $totales,*/
                'operaciones' => $operaciones,
                // 'catalogo' => $catalogo,


            ]);


            $pdf->WriteHTML($contenido);



            $pdf->Output();
        } catch (Exception $e) {
            echo json_encode([
                "detalle" => $e->getMessage(),
                "mensaje" => "Ocurrió  un error en base de datos.",

                "codigo" => 4,
            ]);
            exit;
        }
    }
    public static function pdf_busquedaReporte(Router $router)
    {
        try {
            getHeadersApi();

            date_default_timezone_set("America/Caracas");
            setlocale(LC_TIME, 'es_VE.UTF-8','esp');
            $mes = date("m");
            $ano = date("Y");
    
            $fechaReporte = $_GET['fechaReporte'];


            $inicia = $ano.'-'.$fechaReporte.'-'. 01;
            $termina= $ano.'-'.$fechaReporte.'-'. 30;
            
            $user = $_SESSION['auth_user'];

            $userInfo = ActiveRecord::fetchFirst("SELECT * from mper inner join morg on per_plaza = org_plaza inner join mdep on org_dependencia = dep_llave where per_catalogo = $user ");


            $sql = " SELECT smm_reporte.id as id,
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
            smm_reporte.situacion as situacion,
            TO_CHAR(CURRENT, '%H:%M') as horaactualizada
            from smm_reporte, smm_banda, mdep, smm_actividad
            where smm_reporte.nombre = smm_banda.id 
            and smm_reporte.comision=mdep.dep_llave 
            and smm_reporte.motivo=smm_actividad.id
            and smm_reporte.situacion=1
            and smm_reporte.fecha BETWEEN '$inicia' AND '$termina'";

            $dataOperaciones = reporteMensual::fetchArray($sql);
            //echo json_encode ($dataOperaciones);
            //exit;

            $operaciones = [];
            $total = 0;
            foreach ($dataOperaciones as $key => $registro) {
                $operaciones[] = [
                    'contador' => $key + 1,
                    'nombrebanda' => $registro['nombrebanda'],
                    'idcomision' => $registro['idcomision'],
                    'comisionnombre' => $registro['comisionnombre'],
                    'civil' => $registro['civil'],
                    'motivo' => $registro['motivo'],
                    'cantidad' => $registro['cantidad'],
                    'fecha' => $registro['fecha'],
                    'horain' => $registro['horain'],
                    'horafin' => $registro['horafin'],
                    'horaactualizada' => $registro['horaactualizada'],

                ];
                //$total += $registro['distancia'];
            }

            // echo json_encode ($operaciones);
            //              exit;

            // echo json_encode($catalogo);
// exit;
            $reporte = new ReportePDF($router, $userInfo);
            $pdf = $reporte->generatePDF();

            $contenido = $router->load('impresion/pdfbusquedaReporteMensualElegido', [
                /* 'g3' => $g3,
                 'totales' => $totales,*/
                'operaciones' => $operaciones,
                'fechaReporte' => $fechaReporte,


            ]);


            $pdf->WriteHTML($contenido);



            $pdf->Output();
        } catch (Exception $e) {
            echo json_encode([
                "detalle" => $e->getMessage(),
                "mensaje" => "Ocurrió  un error en base de datos.",

                "codigo" => 4,
            ]);
            exit;
        }
    }




}