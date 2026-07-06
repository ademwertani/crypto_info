<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    protected $fillable = [
        'article_category_id', 'title', 'slug', 'excerpt', 'sections',
        'cover_image_url', 'meta_title', 'meta_description', 'related_coin_slugs',
        'author_name', 'status', 'published_at',
    ];

    protected $casts = [
        'sections'            => 'array',
        'related_coin_slugs'  => 'array',
        'published_at'        => 'datetime',
        'views_count'         => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }
}
