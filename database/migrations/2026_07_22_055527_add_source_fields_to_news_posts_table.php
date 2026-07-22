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
        Schema::table('news_posts', function (Blueprint $table) {
            // Set only by `news:generate` (AI-drafted from a real RSS item) —
            // null for hand-written posts. Drives the "Source: X" attribution
            // link on the public page and dedup against re-fetching the same
            // article.
            $table->string('source_url')->nullable()->after('meta_description');
            $table->string('source_name')->nullable()->after('source_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_posts', function (Blueprint $table) {
            $table->dropColumn(['source_url', 'source_name']);
        });
    }
};
