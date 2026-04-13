<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->decimal('target_weight', 5, 2);
            $table->unsignedInteger('daily_calories');
            $table->boolean('is_active')->default(true);
            $table->date('goal_end_date')->nullable();

            $table->foreignId('macronutrient_id')
                ->constrained('macronutrients')
                ->restrictOnDelete();

            $table->foreignId('goal_type_id')
                ->constrained('goal_types')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};