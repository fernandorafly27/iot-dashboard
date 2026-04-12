<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Columns already created in 2026_04_02_064143 - no-op
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeignIdFor('users');
            $table->dropColumn(['name', 'description', 'code', 'category', 'duration', 'status', 'user_id']);
        });
    }
};
