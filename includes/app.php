<?php
use Dotenv\Dotenv;
use Model\ActiveRecord;


require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
if(session_status()!==PHP_SESSION_ACTIVE){
    $sessionDir=dirname(__DIR__).'/storage/sessions';
    if(!is_dir($sessionDir))mkdir($sessionDir,0700,true);
    session_save_path($sessionDir);
    ini_set('session.gc_maxlifetime','28800');
    ini_set('session.use_strict_mode','1');
    session_name('agroflorsa_session');
    session_set_cookie_params([
        'lifetime'=>0, 'path'=>'/', 'httponly'=>true, 'samesite'=>'Lax',
        'secure'=>filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN)
            || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

ini_set('display_errors', $_ENV['DEBUG_MODE']);
ini_set('display_startup_errors', $_ENV['DEBUG_MODE']);
error_reporting(-$_ENV['DEBUG_MODE']);

// Configurar la zona horaria para toda la aplicación
date_default_timezone_set('America/Guatemala');

// Prefijo de APP_NAME ya no requiere urlBase dinámico

require 'funciones.php';
require 'database.php';
// Conectarnos a la base de datos

ActiveRecord::setDB($db);

// Control central para todas las rutas cuando está activa la integración nueva.
if (filter_var($_ENV['SSO_ENABLED'] ?? false,FILTER_VALIDATE_BOOLEAN)) {
    $path=rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/',PHP_URL_PATH),'/');
    $publicPaths=[rtrim(url('/login'),'/'),rtrim(url('/sso'),'/'),rtrim(url('/sso/start'),'/'),rtrim(url('/logout'),'/')];
    $publicPaths[]=rtrim(url('/sso/activity'),'/');
    if (!in_array($path,$publicPaths,true)) {
        if(isset($_SESSION['usuario_id'],$_SESSION['portal_subject'])) {
            try { \Classes\SsoClient::renew(); }
            catch(\Throwable $e) {
                http_response_code(503);
                exit('No se pudo renovar la conexión con Login. La operación no se ejecutó; conserva el formulario y vuelve a intentarlo.');
            }
        }
        if (!isset($_SESSION['usuario_id'],$_SESSION['portal_subject']) || ($_SESSION['portal_expires'] ?? 0)<=time()) {
            // Una petición paralela (por ejemplo favicon) no debe borrar el state
            // que se está usando en el recorrido de autorización.
            unset($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], $_SESSION['usuario_rol'],
                $_SESSION['portal_subject'], $_SESSION['portal_expires'], $_SESSION['turno_id'], $_SESSION['sucursal_id']);
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(401);
                exit('La sesión debe renovarse. Abre el inicio de Agroflorsa y vuelve a intentar la operación.');
            }
            header('Location: '.url('/sso/start'));
            exit;
        }
        $identity=(new \Classes\PortalIdentity($db))->find($_SESSION['portal_issuer'] ?? $_ENV['SSO_ISSUER'],$_SESSION['portal_subject']);
        if (!$identity || (int)$identity['id']!==(int)$_SESSION['usuario_id']) { $_SESSION=[]; http_response_code(403); exit('Usuario deshabilitado o identidad no autorizada.'); }
        $_SESSION['usuario_rol']=$identity['rol'];
    }
}
