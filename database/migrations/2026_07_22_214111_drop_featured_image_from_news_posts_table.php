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
            // News posts are text-only going forward — no image field, in
            // Filament or on the public site. AI-drafted posts never set
            // this; a handful of hand-seeded example posts did, but that
            // content is dropped along with the column.
            $table->dropColumn('featured_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_posts', function (Blueprint $table) {
            $table->string('featured_image')->nullable()->after('content');
        });
    }
};
