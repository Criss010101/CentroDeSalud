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
        Schema::create('nursing_intake_amendments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('nursing_intake_id')->constrained()->restrictOnDelete();
            $table->foreignId('author_user_id')->constrained('users')->restrictOnDelete();
            $table->string('reason', 500);
            $table->json('data');
            $table->timestamps();

            $table->index(['nursing_intake_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nursing_intake_amendments');
    }
};
