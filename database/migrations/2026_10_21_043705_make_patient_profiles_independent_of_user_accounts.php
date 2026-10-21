<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patient_profiles', function (Blueprint $table): void {
            $table->dropUnique(['user_id']);
            $table->dropForeign(['user_id']);
            $table->string('name')->nullable()->after('user_id');
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('user_id');
        });

        DB::table('patient_profiles')
            ->select('id', 'user_id')
            ->orderBy('id')
            ->chunkById(100, function ($profiles): void {
                foreach ($profiles as $profile) {
                    $name = DB::table('users')->where('id', $profile->user_id)->value('name');

                    if ($name !== null) {
                        DB::table('patient_profiles')->where('id', $profile->id)->update(['name' => $name]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('patient_profiles')->whereNull('user_id')->exists()) {
            throw new RuntimeException('No se pueden volver a vincular las fichas: existen pacientes sin cuenta de portal.');
        }

        Schema::table('patient_profiles', function (Blueprint $table): void {
            $table->dropIndex(['user_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('name');
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique('user_id');
        });
    }
};
