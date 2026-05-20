<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('performed_session_id')
                ->constrained('performed_sessions')
                ->cascadeOnDelete();

            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'performed_session_id']);
            $table->index(['published_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_posts');
    }
};
