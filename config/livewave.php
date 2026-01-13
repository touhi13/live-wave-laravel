<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LiveWave API Key
    |--------------------------------------------------------------------------
    |
    | Your LiveWave API key for authentication. You can find this in your
    | LiveWave dashboard under API Keys.
    |
    */

    'api_key' => env('LIVEWAVE_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | LiveWave API Secret
    |--------------------------------------------------------------------------
    |
    | Your LiveWave API secret for signing requests. This is used to
    | generate and verify webhook signatures.
    |
    */

    'api_secret' => env('LIVEWAVE_API_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | LiveWave Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your LiveWave instance. For self-hosted installations,
    | this would be your server's URL.
    |
    */

    'base_url' => env('LIVEWAVE_BASE_URL', 'https://api.livewave.app'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests. Increase this if you're
    | experiencing timeout issues.
    |
    */

    'timeout' => env('LIVEWAVE_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for incoming webhooks from LiveWave.
    |
    */

    'webhooks' => [
        'tolerance' => env('LIVEWAVE_WEBHOOK_TOLERANCE', 300), // 5 minutes
    ],

];
