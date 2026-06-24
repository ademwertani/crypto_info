<?php

namespace App\Providers;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No bindings to register — services are auto-discovered
    }

    public function boot(): void
    {
        // Prevents "key too long" errors on MySQL < 8.0 with utf8mb4
        Builder::defaultStringLength(191);
    }
}
