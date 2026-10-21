<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('roles')->updateOrInsert(
            ['code' => 'receptionist'],
            ['name' => 'Recepción', 'created_at' => now(), 'updated_at' => now()],
        );

        Schema::table('appointments', function (Blueprint $table): void {
            $table->string('booking_code', 20)->nullable();
        });

        DB::table('appointments')->select('id')->orderBy('id')->chunkById(100, function ($appointments): void {
            foreach ($appointments as $appointment) {
                do {
                    $code = 'SJ-'.Str::upper(Str::random(12));
                } while (DB::table('appointments')->where('booking_code', $code)->exists());

                DB::table('appointments')->where('id', $appointment->id)->update(['booking_code' => $code]);
            }
        });

        Schema::table('appointments', function (Blueprint $table): void {
            $table->unique('booking_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $receptionistRoleId = DB::table('roles')->where('code', 'receptionist')->value('id');

        if ($receptionistRoleId !== null && DB::table('users')->where('role_id', $receptionistRoleId)->exists()) {
            throw new RuntimeException('No se puede eliminar el rol de recepción mientras tenga cuentas asignadas.');
        }

        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropUnique(['booking_code']);
            $table->dropColumn('booking_code');
        });

        DB::table('roles')->where('code', 'receptionist')->delete();
    }
};
