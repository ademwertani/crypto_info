<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_comparisons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('platform_a_id')->constrained('platforms')->cascadeOnDelete();
            $table->foreignId('platform_b_id')->constrained('platforms')->cascadeOnDelete();

            $table->string('slug')->unique();
            $table->longText('verdict_html');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            $table->enum('status', ['draft', 'published'])->default('published');
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            // The seeder always stores the lower platform id as `a`, so a
            // pair can only ever exist once regardless of comparison order.
            $table->unique(['platform_a_id', 'platform_b_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_comparisons');
    }
};
