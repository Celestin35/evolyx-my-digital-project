<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performed_sessions', function (Blueprint $table) {
            $table->dateTime('completed_at')->nullable()->after('performed_at');
            $table->index(['user_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('performed_sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'completed_at']);
            $table->dropColumn('completed_at');
        });
    }
};
