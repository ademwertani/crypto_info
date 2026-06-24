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
        Schema::table('cryptocurrencies', function (Blueprint $table) {
            $table->unsignedBigInteger('views_count')->default(0)->after('description');
            $table->string('ai_summary')->nullable()->after('views_count');
            $table->index('views_count', 'idx_views_count');
        });
    }

    public function down(): void
    {
        Schema::table('cryptocurrencies', function (Blueprint $table) {
            $table->dropIndex('idx_views_count');
            $table->dropColumn(['views_count', 'ai_summary']);
        });
    }
};
