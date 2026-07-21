<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Platform extends Model
{
    protected $fillable = [
        'slug', 'name', 'type',
        'hq_country', 'requires_kyc', 'supports_cards', 'best_for',
        'pros', 'cons',
        'fee_summary', 'data_verified_at',
        'affiliate_url', 'status',
    ];

    protected $casts = [
        'pros' => 'array',
        'cons' => 'array',
        'requires_kyc' => 'boolean',
        'supports_cards' => 'boolean',
        'data_verified_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function comparisonsAsA(): HasMany
    {
        return $this->hasMany(PlatformComparison::class, 'platform_a_id');
    }

    public function comparisonsAsB(): HasMany
    {
        return $this->hasMany(PlatformComparison::class, 'platform_b_id');
    }

    /** True once a human has confirmed fee_summary against the platform's real pricing page. */
    public function isFeeVerified(): bool
    {
        return $this->data_verified_at !== null;
    }
}
