<?php
namespace Controllers;

use MVC\Router;
use Models\Usuario;

/**
 * AuthController — Login y Logout
 */
class AuthController {

    public static function login(Router $router): void {
        // Si ya está logueado, redirigir al dashboard
        if (isset($_SESSION['usuario_id'])) {
            redirectTo('/dashboard');
        }

        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario();
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarLogin();

            if (empty($alertas)) {
                // Buscar usuario en BD
                $encontrado = Usuario::findByUsuario($usuario->usuario);

                if (!$encontrado || !$encontrado->verificarPassword($usuario->password)) {
                    Usuario::setAlerta('danger', 'Usuario o contraseña incorrectos');
                    $alertas = Usuario::getAlertas();
                } elseif ($encontrado->activo != 1) {
                    Usuario::setAlerta('danger', 'Tu cuenta está inactiva. Contacta al administrador.');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Crear sesión
                    session_regenerate_id(true);
                    $_SESSION['usuario_id']     = $encontrado->id;
                    $_SESSION['usuario_nombre'] = $encontrado->nombre;
                    $_SESSION['usuario_rol']    = $encontrado->rol;

                    redirectTo('/dashboard');
                }
            }
        }

        $router->render('auth/login', [
            'alertas' => $alertas,
            'titulo'  => 'Iniciar Sesión',
            'layout'  => 'auth', // sin sidebar
        ]);
    }

    public static function logout(Router $router): void {
        $_SESSION = [];
        session_destroy();
        redirectTo('/login');
    }
}
