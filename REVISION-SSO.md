# Revisión de acceso Login → Agroflorsa

Revisión local del 18 de septiembre de 2026. No constituye una validación del servidor de producción.

## Hallazgos comprobados

- La configuración local del canal de canje no cumple la política de HTTPS/localhost del cliente actual. `php bin/diagnose-sso.php --config-only` devuelve error. No se imprimen ni modifican secretos.
- `login/bin/setup-agroflorsa-sso.php` es un instalador antiguo: genera un backchannel HTTP con `host.docker.internal`, usa `SSO_USER_MAP` y prepara RSA. El contrato actual rechaza ese backchannel y consulta `usuarios.portal_issuer`/`portal_subject`, no el mapa. No debe usarse para instalar el contrato actual.
- `bin/setup-production-sso.php` también es antiguo; su documentación ya indica no ejecutarlo para el contrato sin JWT.
- Asignar la aplicación en Login no crea el vínculo con el usuario de Agroflorsa. El secreto correcto tampoco sustituye ese vínculo. El diagnóstico comprueba ambos por separado.
- La captura del incidente contiene un mensaje distinto del controlador local. Es necesario comparar los archivos desplegados y la caché de PHP antes de atribuir el incidente a una causa concreta.

## Cambios

- El cliente distingue errores de transporte, `invalid_client` y `invalid_grant`; conserva el puerto del portal en el encabezado Host y exige validación TLS explícita.
- Login rechaza parámetros de canje que sean arrays o tengan formato inválido antes de consultar la base. Los errores de conexión, consulta y emisión devuelven JSON y registran su etapa sin datos privados.
- Agroflorsa rechaza identidades locales activas duplicadas en lugar de tomar arbitrariamente la primera cuenta.
- Se incorpora `bin/diagnose-sso.php`: comprobaciones de configuración, huellas de código, secreto compartido, callback, tablas, asignaciones y vínculos. Solo ejecuta consultas SELECT.

## Comprobación en el servidor

Desde el directorio de Agroflorsa, con el mismo usuario y versión PHP que utiliza la aplicación:

```sh
php bin/diagnose-sso.php --login-dir=/ruta/real/login
```

El comando devuelve 1 si encuentra errores y 0 si pasan sus comprobaciones. La opción `--config-only` omite MySQL. No comprueba DNS, HTTPS real, permisos del proceso web si se ejecuta con otro usuario, ni OPcache. Las huellas identifican archivos en disco; no demuestran qué versión tiene cargada PHP.

Si se despliega esta versión, publicar primero Login y después Agroflorsa. Comparar las huellas de ambos controladores y recargar el servicio PHP correspondiente si mantiene código anterior en OPcache. Iniciar un acceso nuevo desde `/sso/start`; los códigos anteriores expiran y solo sirven una vez.

## Validación local

Pasaron las siete pruebas de `login/tests` incluidas en Composer y las pruebas de Agroflorsa de identidad, callback, sesión, inicio, GET/POST sin sesión, usuario inactivo/inexistente y logout. Se añadieron casos de transporte, secreto/código rechazado, puerto del portal, entradas malformadas e identidades duplicadas.

Docker no está en ejecución en este equipo. No se pudo comprobar aquí el recorrido HTTP con MySQL ni el acceso de producción. Las pruebas aisladas usan SQLite y transporte simulado; no prueban bloqueos concurrentes de MySQL.
