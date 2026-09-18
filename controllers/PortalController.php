<?php
namespace Controllers;
use Classes\SsoClient;

final class PortalController
{
    public static function start(): void { SsoClient::start(); }
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
            session_regenerate_id(true);
            $_SESSION=[
                'portal_subject'=>$claims->sub, 'portal_issuer'=>$claims->iss,
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
            session_regenerate_id(true);
            $_SESSION=[];
            error_log('SSO Agroflorsa etapa='.$stage.' tipo='.get_class($e).' codigo='.$e->getCode());
            http_response_code(403);
            echo 'No se pudo autorizar el acceso. Vuelve al portal y selecciona Agroflorsa. Si persiste, revisa la asignación de tu usuario.';
        }
    }
}
