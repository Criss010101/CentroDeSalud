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
        Schema::create('care_encounters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('doctor_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('received_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('source', 20);
            $table->string('status', 32)->default('waiting');
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['doctor_user_id', 'status', 'received_at']);
            $table->index(['patient_profile_id', 'created_at']);
            $table->index('appointment_id');
        });

        Schema::create('care_encounter_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('care_encounter_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('author_user_id')->constrained('users')->restrictOnDelete();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['care_encounter_id', 'created_at']);
            $table->index(['appointment_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_encounter_events');
        Schema::dropIfExists('care_encounters');
    }
};
