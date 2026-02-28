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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'nominatim' => [
        'search_url' => env('NOMINATIM_SEARCH_URL', 'https://nominatim.openstreetmap.org/search'),
        'reverse_url' => env('NOMINATIM_REVERSE_URL', 'https://nominatim.openstreetmap.org/reverse'),
        'user_agent' => env('NOMINATIM_USER_AGENT', env('APP_NAME', 'Laravel').'/1.0'),
        'language' => env('NOMINATIM_LANGUAGE', 'en'),
    ],

    'maps' => [
        'style_url' => env('MAP_STYLE_URL', 'https://tiles.openfreemap.org/styles/liberty'),
        'fallback_tile_url' => env('MAP_FALLBACK_TILE_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'fallback_tile_attribution' => env('MAP_FALLBACK_TILE_ATTRIBUTION', '&copy; OpenStreetMap contributors'),
        'default_lat' => (float) env('MAP_DEFAULT_LAT', 27.7172),
        'default_lng' => (float) env('MAP_DEFAULT_LNG', 85.3240),
        'default_zoom' => (int) env('MAP_DEFAULT_ZOOM', 12),
    ],

];
