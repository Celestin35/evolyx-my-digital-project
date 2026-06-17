<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->string('display_name', 50)->nullable()->after('name');
        });

        Schema::create('metrics', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('label', 80);
            $table->string('unit', 20)->nullable();
            $table->string('value_type', 20)->default('decimal');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('exercise_metric', function (Blueprint $table) {
            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            $table->foreignId('metric_id')
                ->constrained('metrics')
                ->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->primary(['exercise_id', 'metric_id']);
        });

        Schema::create('performance_metric_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('performance_id')
                ->constrained('performances')
                ->cascadeOnDelete();

            $table->foreignId('metric_id')
                ->constrained('metrics')
                ->cascadeOnDelete();

            $table->decimal('value', 12, 2);
            $table->timestamps();

            $table->unique(['performance_id', 'metric_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_metric_values');
        Schema::dropIfExists('exercise_metric');
        Schema::dropIfExists('metrics');

        Schema::table('sports', function (Blueprint $table) {
            $table->dropColumn('display_name');
        });
    }
};
