Agroflorsa usa https://login.agroflorsa.com como puerta de entrada. Login autentica;
Agroflorsa conserva sus usuarios, roles, sucursales y turnos existentes.

En el servidor, con ambos repositorios actualizados y sus dependencias instaladas:

```bash
cd /var/www/agroflorsa
sudo -u www-data php bin/setup-production-sso.php --portal-user=ivan --local-user=USUARIO_AGROFLORSA
sudo -u www-data php bin/setup-production-sso.php --portal-user=ivan --local-user=USUARIO_AGROFLORSA --apply
```

Sustituye USUARIO_AGROFLORSA por el nombre de usuario existente que corresponde a
Ivan. No uses automáticamente admin: se conserva el rol del usuario elegido.
Ejecuta con una cuenta que pueda leer/escribir los .env y storage de ambos
proyectos y login/config/applications.php; PHP del servidor debe poder leer los
archivos resultantes. No hace falta compartir credenciales: se leen de los .env.

El primer comando comprueba usuarios y requisitos sin modificar nada. El segundo
prepara tablas e índices de forma aditiva, reutiliza/genera claves RSA, copia solo
la pública a Agroflorsa, registra callback y secreto, asigna acceso y vincula la
identidad. Activa SSO al final. Conserva aplicaciones y asignaciones existentes.
Respaldar las bases antes de aplicar: MySQL no revierte DDL. El comando respalda
los archivos modificados en storage/sso-backup-* y se puede volver a ejecutar;
un fallo puede dejar cambios parciales. No modifica roles ni contraseñas.

Ambos dominios deben tener HTTPS válido y servir public/. El servidor debe poder
conectar a https://login.agroflorsa.com. El callback usa el dominio canónico
https://agroflorsa.com/sso; www debe redirigir a ese dominio antes de iniciar SSO.

Validar en una ventana privada: abrir Agroflorsa, autenticarse en Login, regresar
al dashboard, comprobar rol y turno abierto, y usar la app durante más de cinco
minutos para comprobar renovación. También probar un usuario sin asignación:
debe denegarse el acceso. La preparación no sustituye esta prueba en producción.

Para otro usuario, repetir el comando con ambos nombres correspondientes. No
sobrescribe vínculos existentes a otra identidad. No ejecutar el antiguo
login/bin/setup-agroflorsa-sso.php: configura direcciones de desarrollo.
