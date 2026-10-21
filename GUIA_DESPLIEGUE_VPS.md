# Guía para subir Centro de Salud San Juan a un VPS

Esta guía explica cómo publicar este proyecto Laravel junto con Moodle en el VPS existente con **Ubuntu 26.04**, **Apache** y **MariaDB**, sin sustituir ni desactivar los servicios o el sitio de Moodle. Se recomienda usar un subdominio propio para Laravel, por ejemplo `salud.midominio.com`, y una base de datos independiente. Se usa **PuTTY** para ejecutar comandos y **WinSCP** para transferir archivos por SFTP.

> Los comandos de instalación están escritos para Ubuntu 26.04 (`resolute`). Si tu VPS usa Debian, AlmaLinux, Windows Server u otra versión de Ubuntu, algunos comandos cambian. No ejecutes comandos de otra distribución sin adaptarlos.

> **Este VPS ya aloja Moodle.** Antes de cambiar la configuración, crea una copia de seguridad/snapshot desde el panel del proveedor. No instales MySQL, no reemplaces MariaDB, no desactives el sitio Apache existente y no borres datos ni archivos de Moodle. La instalación de MySQL puede quitar MariaDB.

## 1. Qué necesitas antes de empezar

Ten a mano:

- La dirección IP pública del VPS.
- El puerto SSH (normalmente `22`), el usuario y la contraseña o llave privada entregados por el proveedor.
- Acceso al panel del proveedor para abrir puertos y, si corresponde, configurar DNS.
- Un dominio, si quieres una dirección como `salud.midominio.com` y HTTPS. También se puede probar primero con la IP.
- PuTTY y WinSCP instalados en Windows.
- Los archivos del proyecto. No compartas por chat ni subas a Git la contraseña de producción, el archivo `.env` ni llaves privadas.

**Importante:** PuTTY abre una terminal SSH; no transfiere archivos. WinSCP usa SFTP sobre SSH y sirve para subirlos. Si el proyecto ya está en un repositorio Git accesible desde el VPS, puedes clonarlo en vez de usar WinSCP.

## 2. Conectarte con PuTTY

1. Abre PuTTY.
2. En **Host Name**, escribe la IP del VPS.
3. En **Port**, escribe el puerto SSH del proveedor; deja `22` si no te indicaron otro.
4. Selecciona **SSH** y pulsa **Open**.
5. Acepta la huella del servidor si es la primera conexión y escribe el usuario y la contraseña. Al escribir una contraseña en la terminal no aparecen caracteres; es normal.

Una vez dentro, verifica el sistema:

```bash
cat /etc/os-release
```

Los comandos siguientes son para Ubuntu 26.04. Si accediste como `root`, crea un usuario de despliegue y úsalo para el resto del procedimiento:

```bash
adduser deploy
usermod -aG sudo deploy
```

Cierra la sesión con `exit` y vuelve a conectarte por PuTTY como `deploy`. Si el proveedor ya te dio un usuario normal con permiso `sudo`, puedes usar ese usuario en lugar de crear `deploy`.

## 3. Preparar el VPS existente

Este VPS ya tiene Apache, UFW y las reglas para SSH y HTTP/HTTPS. Compruébalas; no hace falta activar el firewall ni instalar otro servidor web:

```bash
sudo apt update
sudo ufw status
```

En este servidor en producción, no ejecutes `apt upgrade` sin una ventana de mantenimiento y una copia de seguridad. No detengas ni reemplaces Apache.

Si preparas otro VPS nuevo y usarás UFW, permite SSH y Apache en el firewall del proveedor y en UFW antes de activarlo para no perder el acceso.

## 4. Comprobar PHP e instalar lo que falte

El proyecto utiliza Laravel 13 y requiere PHP 8.3 o superior. Antes de cambiar PHP, comprueba cuál usa el Moodle existente y qué módulos de PHP tiene Apache:

```bash
php -v
sudo apache2ctl -M | grep -E 'php|proxy_fcgi'
ls /run/php/
```

**No cambies ni desactives el controlador PHP actual de Apache/Moodle.** Si PHP ya está instalado y es 8.3 o superior, conserva esa versión y confirma que tiene las extensiones `mysql`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `intl` y `gd`. Si falta alguna, instala solo la extensión correspondiente para la versión ya instalada. Si no hay PHP 8.3 o superior, detente antes de instalar: primero verifica la compatibilidad de la versión de Moodle y prepara la configuración para que cada VirtualHost use una versión compatible.

