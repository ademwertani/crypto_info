<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Beginner Guides',
                'slug'        => 'beginner-guides',
                'description' => 'Foundational explainers for people new to cryptocurrency.',
            ],
            [
                'name'        => 'Security',
                'slug'        => 'security',
                'description' => 'Wallet safety, scam awareness and general account security.',
            ],
            [
                'name'        => 'Market Analysis',
                'slug'        => 'market-analysis',
                'description' => 'How to read prices, volume, market cap and market sentiment.',
            ],
        ];

        foreach ($categories as $category) {
            ArticleCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
