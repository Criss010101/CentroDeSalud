# Sistema de Gestión del Centro de Salud San Juan

Aplicación web para administrar las cuentas, los roles y las fichas de pacientes del Centro de Salud San Juan.

## Sprints 1 y 2 implementados

Documentación de las iteraciones: [Sprint 1](./Sprint1.md) · [Sprint 2](./Sprint2.md).

- Inicio y cierre de sesión con regeneración de sesión y límite de intentos.
- Administración de cuentas y asignación de roles: Administrador, Médico y Paciente.
- Registro público de pacientes y creación de su ficha personal.
- Edición de la ficha propia del paciente.
- Búsqueda filtrable y paginada de pacientes para médicos y administradores.
- Registro del personal médico y administrativo con cuentas y permisos separados.
- Gestión de especialidades y asignación a médicos.
- Horarios semanales recurrentes y disponibilidad en bloques de 30 minutos.
- Reserva, confirmación y cancelación de citas para pacientes y administración.
- Consulta de agenda diaria por parte del médico.

## Instalación local

Requisitos: PHP 8.4, Composer 2, Node.js/npm. Para SQL Server también se requiere Microsoft ODBC Driver y la extensión PDO_SQLSRV compatible con la versión de PHP.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Para desarrollo local, configura `.env` para SQLite:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Luego prepara la base, compila los estilos y ejecuta la aplicación:

```powershell
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

`migrate --seed` crea los roles iniciales. No hay credenciales de administrador predeterminadas. Para crear la cuenta inicial, agrega temporalmente estas variables a `.env` antes de ejecutar el seeder, usando una contraseña exclusiva de al menos 12 caracteres:

```dotenv
INITIAL_ADMIN_NAME="Administrador del centro"
INITIAL_ADMIN_EMAIL=admin@centrosalud.example
INITIAL_ADMIN_PASSWORD=una-contraseña-larga-y-única
```

Después de crear la cuenta, elimina `INITIAL_ADMIN_PASSWORD` del entorno y actualiza la caché de configuración si la utilizas.

## SQL Server y despliegue

`.env.example` configura SQL Server como motor previsto para despliegue. En el servidor define `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` fuera del repositorio. En producción configura además `APP_ENV=production`, `APP_DEBUG=false` y HTTPS.

> La arquitectura del backlog especifica Laravel 11/PHP 8.2, pero la base ya existente usa Laravel 13/PHP 8.4. El Sprint 1 conserva la versión instalada; conviene decidir expresamente la alineación de versiones antes del despliegue final.

Pruebas automatizadas:

```powershell
php artisan test --compact
```

---

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
