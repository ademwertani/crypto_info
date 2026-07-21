<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformComparison extends Model
{
    protected $fillable = [
        'platform_a_id', 'platform_b_id', 'slug',
        'verdict_html', 'meta_title', 'meta_description',
        'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function platformA(): BelongsTo
    {
        return $this->belongsTo(Platform::class, 'platform_a_id');
    }

    public function platformB(): BelongsTo
    {
        return $this->belongsTo(Platform::class, 'platform_b_id');
    }
}
