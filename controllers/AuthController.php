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
        if (filter_var($_ENV['SSO_ENABLED'] ?? false,FILTER_VALIDATE_BOOLEAN)) {
            \Classes\SsoClient::start();
        }
        $portalUrl = trim($_ENV['PORTAL_URL'] ?? '');
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $portalUrl !== '') {
            header('Location: ' . $portalUrl);
            exit;
        }
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

    public static function sso(Router $router): void {
        global $db;
        $token = (string) ($_GET['token'] ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            http_response_code(403);
            exit('Acceso no autorizado.');
        }

        $db->beginTransaction();
        try {
            $statement = $db->prepare(
                "SELECT t.id, u.id AS usuario_id, u.nombre, u.rol, u.activo
                 FROM tokens_acceso t
                 INNER JOIN usuarios u ON u.id = t.usuario_id
                 INNER JOIN aplicaciones a ON a.id = t.aplicacion_id
                 WHERE t.token_hash = :token_hash AND a.slug = 'agroflorsa'
                   AND t.usado_en IS NULL AND t.expira_en >= NOW()
                 FOR UPDATE"
            );
            $statement->execute(['token_hash' => hash('sha256', $token)]);
            $access = $statement->fetch();
            if (!$access || (int) $access['activo'] !== 1) {
                $db->rollBack();
                http_response_code(403);
                exit('El enlace de acceso es inválido o expiró.');
            }
            $db->prepare('UPDATE tokens_acceso SET usado_en = NOW() WHERE id = :id')->execute(['id' => $access['id']]);
            $db->commit();

            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) $access['usuario_id'];
            $_SESSION['usuario_nombre'] = $access['nombre'];
            $_SESSION['usuario_rol'] = $access['rol'];
            redirectTo('/dashboard');
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) $db->rollBack();
            error_log($exception->getMessage());
            http_response_code(500);
            exit('No fue posible completar el acceso.');
        }
    }

    public static function logout(Router $router): void {
        $_SESSION = [];
        session_destroy();
        if (filter_var($_ENV['SSO_ENABLED'] ?? false,FILTER_VALIDATE_BOOLEAN)) {
            header('Location: '.rtrim($_ENV['SSO_PORTAL_URL'],'/'));
            exit;
        }
        redirectTo('/login');
    }
}