Ubuntu incluye su versión de PHP en sus propios repositorios; **no agregues el PPA `ondrej/php`**, que no publica paquetes para Ubuntu 26.04. Solo si verificaste que instalar PHP-FPM y estas extensiones no cambiará la versión/controlador que usa Moodle, instala:

```bash
sudo apt update
sudo apt install -y php-cli php-fpm php-mysql php-mbstring php-xml php-curl php-zip php-bcmath php-intl php-gd
```

Instala Composer desde su paquete de Ubuntu:

```bash
sudo apt install -y composer
```

Para compilar los recursos del frontend, instala Node.js 22:

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
```

Comprueba que los programas están disponibles:

```bash
php -v
composer --version
node --version
npm --version
```

Confirma que PHP sea 8.3 o superior y anota su versión principal y secundaria para configurar Laravel sin cambiar el manejador de PHP de Moodle.

## 5. Crear una base de datos independiente en MariaDB

Laravel usa el controlador `mysql` para conectarse tanto a MySQL como a MariaDB. Moodle debe continuar usando su base de datos actual; crea una base de datos nueva, con usuario propio, para Laravel. No instales MySQL ni ejecutes `apt remove`, porque puede sustituir o quitar MariaDB. Comprueba que MariaDB esté activa y abre el cliente:

```bash
sudo systemctl status mariadb --no-pager
sudo mariadb
```

En el indicador de MariaDB, crea una base y un usuario exclusivos. Reemplaza `CAMBIA_ESTA_CLAVE_LARGA` por una contraseña larga, aleatoria y distinta de las credenciales de Moodle:

```sql
CREATE DATABASE centro_de_salud_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'centro_app'@'127.0.0.1' IDENTIFIED BY 'CAMBIA_ESTA_CLAVE_LARGA';
GRANT ALL PRIVILEGES ON centro_de_salud_db.* TO 'centro_app'@'127.0.0.1';
FLUSH PRIVILEGES;
QUIT;
```

Guarda la contraseña en un gestor de contraseñas. No uses una cuenta de Moodle ni la cuenta administrativa de MariaDB en el `.env`.

## 6. Crear la carpeta y subir los archivos

En PuTTY, crea la carpeta de la aplicación y asigna el acceso al usuario de despliegue:

```bash
sudo mkdir -p /var/www/centro-salud
sudo chown deploy:www-data /var/www/centro-salud
```

Si tu usuario no se llama `deploy`, reemplaza ese nombre en todos los comandos de permisos de esta guía. Sube Laravel a esta carpeta separada; no sobrescribas ni muevas archivos de Moodle.

### Opción A: subir con WinSCP

1. Abre WinSCP y crea una conexión nueva con protocolo **SFTP**.
2. Usa la misma IP, puerto, usuario y método de autenticación que con PuTTY.
3. En el panel local, abre la carpeta del proyecto en Windows.
4. En el panel remoto, abre `/var/www/centro-salud`.
5. Sube el contenido del proyecto, incluidos los archivos ocultos del proyecto como `.env.example`, `composer.json`, `composer.lock`, `package.json` y `package-lock.json`.
6. **No subas** `.env`, `vendor`, `node_modules` ni contraseñas/llaves privadas. Las dependencias se instalarán en el VPS. No hace falta subir `database/database.sqlite`, porque esta guía configura MariaDB.

### Opción B: clonar desde Git

Si ya tienes el proyecto en un repositorio Git accesible desde el VPS, clónalo directamente en la carpeta de despliegue. Si el repositorio es privado, configura el acceso de Git con una llave de solo lectura y no pegues tokens o llaves en la terminal ni en esta guía.

```bash
cd /var/www/centro-salud
git clone URL_DE_TU_REPOSITORIO .
```

No ejecutes esta opción después de haber subido los archivos con WinSCP a esa misma carpeta.

## 7. Configurar Laravel para producción

En PuTTY, entra a la carpeta de la aplicación e instala las dependencias de PHP y JavaScript:

```bash
cd /var/www/centro-salud
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Crea el archivo de entorno local del servidor y edítalo:

```bash
cp .env.example .env
nano .env
```

