<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 100);
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_logs');
    }
};