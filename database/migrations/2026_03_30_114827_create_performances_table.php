<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performances', function (Blueprint $table) {
            $table->id();
            $table->dateTime('performed_at');
            $table->decimal('weight', 5, 2)->nullable();
            $table->unsignedInteger('repetitions')->nullable();
            $table->decimal('duration_minutes', 8, 2)->nullable();
            $table->decimal('distance_meters', 8, 2)->nullable();

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->index(['user_id', 'performed_at']);
            $table->index(['exercise_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performances');
    }
};