Agroflorsa: SSO de ingreso y sesión local
======================================

GET /sso/start crea state; Login devuelve code y state a GET /sso. Agroflorsa
consume state, hace POST /token y valida JWT RS256, firma, emisor, audiencia,
sujeto y tiempos. Acepta access_token sin refresh_token. Solo valida el JWT
al ingresar y no lo guarda. PortalIdentity mantiene el vínculo exclusivo por
portal_issuer + portal_subject y exige usuario local activo.

El callback regenera la sesión, conserva ID y rol locales y recupera el último
turno abierto con su sucursal. Las rutas posteriores consultan exclusivamente
la sesión PHP y usuarios de MySQL local: usuario activo y rol actual. No consultan
Login ni dependen del vencimiento del JWT. GET sin sesión va a /sso/start;
otros métodos sin sesión reciben 401; usuario inexistente/inactivo recibe 403.

Con SSO_ENABLED=false funciona el login local incluso con PORTAL_URL definido.
Con SSO_ENABLED=true /login inicia SSO. Logout elimina sesión y cookie locales;
puede redirigir al portal sin cerrar la sesión de Login.

Configuración existente que se conserva (no modificar secretos ni claves):

SSO_ENABLED=true
SSO_PORTAL_URL=https://login.agroflorsa.com
SSO_BACKCHANNEL_URL=https://login.agroflorsa.com
SSO_ISSUER=https://login.agroflorsa.com
SSO_CLIENT_ID=agroflorsa
SSO_CLIENT_SECRET=[REDACTED]
SSO_PUBLIC_KEY=/var/www/agroflorsa/storage/portal-public.pem
SESSION_SECURE=true
APP_NAME=

Login debe abrir https://agroflorsa.com/sso/start y registrar el callback
https://agroflorsa.com/sso. Ambos pasos deben usar el mismo dominio canónico.

Obsoleto: SsoClient::renew(), PortalController::activity(), /sso/activity y
public/js/session-activity.js. Se limpian los datos de sesión portal_refresh,
portal_refresh_token, portal_expires, portal_exp, portal_checked,
portal_last_check y activity_token, incluso de sesiones anteriores.
No son variables .env necesarias. No se leyó ni modificó el .env real para
inventariar variables privadas. Cualquier ajuste exclusivo de refresh ya no es
necesario. PORTAL_URL deja de intervenir en el login local.

NO ejecutar bin/setup-production-sso.php para este contrato: es el instalador
antiguo que modifica también Login, claves y secretos y prepara renovación.
No se ejecutaron instaladores ni migraciones. La adaptación no requiere tablas
nuevas y conserva portal_issuer y portal_subject. El esquema de producción no
se ha consultado. database/sso_identity_unique_review.sql permite revisar
los duplicados y crear condicionalmente el índice único si falta. No ejecutarlo
sin revisar: requiere las columnas existentes y ausencia de duplicados.

Pruebas aisladas, sin .env, MySQL real ni red y con claves efímeras de prueba:

php bin/test-portal-identity.php
php bin/test-sso-local.php
php bin/test-sso-local.php start
php bin/test-sso-local.php get
php bin/test-sso-local.php post
php bin/test-sso-local.php local
php bin/test-sso-local.php inactive
php bin/test-sso-local.php missing
php bin/test-sso-local.php logout

Compatibilidades aceptadas:
- Retirar acceso en Login no invalida sesiones locales ya abiertas.
- La duración de sesión depende de PHP: cookie de sesión y almacenamiento
  existente; no se introduce timeout propio ni se utiliza exp del JWT.
- Se aceptan sesiones locales anteriores con usuario activo sin exigir
  portal_subject para navegar; los datos de renovación anteriores se limpian.
- Pestañas antiguas pueden conservar el script de actividad hasta recargarse;
  la ruta retirada no renueva ni contacta Login.

Pendiente: recorrido HTTPS real con Login simplificado y cookies del navegador.
Las pruebas aisladas no sustituyen la validación de producción. No se hizo
ningún deploy, push ni cambio en Login, bases, secretos o claves reales.