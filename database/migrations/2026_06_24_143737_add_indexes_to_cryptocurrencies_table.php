<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Phase 2 — Performance: indexes for market analytics queries
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cryptocurrencies', function (Blueprint $table) {
            $table->index('price_change_percentage_24h_in_currency', 'idx_change_24h');
            $table->index('price_change_percentage_7d_in_currency',  'idx_change_7d');
            $table->index(['market_cap_rank', 'id'], 'idx_rank_id');
            if (DB::getDriverName() !== 'sqlite') {
                $table->fullText(['name', 'symbol'], 'ft_name_symbol');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cryptocurrencies', function (Blueprint $table) {
            $table->dropIndex('idx_change_24h');
            $table->dropIndex('idx_change_7d');
            $table->dropIndex('idx_rank_id');
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropIndex('ft_name_symbol');
            }
        });
    }
};
