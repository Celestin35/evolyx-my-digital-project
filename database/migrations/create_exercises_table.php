<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();

            $table->foreignId('exercise_category_id')
                ->constrained('exercise_categories')
                ->restrictOnDelete();

            $table->foreignId('sport_id')
                ->constrained('sports')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('name');
            $table->index(['sport_id', 'exercise_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};