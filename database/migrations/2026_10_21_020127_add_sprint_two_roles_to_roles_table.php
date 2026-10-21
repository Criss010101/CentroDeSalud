<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ([
            ['code' => 'nurse', 'name' => 'Enfermería'],
            ['code' => 'staff', 'name' => 'Personal administrativo'],
        ] as $role) {
            DB::table('roles')->updateOrInsert(
                ['code' => $role['code']],
                ['name' => $role['name'], 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')
            ->whereIn('code', ['nurse', 'staff'])
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('users')
                ->whereColumn('users.role_id', 'roles.id'))
            ->delete();
    }
};
