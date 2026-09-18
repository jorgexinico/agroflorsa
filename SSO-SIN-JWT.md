Acceso de Agroflorsa sin JWT
===========================

Login recibe el código temporal, client_id y client_secret en POST /token.
Agroflorsa solicita response_format=identity. Login comprueba usuario activo,
asignación y código vigente de un solo uso antes de devolver:

    {"identity":{"iss":"https://login.agroflorsa.com","aud":"agroflorsa","sub":"3"}}

Esta respuesta solo se admite desde el canje directo con el servidor configurado,
por HTTPS con validación de certificado activa y sin seguir redirecciones. No se
acepta identidad enviada por el navegador. HTTP solo se permite si ambos hosts
configurados son localhost, 127.0.0.1 o terminan en .localhost (desarrollo).

State sigue siendo obligatorio y se consume una vez. PortalIdentity conserva
el vínculo por emisor/sujeto y los permisos y turnos locales. Después se usa
la sesión PHP local, sin JWT, claves RSA, refresh ni llamadas periódicas a Login.

Configuración conservada: SSO_ENABLED, SSO_PORTAL_URL, SSO_BACKCHANNEL_URL,
SSO_ISSUER, SSO_CLIENT_ID, SSO_CLIENT_SECRET. SSO_PUBLIC_KEY deja de ser necesaria
para Agroflorsa. No se cambian secretos ni se borran claves existentes.
Login conserva el nombre JWT_ISSUER como identificador del emisor para compatibilidad.

Despliegue coordinado: publicar PRIMERO el cambio de Login/controllers/SsoController.php;
después actualizar Agroflorsa. Login mantiene el contrato JWT anterior para clientes
que no solicitan identity, incluido Rural Center. No hay migración SQL nueva.
Agroflorsa no ejecuta firebase/php-jwt; su dependencia histórica puede permanecer
en composer.lock sin intervenir en este flujo. Los instaladores RSA antiguos no aplican.

La adaptación anterior descrita en SSO-PRODUCCION.md queda reemplazada en lo
relativo al canje JWT por este documento. El vínculo local debe estar preparado:
este cambio no crea usuarios ni vincula automáticamente cuentas.

Pruebas: php bin/test-sso-local.php y php bin/test-portal-identity.php en Agroflorsa;
php tests/sso.php en Login. No sustituyen una prueba HTTPS de producción.
