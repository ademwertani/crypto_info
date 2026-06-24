<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'title', 'slug', 'summary', 'ai_summary', 'url',
        'source', 'image_url', 'coin_slugs', 'sentiment', 'published_at',
    ];

    protected $casts = [
        'coin_slugs'   => 'array',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
