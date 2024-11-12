<?php

use Shopify\Webhooks\Registry;

return [
    /*
    |--------------------------------------------------------------------------
    | Shopify App Name
    |--------------------------------------------------------------------------
    |
    | This option simply lets you display your app's name.
    |
    */

    'app_name' => env('SHOPIFY_APP_NAME', 'Shopify App'),

    /*
    |--------------------------------------------------------------------------
    | Shopify API Version
    |--------------------------------------------------------------------------
    |
    | This option is for the app's API version string.
    | Use "YYYY-MM" or "unstable". Refer to Shopify's documentation
    | on API versioning for the current stable version.
    |
    */

    'api_version' => env('SHOPIFY_API_VERSION', '2024-10'),

    /*
    |--------------------------------------------------------------------------
    | Shopify API Key
    |--------------------------------------------------------------------------
    |
    | This option is for the app's API key.
    |
    */

    'api_key' => env('SHOPIFY_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Shopify API Secret
    |--------------------------------------------------------------------------
    |
    | This option is for the app's API secret.
    |
    */

    'api_secret' => env('SHOPIFY_API_SECRET', ''),

    /*
     |--------------------------------------------------------------------------
     | Shopify API Scopes
     |--------------------------------------------------------------------------
     | This option is for the app's API scopes.
     */
    'api_scopes' => env('SHOPIFY_API_SCOPES', implode(',', [
        'read_locales',
        'read_orders',
        'read_products',
        'read_themes',
    ])),

    /*
     |--------------------------------------------------------------------------
     | After Authenticate Job
     |--------------------------------------------------------------------------
     */
    'after_authenticate_jobs' => [
        [
            'class' => \App\Jobs\UpdateUserProfile::class,
            'inline' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Queues
    |--------------------------------------------------------------------------
    |
    | This option is for setting a specific job queue for webhooks, scripttags
    | and after_authenticate_job.
    |
    */

    'job_queues' => [
        'webhooks'           => env('WEBHOOKS_JOB_QUEUE', null),
        'after_authenticate' => env('AFTER_AUTHENTICATE_JOB_QUEUE', null),
    ],

    /*
     |--------------------------------------------------------------------------
     | GraphQL Max Tries
     |--------------------------------------------------------------------------
     */
    'graphql_max_tries' => 3,

    /*
     |--------------------------------------------------------------------------
     | Webhook setup
     |--------------------------------------------------------------------------
     */
    'webhook_event_bridge_secret' => env('SHOPIFY_WEBHOOK_EVENT_BRIDGE_SECRET', ''),
];
