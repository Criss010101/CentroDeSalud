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
        Schema::create('medical_consultations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('doctor_user_id')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->text('observations');
            $table->text('diagnosis');
            $table->text('treatment');
            $table->text('recommendations');
            $table->timestamps();

            $table->index(['patient_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_consultations');
    }
};
