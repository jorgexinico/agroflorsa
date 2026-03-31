<?php
namespace Controllers;

use MVC\Router;
use Models\Usuario;
use Model\ActiveRecord;

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

                    // ── Restaurar Turno Activo (si existe) ────────
                    $turnoActivo = ActiveRecord::fetchFirstRaw(
                        "SELECT id, sucursal_id FROM turnos 
                         WHERE usuario_id = :uid AND estado = 'abierto' LIMIT 1",
                        [':uid' => $encontrado->id]
                    );
                    if ($turnoActivo) {
                        $_SESSION['turno_id']    = (int)$turnoActivo['id'];
                        $_SESSION['sucursal_id'] = (int)$turnoActivo['sucursal_id'];
                    }

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
