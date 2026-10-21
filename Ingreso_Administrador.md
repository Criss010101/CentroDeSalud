# Cómo ingresar como administrador

## Estado actual

En la base de datos consultada no hay cuentas con el rol de administrador. Además, las variables `INITIAL_ADMIN_NAME`, `INITIAL_ADMIN_EMAIL` y `INITIAL_ADMIN_PASSWORD` están vacías en `.env`.

Por eso, **no existe un correo ni una contraseña predeterminados que puedas usar ahora mismo**. El archivo de migración de roles crea la estructura de roles, pero no crea por sí solo una cuenta de administrador. El seeder del proyecto permite crearla con credenciales que tú configures.

## Crear la cuenta inicial

1. Abre el archivo `.env` en la carpeta raíz del proyecto.
2. Completa estas variables con tu propio nombre, correo y una contraseña segura de al menos 12 caracteres:

```dotenv
INITIAL_ADMIN_NAME="Administrador del centro"
INITIAL_ADMIN_EMAIL=tu-correo@ejemplo.com
INITIAL_ADMIN_PASSWORD="Escribe-aqui-una-clave-unica-y-segura"
```

No uses literalmente los valores de ejemplo. La contraseña es temporalmente necesaria para crear la cuenta; no la compartas ni la guardes en este documento.

3. Abre PowerShell en la carpeta del proyecto y ejecuta:

```powershell
php artisan config:clear
php artisan db:seed --class=RoleSeeder --no-interaction
```

El seeder crea los roles de administrador, médico y paciente, y crea la cuenta inicial con el rol de administrador. La contraseña se guarda cifrada por el modelo de usuario.

4. Cuando el comando termine correctamente, elimina la línea `INITIAL_ADMIN_PASSWORD` de `.env`. También puedes borrar las líneas `INITIAL_ADMIN_NAME` y `INITIAL_ADMIN_EMAIL` si ya no necesitas conservar esos valores de configuración.
5. Si la aplicación usa caché de configuración, vuelve a ejecutar:

```powershell
php artisan config:clear
```

## Iniciar sesión

1. Inicia la aplicación localmente si aún no está en ejecución:

```powershell
php artisan serve
```

2. Abre la página de inicio de sesión en `http://127.0.0.1:8000/ingresar`.
3. Ingresa el correo que configuraste en `INITIAL_ADMIN_EMAIL` y la contraseña que elegiste en `INITIAL_ADMIN_PASSWORD`.
4. Al ingresar, deberías ver el panel y el acceso a **Gestión de usuarios y roles**.

El registro público del sitio crea cuentas de paciente, no de administrador. Para proteger el sistema, no asignes el rol de administrador a cuentas que no deban administrar usuarios.
