<?php

namespace Classes;

use Mpdf\Mpdf;
use MVC\Router;

class ReportePDF
{
    public $reporte = null;
    private $router = null;
    public function __construct(Router $router, $userInfo)
    {
        $this->reporte = new Mpdf([
            'default_font_size' => '12',
            'default_font' => 'arial',
            'orientation' => 'L',
            'margin_top' => '30',
            'tempDir' => __DIR__ . '/../temp'
        ]);
        $this->router = $router;
        $this->loadHeaderFooter($userInfo);
    }

    private function loadHeaderFooter($userInfo)
    {
        $header = $this->router->load('templates/header', [
            'user' => $userInfo
        ]);

        $footer = $this->router->load('templates/footer');

        $this->reporte->SetHTMLHeader($header);
        $this->reporte->SetHTMLFooter($footer);
    }

    public function generatePDF()
    {
        return $this->reporte;
    }
}