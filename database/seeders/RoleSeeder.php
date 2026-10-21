<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'admin', 'name' => 'Administrador'],
            ['code' => 'doctor', 'name' => 'Médico'],
            ['code' => 'nurse', 'name' => 'Enfermería'],
            ['code' => 'receptionist', 'name' => 'Recepción'],
            ['code' => 'staff', 'name' => 'Personal administrativo'],
            ['code' => 'patient', 'name' => 'Paciente'],
        ] as $attributes) {
            Role::query()->updateOrCreate(['code' => $attributes['code']], ['name' => $attributes['name']]);
        }

        $name = config('app.initial_admin.name');
        $email = config('app.initial_admin.email');
        $password = config('app.initial_admin.password');

        if (blank($name) && blank($email) && blank($password)) {
            return;
        }

        if (blank($name) || blank($email) || blank($password)) {
            throw new RuntimeException('Configura INITIAL_ADMIN_NAME, INITIAL_ADMIN_EMAIL e INITIAL_ADMIN_PASSWORD para crear al administrador inicial.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($name) > 255 || mb_strlen($password) < 12) {
            throw new RuntimeException('Los datos del administrador inicial no son válidos; la contraseña debe tener al menos 12 caracteres.');
        }

        $adminRole = Role::query()->where('code', 'admin')->firstOrFail();
        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser && (int) $existingUser->role_id !== (int) $adminRole->id) {
            throw new RuntimeException('El correo configurado para el administrador ya pertenece a una cuenta con otro rol.');
        }

        User::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password, 'role_id' => $adminRole->id],
        );
    }
}
