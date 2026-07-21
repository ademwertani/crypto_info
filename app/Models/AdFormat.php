<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdFormat extends Model
{
    protected $fillable = [
        'slug', 'name', 'description', 'specs', 'price_range', 'sort_order', 'status',
    ];

    protected $casts = [
        'specs' => 'array',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
