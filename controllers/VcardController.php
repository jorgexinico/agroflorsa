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
            $id = uniqid();
            $vcard = "BEGIN:VCARD\r\n";
            
            
            $vcard .= "N:Fuetnes;Daniel;\r\n";
            $vcard .= "TITLE:Programador\r\n";
            $vcard .= "ORG:MINDEF\r\n";
            $vcard .= "TEL;TYPE=work,VOICE:+50256568923\r\n";
            $vcard .= "TEL;TYPE=home,VOICE:+50256892322\r\n";
            $vcard .= "TEL;TYPE=mobile:+50255441122\r\n";
            $vcard .= "EMAIL:correo@corre.com\r\n";
            $vcard .= "ADR;TYPE=WORK,PREF:;;Ciudad de Guatemala\r\n";
            $vcard .= "VERSION:3.0\r\n";
            $vcard .= "END:VCARD";
            $qrCode = new QrCode($vcard);
            $output = new Png;
            $data = $output->output($qrCode,100, [255,255,255], [0,0,0]);
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
        $html = "<div style='width: 300px;
        border: 3px solid black;
        padding: 1px;
        text-align: center;'> <div style='margin-top: -5px; z-index:4' > <p> Generado por:   </p> 

        <div style='margin-top:-50px; z-index:1' >
        <img src='$nombre' width='300px' /></div>
        <div style='margin-top:-50px; z-index:2' >";

        $mpdf->WriteHTML($html);
        // $mpdf->WriteHTML("");
        // $mpdf->WriteHTML("<");
        // $mpdf->WriteHTML("");
        
        $mpdf->Output();
    }

}