En `.env`, deja estos valores para producción y reemplaza el subdominio y la contraseña por los tuyos. Utiliza la base de datos dedicada de Laravel, no la de Moodle:

```dotenv
APP_NAME="Centro de Salud San Juan"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://TU_SUBDOMINIO

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=centro_de_salud_db
DB_USERNAME=centro_app
DB_PASSWORD="LA_CLAVE_LARGA_QUE_CONFIGURASTE"

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

INITIAL_ADMIN_NAME="Administrador"
INITIAL_ADMIN_EMAIL="admin@TU_SUBDOMINIO"
INITIAL_ADMIN_PASSWORD="UNA_CLAVE_DE_12_CARACTERES_O_MAS"
```

Configura un subdominio exclusivo para Laravel (por ejemplo, `salud.midominio.com`) antes de continuar con Apache y HTTPS. No uses la dirección ni las rutas del Moodle para la aplicación Laravel.

Guarda en Nano con `Ctrl+O`, `Enter`, y sal con `Ctrl+X`.

Genera la clave de Laravel, aplica las migraciones y crea los roles y el administrador inicial:

```bash
php artisan key:generate
php artisan migrate --force --seed
```

El seeder de este proyecto crea el administrador inicial con las variables `INITIAL_ADMIN_*`. La contraseña debe tener al menos 12 caracteres. **Después de confirmar que el usuario administrador se creó**, vuelve a editar `.env` y elimina las tres variables `INITIAL_ADMIN_NAME`, `INITIAL_ADMIN_EMAIL` y `INITIAL_ADMIN_PASSWORD`; no dejes la contraseña inicial guardada en el servidor. No vuelvas a ejecutar el seeder con credenciales temporales si no quieres crear otra cuenta inicial.

## 8. Ajustar permisos de Laravel

Apache y PHP-FPM deben poder leer el proyecto, y PHP-FPM debe poder escribir en `storage` y `bootstrap/cache`:

```bash
sudo chown -R deploy:www-data /var/www/centro-salud
sudo find /var/www/centro-salud -type d -exec chmod 755 {} \;
sudo find /var/www/centro-salud -type f -exec chmod 644 {} \;
sudo find /var/www/centro-salud/storage /var/www/centro-salud/bootstrap/cache -type d -exec chmod 775 {} \;
sudo find /var/www/centro-salud/storage /var/www/centro-salud/bootstrap/cache -type f -exec chmod 664 {} \;
sudo chmod 640 /var/www/centro-salud/.env
```

Si el usuario de despliegue no es `deploy`, reemplázalo en el comando `chown`. No uses `chmod -R 777`.

## 9. Configurar Apache

Apache ya estaba ejecutándose en este VPS y sirve Moodle. **No intentes iniciar Nginx, detener Apache ni reemplazar el VirtualHost actual de Moodle.** Asigna a Laravel un subdominio separado, por ejemplo `salud.midominio.com`, y crea un registro DNS `A` para ese subdominio apuntando a la IP del VPS. La propagación puede tardar. No uses el dominio ni una ruta de Moodle como `ServerName`.

Si Nginx quedó instalado por un intento anterior, no lo uses ni lo inicies; Apache atenderá ambos sitios.

Activa los módulos proxy requeridos para que **solo el VirtualHost de Laravel** use PHP-FPM. Reemplaza `phpVERSION` con la versión que comprobaste en el paso 4 (por ejemplo, `8.5`). No habilites una configuración global `phpVERSION-fpm`, ya que podría cambiar el manejador de PHP que usa Moodle.

```bash
sudo a2enmod rewrite proxy_fcgi setenvif
sudo systemctl enable --now phpVERSION-fpm
```

Crea el sitio:

```bash
sudo nano /etc/apache2/sites-available/centro-salud.conf
```

Pega esta configuración. Reemplaza `TU_SUBDOMINIO` por el subdominio dedicado a Laravel, por ejemplo `salud.midominio.com`. No uses el dominio de Moodle ni elimines su configuración.

```apache
<VirtualHost *:80>
   ServerName TU_SUBDOMINIO
   DocumentRoot /var/www/centro-salud/public

   <Directory /var/www/centro-salud/public>
       AllowOverride All
       Require all granted
       Options FollowSymLinks
   </Directory>

   <FilesMatch \.php$>
       SetHandler "proxy:unix:/run/php/phpVERSION-fpm.sock|fcgi://localhost"
   </FilesMatch>

   ErrorLog ${APACHE_LOG_DIR}/centro-salud-error.log
   CustomLog ${APACHE_LOG_DIR}/centro-salud-access.log combined
</VirtualHost>
```

