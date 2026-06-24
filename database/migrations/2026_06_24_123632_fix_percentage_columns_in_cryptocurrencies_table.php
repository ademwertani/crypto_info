<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // decimal(10,4) allows max 999999.9999 — not enough for ATL % on low-cap coins
    // (e.g. Shiba Inu ATL change: ~134,000,000%). Widening to decimal(20,4).

    public function up(): void
    {
        Schema::table('cryptocurrencies', function (Blueprint $table) {
            $table->decimal('atl_change_percentage', 20, 4)->nullable()->change();
            $table->decimal('ath_change_percentage', 20, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cryptocurrencies', function (Blueprint $table) {
            $table->decimal('atl_change_percentage', 10, 4)->nullable()->change();
            $table->decimal('ath_change_percentage', 10, 4)->nullable()->change();
        });
    }
};
