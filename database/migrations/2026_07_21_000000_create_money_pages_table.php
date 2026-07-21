<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('money_pages', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique();
            $table->string('locale', 5)->default('en')->index();
            // Optional link between language variants of the same guide —
            // see App\Services\SeoService::forMoneyPage() for how this
            // drives real per-URL hreflang alternates. Null = standalone
            // page, no translation exists (the common case for mass output).
            $table->string('translation_group')->nullable()->index();

            $table->enum('type', [
                'buy_asset', 'best_list', 'exchange_review',
                'wallet_review', 'how_to', 'comparison',
            ]);
            $table->string('cluster');

            $table->string('h1');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->longText('intro_html')->nullable();
            $table->longText('body_html');

            $table->json('faq')->nullable();
            $table->json('cta_config')->nullable();
            $table->json('related_coin_ids')->nullable();
            $table->json('related_page_ids')->nullable();

            $table->enum('status', ['draft', 'in_review', 'published'])->default('draft');
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('reading_time_min')->default(1);

            $table->timestamps();

            $table->index(['status', 'cluster', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('money_pages');
    }
};
