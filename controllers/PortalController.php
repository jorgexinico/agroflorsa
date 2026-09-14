<?php
namespace Controllers;
use Classes\SsoClient;

final class PortalController
{
    public static function start(): void { SsoClient::start(); }
    public static function activity():void {
        header('Cache-Control: no-store');
        $token=$_SERVER['HTTP_X_ACTIVITY_TOKEN']??'';
        if(!isset($_SESSION['usuario_id'],$_SESSION['activity_token']) || !is_string($token) || !hash_equals($_SESSION['activity_token'],$token)){http_response_code(403);return;}
        try {
            SsoClient::renew();
            http_response_code(($_SESSION['portal_expires']??0)>time()?204:401);
        }catch(\Throwable $e){http_response_code(503);}
    }

    public static function callback(): void
    {
        global $db;
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');
        $stage='canje_y_jwt';
        try {
            $claims=SsoClient::consume();
            $stage='vinculo_usuario';
            $user=(new \Classes\PortalIdentity($db))->find($claims->iss,$claims->sub);
            if (!$user) throw new \RuntimeException('Identidad no vinculada.');
            // Eliminar permisos/turnos de cualquier sesión anterior.
            $_SESSION=[
                'portal_refresh'=>$_SESSION['portal_refresh']??null,'portal_checked'=>time(),
                'portal_subject'=>$claims->sub, 'portal_issuer'=>$claims->iss, 'portal_expires'=>$claims->exp,
                'usuario_id'=>(int)$user['id'], 'usuario_nombre'=>$user['nombre'],
                'usuario_rol'=>$user['rol'],
            ];
            $stage='turno_local';
            $q=$db->prepare("SELECT id,sucursal_id FROM turnos WHERE usuario_id=? AND estado='abierto' ORDER BY id DESC LIMIT 1");
            $q->execute([$user['id']]);
            if ($turno=$q->fetch()) {
                $_SESSION['turno_id']=(int)$turno['id'];
                $_SESSION['sucursal_id']=(int)$turno['sucursal_id'];
            }
            redirectTo('/dashboard');
        } catch (\Throwable $e) {
            $_SESSION=[];
            error_log('SSO Agroflorsa etapa='.$stage.' tipo='.get_class($e).' codigo='.$e->getCode());
            http_response_code(403);
            echo 'No se pudo autorizar el acceso. Vuelve al portal y selecciona Agroflorsa. Si persiste, revisa la asignación de tu usuario.';
        }
    }
}
