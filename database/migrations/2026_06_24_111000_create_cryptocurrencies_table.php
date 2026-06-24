<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cryptocurrencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol', 20);
            $table->string('slug')->unique();
            $table->string('image_url')->nullable();

            // Pricing — decimal(30,10) handles both micro-coins and large-cap prices
            $table->decimal('current_price', 30, 10)->nullable();
            $table->decimal('market_cap', 30, 2)->nullable();
            $table->unsignedInteger('market_cap_rank')->nullable()->index();
            $table->decimal('fully_diluted_valuation', 30, 2)->nullable();
            $table->decimal('total_volume', 30, 2)->nullable();
            $table->decimal('high_24h', 30, 10)->nullable();
            $table->decimal('low_24h', 30, 10)->nullable();

            // Percentage changes
            $table->decimal('price_change_percentage_1h_in_currency', 10, 4)->nullable();
            $table->decimal('price_change_percentage_24h_in_currency', 10, 4)->nullable();
            $table->decimal('price_change_percentage_7d_in_currency', 10, 4)->nullable();

            // Supply
            $table->decimal('circulating_supply', 30, 2)->nullable();
            $table->decimal('total_supply', 30, 2)->nullable();
            $table->decimal('max_supply', 30, 2)->nullable();

            // All-time high / low
            $table->decimal('ath', 30, 10)->nullable();
            $table->decimal('ath_change_percentage', 20, 4)->nullable(); // can be very large negative (e.g. -99%)
            $table->timestamp('ath_date')->nullable();
            $table->decimal('atl', 30, 10)->nullable();
            $table->decimal('atl_change_percentage', 20, 4)->nullable(); // can exceed 100,000,000% for micro-caps
            $table->timestamp('atl_date')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cryptocurrencies');
    }
};
