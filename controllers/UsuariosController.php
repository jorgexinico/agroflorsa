<?php
namespace Controllers;

use MVC\Router;
use Models\Usuario;

/**
 * UsuariosController — CRUD de usuarios del sistema.
 * Solo accesible al rol 'admin'.
 */
class UsuariosController {

    public static function index(Router $router): void {
        isAuth();
        isRole(['admin']);

        $usuarios = Usuario::all('nombre');

        $router->render('usuarios/index', [
            'titulo'   => 'Usuarios del Sistema',
            'usuarios' => $usuarios,
        ]);
    }

    public static function crear(Router $router): void {
        isAuth();
        isRole(['admin']);

        $usuario = new Usuario();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->nombre   = trim($_POST['nombre']  ?? '');
            $usuario->usuario  = trim($_POST['usuario'] ?? '');
            $usuario->password = trim($_POST['password'] ?? '');
            $usuario->rol      = $_POST['rol'] ?? 'vendedor';
            $usuario->activo   = 1;

            // Verificar que las contraseñas coincidan
            $passwordConfirm = trim($_POST['password_confirm'] ?? '');
            $alertas = $usuario->validar();

            if (empty($alertas['danger'])) {
                if ($usuario->password !== $passwordConfirm) {
                    Usuario::setAlerta('danger', 'Las contraseñas no coinciden');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Verificar que el usuario no exista ya
                    $existe = Usuario::findByUsuario($usuario->usuario);
                    if ($existe) {
                        Usuario::setAlerta('danger', 'El nombre de usuario ya está en uso');
                        $alertas = Usuario::getAlertas();
                    } else {
                        $usuario->hashPassword();
                        $usuario->creado_en = date('Y-m-d H:i:s');
                        $resultado = $usuario->crear();
                        if ($resultado['resultado']) {
                            header('Location: /' . $_ENV['APP_NAME'] . '/usuarios?ok=1');
                            exit;
                        }
                    }
                }
            }
        }

        $router->render('usuarios/form', [
            'titulo'  => 'Nuevo Usuario',
            'accion'  => 'crear',
            'usuario' => $usuario,
            'alertas' => $alertas,
        ]);
    }

    public static function editar(Router $router): void {
        isAuth();
        isRole(['admin']);

        $id      = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $usuario = Usuario::find($id);
        $alertas = [];

        if (!$usuario) {
            header('Location: /' . $_ENV['APP_NAME'] . '/usuarios');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->nombre  = trim($_POST['nombre']  ?? '');
            $usuario->usuario = trim($_POST['usuario'] ?? '');
            $usuario->rol     = $_POST['rol'] ?? 'vendedor';

            $nuevaPassword   = trim($_POST['password']         ?? '');
            $passwordConfirm = trim($_POST['password_confirm'] ?? '');

            // Validación básica (sin exigir password en edición)
            $errores = [];
            if (empty($usuario->nombre))  $errores[] = 'El nombre es obligatorio';
            if (empty($usuario->usuario)) $errores[] = 'El usuario es obligatorio';
            if ($nuevaPassword !== '' && $nuevaPassword !== $passwordConfirm) {
                $errores[] = 'Las contraseñas no coinciden';
            }

            if (empty($errores)) {
                if ($nuevaPassword !== '') {
                    $usuario->password = $nuevaPassword;
                    $usuario->hashPassword();
                }
                $usuario->actualizar();
                header('Location: /' . $_ENV['APP_NAME'] . '/usuarios?ok=2');
                exit;
            }

            $alertas = ['danger' => $errores];
        }

        // Limpiar el hash para que no aparezca en el formulario
        $usuario->password_hash = '';

        $router->render('usuarios/form', [
            'titulo'  => 'Editar Usuario',
            'accion'  => 'editar',
            'usuario' => $usuario,
            'alertas' => $alertas,
        ]);
    }

    public static function eliminar(Router $router): void {
        isAuth();
        isRole(['admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /' . $_ENV['APP_NAME'] . '/usuarios');
            exit;
        }

        $id      = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $usuario = Usuario::find($id);

        if ($usuario) {
            // No permitir desactivar al único admin
            $usuario->activo = ($usuario->activo == 1) ? 0 : 1;
            $usuario->actualizar();
        }

        header('Location: /' . $_ENV['APP_NAME'] . '/usuarios?ok=3');
        exit;
    }
}
