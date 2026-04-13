<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performed_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('workout_session_id')
                ->constrained('workout_sessions')
                ->cascadeOnDelete();
            $table->dateTime('performed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'performed_at']);
            $table->index(['workout_session_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performed_sessions');
    }
};
