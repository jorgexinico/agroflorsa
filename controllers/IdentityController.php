<?php
namespace Controllers;
use MVC\Router;
use Classes\PortalIdentity;

final class IdentityController {
    public static function index(Router $router):void {
        global $db;
        isAuth();isRole(['admin']);
        if(empty($_SESSION['identity_csrf']))$_SESSION['identity_csrf']=bin2hex(random_bytes(32));
        $message='';
        if($_SERVER['REQUEST_METHOD']==='POST') {
            $token=$_POST['csrf']??'';
            if(!is_string($token)||!hash_equals($_SESSION['identity_csrf'],$token)){http_response_code(419);exit('Solicitud inválida.');}
            try {
                (new PortalIdentity($db))->link($_ENV['SSO_ISSUER'],trim((string)($_POST['subject']??'')),(int)($_POST['local_id']??0));
                $message='Identidad guardada. El rol de Agroflorsa se conserva.';
            }catch(\Throwable $e){http_response_code(422);$message='No se pudo guardar. Comprueba los IDs y que ninguno tenga otra identidad asociada.';}
        }
        $users=$db->query('SELECT id,nombre,usuario,rol,portal_subject FROM usuarios ORDER BY nombre')->fetchAll();
        $router->render('usuarios/identidad',['titulo'=>'Identidades de Login','users'=>$users,'message'=>$message]);
    }
}
