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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->text('ai_summary')->nullable();
            $table->string('url');
            $table->string('source')->nullable();
            $table->string('image_url')->nullable();
            $table->json('coin_slugs')->nullable();
            $table->string('sentiment')->default('neutral'); // positive | neutral | negative
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index('published_at');
            $table->index('sentiment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
