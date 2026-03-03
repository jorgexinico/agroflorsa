<?php
namespace Controllers;

use MVC\Router;
use Model\ActiveRecord;

/**
 * AppController — Dashboard principal
 */
class AppController {

    public static function index(Router $router): void {
        isAuth();
        $router->render('pages/index', [
            'titulo' => 'Dashboard',
        ]);
    }
}