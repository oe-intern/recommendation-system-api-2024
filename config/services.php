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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],


    /*
     |--------------------------------------------------------------------------
     | Recommendation model API
     |--------------------------------------------------------------------------
     */
    'recommendation' => [
        'url' => env('SERVICE_RECOMMENDATION_URL', 'http://localhost:5001'),
        'max_retries' => env('SERVICE_RECOMMENDATION_MAX_RETRIES', 3),
        'timeout' => env('SERVICE_RECOMMENDATION_TIMEOUT', 20),
        'refresh_interval_days' => env('SERVICE_RECOMMENDATION_REFRESH_INTERVAL_DAYS', 30),
        'refresh_limit' => env('SERVICE_RECOMMENDATION_REFRESH_LIMIT', 5),
    ],
];
