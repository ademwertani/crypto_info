<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertiserLead extends Model
{
    /** Fixed budget bands offered in the public form's <select> — also the source of truth for validation. */
    public const BUDGET_RANGES = [
        '< $500',
        '$500 - $2,000',
        '$2,000 - $5,000',
        '$5,000+',
        'Not sure yet',
    ];

    protected $fillable = [
        'name', 'email', 'company', 'budget_range', 'message', 'status', 'ip',
    ];
}
