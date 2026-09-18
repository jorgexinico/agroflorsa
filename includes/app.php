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

// SSO autentica una vez; después se utiliza únicamente la sesión y BD locales.
if (filter_var($_ENV['SSO_ENABLED'] ?? false,FILTER_VALIDATE_BOOLEAN)) {
    require __DIR__.'/sso-session.php';
}