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
        $stage='canje_identidad';
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
            error_log('SSO Agroflorsa etapa='.$stage.' tipo='.get_class($e).' codigo='.$e->getCode()
                .' archivo='.$e->getFile().' linea='.$e->getLine());
            http_response_code(403);
            $reason=$stage==='canje_identidad' ? match ($e->getCode()) {
                1001 => 'La sesión de ingreso falta, venció o el enlace ya se utilizó.',
                1002 => 'No se pudo completar el canje con Login. Revisa el registro del servidor.',
                1003 => 'La conexión con Login requiere HTTPS.',
                1004 => 'Login devuelve un formato incompatible. Falta desplegar el soporte response_format=identity en Login.',
                1005 => 'La identidad recibida no coincide con la configuración esperada.',
                1006 => 'No se pudo conectar con Login. El administrador debe revisar la conexión y el certificado del servidor.',
                1007 => 'Login rechazó la configuración de acceso de Agroflorsa. El administrador debe revisar la aplicación y su secreto compartido.',
                1008 => 'El código de ingreso venció, ya se utilizó o el acceso fue revocado. Inicia sesión de nuevo.',
                default => 'Ocurrió un error al validar el ingreso con Login.',
            } : ($stage==='vinculo_usuario'
                ? 'No se pudo resolver el usuario local activo. Revisa su vínculo y el registro del servidor.'
                : 'No se pudo recuperar el turno local. Revisa el registro del servidor.');
            echo 'No se pudo autorizar el acceso. '.$reason.' <a href="'
                .htmlspecialchars(url('/sso/start'),ENT_QUOTES,'UTF-8').'">Volver a iniciar sesión</a>';
        }
    }
}
