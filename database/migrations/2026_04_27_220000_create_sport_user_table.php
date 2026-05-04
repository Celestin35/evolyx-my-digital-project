<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_user', function (Blueprint $table) {
            $table->foreignId('sport_id')
                ->constrained('sports')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->primary(['sport_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_user');
    }
};

