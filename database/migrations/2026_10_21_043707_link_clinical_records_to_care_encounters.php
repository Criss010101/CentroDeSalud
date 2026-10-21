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
        Schema::table('nursing_intakes', function (Blueprint $table): void {
            $table->dropUnique(['appointment_id']);
            $table->foreignId('care_encounter_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->foreignId('patient_profile_id')->nullable()->after('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->change();
            $table->foreignId('patient_id')->nullable()->change();
            $table->index('appointment_id');
            $table->index(['patient_profile_id', 'created_at']);
        });

        Schema::table('medical_consultations', function (Blueprint $table): void {
            $table->dropUnique(['appointment_id']);
            $table->foreignId('care_encounter_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->foreignId('patient_profile_id')->nullable()->after('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->change();
            $table->foreignId('patient_id')->nullable()->change();
            $table->index('appointment_id');
            $table->index(['patient_profile_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            DB::table('nursing_intakes')->whereNotNull('care_encounter_id')->exists()
            || DB::table('medical_consultations')->whereNotNull('care_encounter_id')->exists()
            || DB::table('nursing_intakes')->whereNull('appointment_id')->exists()
            || DB::table('medical_consultations')->whereNull('appointment_id')->exists()
        ) {
            throw new RuntimeException('No se pueden desvincular los registros clínicos: hay datos asociados a atenciones nuevas.');
        }

        Schema::table('nursing_intakes', function (Blueprint $table): void {
            $table->dropIndex(['appointment_id']);
            $table->dropIndex(['patient_profile_id', 'created_at']);
            $table->dropForeign(['care_encounter_id']);
            $table->dropForeign(['patient_profile_id']);
            $table->dropColumn(['care_encounter_id', 'patient_profile_id']);
            $table->foreignId('appointment_id')->nullable(false)->change();
            $table->foreignId('patient_id')->nullable(false)->change();
            $table->unique('appointment_id');
        });

        Schema::table('medical_consultations', function (Blueprint $table): void {
            $table->dropIndex(['appointment_id']);
            $table->dropIndex(['patient_profile_id', 'created_at']);
            $table->dropForeign(['care_encounter_id']);
            $table->dropForeign(['patient_profile_id']);
            $table->dropColumn(['care_encounter_id', 'patient_profile_id']);
            $table->foreignId('appointment_id')->nullable(false)->change();
            $table->foreignId('patient_id')->nullable(false)->change();
            $table->unique('appointment_id');
        });
    }
};