Guarda y sal de Nano. Habilita el nuevo sitio, comprueba la configuración y recarga Apache. Esto añade el sitio de Laravel sin deshabilitar el de Moodle:

```bash
sudo a2ensite centro-salud.conf
sudo apache2ctl configtest
sudo systemctl enable --now apache2
sudo systemctl reload apache2
```

**No deshabilites `000-default.conf` ni ningún otro sitio existente:** Moodle ya está alojado en este servidor. Si `apache2ctl configtest` no muestra `Syntax OK`, no recargues Apache; corrige el error primero.

## 10. Activar HTTPS con Let's Encrypt

Este paso requiere que el dominio ya resuelva a la IP del VPS y que los puertos `80` y `443` estén permitidos tanto en UFW como en el firewall del proveedor.

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d TU_SUBDOMINIO
```

Reemplaza `TU_SUBDOMINIO` por el subdominio de Laravel y sigue las instrucciones para indicar un correo y aceptar las condiciones. Comprueba que la renovación automática funciona:

```bash
sudo certbot renew --dry-run
```

Después de activar HTTPS, confirma que `APP_URL` en `.env` es `https://TU_SUBDOMINIO` y limpia/genera la caché de configuración como se indica en el paso siguiente.

## 11. Optimizar y comprobar la aplicación

Después de editar `.env`, limpia y reconstruye las cachés:

```bash
cd /var/www/centro-salud
php artisan optimize:clear
php artisan optimize
```

Comprueba servicios y permisos:

```bash
sudo systemctl status apache2 phpVERSION-fpm mariadb --no-pager
php artisan about
```

Abre `https://TU_SUBDOMINIO`. La página inicial de este proyecto redirige al formulario `/ingresar`. Comprueba también que Moodle sigue abriendo en su dirección habitual. Si alguna aplicación deja de responder, no deshabilites sitios ni reinicies la base de datos; revisa primero la configuración y los registros.

Si algo falla, consulta los registros:

```bash
sudo tail -n 100 /var/log/apache2/centro-salud-error.log
sudo tail -n 100 /var/www/centro-salud/storage/logs/laravel.log
```

No actives `APP_DEBUG=true` en un servidor público. Para verificar la ruta de salud de Laravel, también puedes abrir `/up`.

## 12. Cómo publicar futuras actualizaciones

Antes de actualizar, respalda la base de datos de Laravel. El siguiente comando pedirá la contraseña del usuario de MariaDB:

```bash
mysqldump -h 127.0.0.1 -u centro_app -p centro_de_salud_db > "$HOME/centro-de-salud-$(date +%F).sql"
```

Este respaldo es solo de la base Laravel. Mantén también las copias de seguridad habituales de Moodle.

Sube por WinSCP los archivos nuevos, sin reemplazar `.env`, o actualiza con Git si ese fue tu método. Luego ejecuta:

```bash
cd /var/www/centro-salud
php artisan down
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan up
```

Si un comando falla durante el mantenimiento, revisa el error antes de continuar y vuelve a habilitar el sitio con `php artisan up` cuando sea seguro.

## Lista rápida de verificación

- [ ] Puedo entrar al VPS por PuTTY y conservé acceso SSH tras configurar el firewall.
- [ ] Subí el proyecto a `/var/www/centro-salud`; no subí `.env`, `vendor` ni `node_modules`.
- [ ] MariaDB de Moodle sigue activa; Laravel usa una base y un usuario separados.
- [ ] `.env` tiene `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` generado y los datos correctos de la base Laravel.
- [ ] Migraciones y seeder terminaron correctamente; eliminé del `.env` las credenciales temporales del administrador.
- [ ] Apache conserva la configuración de Moodle y sirve Laravel desde `/var/www/centro-salud/public`.
- [ ] El subdominio de Laravel apunta a la IP y HTTPS se instaló correctamente.
- [ ] Moodle sigue abriendo y conectando a su base de datos después de habilitar el nuevo sitio.
- [ ] Puedo abrir `/ingresar` e iniciar sesión.
- [ ] Tengo una copia de seguridad y sé dónde revisar los registros.
