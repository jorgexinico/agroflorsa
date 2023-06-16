<?php

namespace Controllers;

use Exception;
use Mpdf\Mpdf;
use Mpdf\QrCode\Output\Png;
use Mpdf\QrCode\QrCode;
use MVC\Router;

class VcardController {
    public static function index(Router $router){
        $router->render('vcard/index', []);
    }
    public static function generarAPI(){
        getHeadersApi();
        try {
            extract($_POST);
            $id = uniqid();
            $vcard = "BEGIN:VCARD\r\n";
            
            
            $vcard .= "N:$apellido;$nombre;\r\n";
            $vcard .= "TITLE:$puesto\r\n";
            $vcard .= "ORG:$organizacion\r\n";
            $vcard .= "TEL;TYPE=work,VOICE:+502$telefono_trabajo\r\n";
            $vcard .= "TEL;TYPE=mobile:+502$telefono_personal\r\n";
            $vcard .= "EMAIL:$correo\r\n";
            $vcard .= "EMAIL;TYPE=WORK:$correo_empresa\r\n";
            $vcard .= "ADR;Home:;;$direccion\r\n";
            $vcard .= "VERSION:3.0\r\n";
            $vcard .= "END:VCARD";
            $qrCode = new QrCode($vcard);
            $output = new Png;
            $data = $output->output($qrCode,100, [255,255,255], [2,156,223]);
            $nombre = '../storage/' . $id .  ".png";
            $guardado = file_put_contents($nombre, $data);

            // if(file)

            echo json_encode($id);

        
        } catch (Exception $e) {
            echo json_encode([
                "detalle" => $e->getMessage(),
                "mensaje" => "Ocurrió un error de ejecución",
                "codigo" => 4,
            ]);
        }
    }

    public static function imprimir(){
        $idImagen = $_GET['idImagen'];

        $mpdf = new Mpdf([
            'default_font_size' => '12',
            'default_font' => 'arial',
            'orientation' => 'P',
            'margin_top' => '30',
            'format' => 'letter'
        ]);
        $nombre = '../storage/' . $idImagen .  ".png";
        $html = "<div style='width: 500px;
        border: 3px solid rgb(2,156,223);
        border-radius: 5%;
        padding: 1px;
        text-align: center;'> 
        <img src='$nombre' width='100%' /></div>
        <div style='margin-top:-50px; z-index:2' >";

        $mpdf->WriteHTML($html);
        // $mpdf->WriteHTML("");
        // $mpdf->WriteHTML("<");
        // $mpdf->WriteHTML("");
        
        $mpdf->Output();
    }

}