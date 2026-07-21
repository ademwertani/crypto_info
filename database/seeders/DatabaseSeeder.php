<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Blog content — see ArticleSeeder for the "example content" disclaimer.
        $this->call(ArticleCategorySeeder::class);
        $this->call(ArticleSeeder::class);

        // News content — see NewsPostSeeder for the "example content" disclaimer.
        $this->call(NewsPostSeeder::class);

        // Platform comparisons — PlatformComparisonSeeder calls PlatformSeeder
        // itself too, so it stays safe to run standalone.
        $this->call(PlatformSeeder::class);
        $this->call(PlatformComparisonSeeder::class);

        // Ad formats — provisional prices, editable in Filament without touching code.
        $this->call(AdFormatSeeder::class);
    }
}
