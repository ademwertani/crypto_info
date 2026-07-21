<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique();
            $table->string('name');
            $table->enum('type', ['exchange', 'wallet']);

            $table->string('hq_country')->nullable();
            $table->boolean('requires_kyc')->default(true);
            $table->boolean('supports_cards')->default(false);
            $table->string('best_for');

            $table->json('pros');
            $table->json('cons');

            // Placeholder only — never a verified figure. See PlatformSeeder:
            // every row ships with data_verified_at = null until a human
            // confirms the real number against the platform's pricing page.
            $table->string('fee_summary')->nullable();
            $table->timestamp('data_verified_at')->nullable();

            $table->string('affiliate_url')->nullable();
            $table->enum('status', ['draft', 'published'])->default('published');

            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};
