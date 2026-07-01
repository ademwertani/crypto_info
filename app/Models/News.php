<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'title', 'slug', 'summary', 'ai_summary', 'url',
        'source', 'image_url', 'coin_slugs', 'sentiment', 'published_at',
        'views_count',
    ];

    protected $casts = [
        'coin_slugs'   => 'array',
        'published_at' => 'datetime',
        'views_count'  => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
