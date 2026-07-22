<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'coingecko' => [
        'url' => env('COINGECKO_API_URL', 'https://api.coingecko.com/api/v3'),
        'api_key' => env('COINGECKO_API_KEY'),
    ],

    'ga4' => [
        'id' => env('GA4_ID'),
    ],

    'clarity' => [
        'id' => env('CLARITY_ID'),
    ],

    'search_console' => [
        'verification' => env('SEARCH_CONSOLE_VERIFICATION'),
    ],

    'advertise' => [
        // Placeholder until the real contact@ mailbox exists — see the
        // "action manuelle semaine 3" note in the Advertise module spec.
        // `?:` (not env()'s own default) so a blank-but-present .env value
        // still falls back instead of producing a recipient-less email.
        'contact_email' => env('ADVERTISE_CONTACT_EMAIL') ?: 'contact@cryptoinfo.business',
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],

];
