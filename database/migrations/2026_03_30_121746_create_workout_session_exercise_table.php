<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_session_exercise', function (Blueprint $table) {
            $table->foreignId('workout_session_id')
                ->constrained('workout_sessions')
                ->cascadeOnDelete();

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            $table->decimal('rest_time', 8, 2)->nullable();
            $table->unsignedInteger('position');

            $table->primary(['workout_session_id', 'exercise_id']);
            $table->unique(['workout_session_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_session_exercise');
    }
};