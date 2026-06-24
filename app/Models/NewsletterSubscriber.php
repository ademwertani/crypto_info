<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'name', 'confirmed', 'token', 'confirmed_at'];

    protected $casts = [
        'confirmed'    => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    public static function subscribe(string $email, ?string $name = null): self
    {
        return self::firstOrCreate(
            ['email' => strtolower(trim($email))],
            ['name' => $name, 'token' => Str::random(40)]
        );
    }
}
