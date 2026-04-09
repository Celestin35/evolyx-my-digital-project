<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performances', function (Blueprint $table) {
            $table->foreignId('performed_session_id')
                ->nullable()
                ->after('exercise_id')
                ->constrained('performed_sessions')
                ->nullOnDelete();

            $table->index(['performed_session_id', 'performed_at']);
            $table->unique(['performed_session_id', 'exercise_id']);
        });
    }

    public function down(): void
    {
        Schema::table('performances', function (Blueprint $table) {
            $table->dropUnique(['performed_session_id', 'exercise_id']);
            $table->dropIndex(['performed_session_id', 'performed_at']);
            $table->dropConstrainedForeignId('performed_session_id');
        });
    }
};
