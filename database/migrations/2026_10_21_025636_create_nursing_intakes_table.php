<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nursing_intakes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('nurse_user_id')->constrained('users')->restrictOnDelete();
            $table->string('reason', 500);
            $table->unsignedSmallInteger('blood_pressure_systolic')->nullable();
            $table->unsignedSmallInteger('blood_pressure_diastolic')->nullable();
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->decimal('temperature_celsius', 4, 1)->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->unsignedTinyInteger('oxygen_saturation')->nullable();
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->text('observations')->nullable();
            $table->timestamp('measured_at');
            $table->timestamps();

            $table->index(['patient_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nursing_intakes');
    }
};
