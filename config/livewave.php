<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LiveWave Application ID
    |--------------------------------------------------------------------------
    |
    | Your LiveWave application ID. This identifies your app when connecting
    | to the LiveWave WebSocket server. Get this from your LiveWave dashboard.
    |
    */

    'app_id' => env('LIVEWAVE_APP_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | LiveWave Application Key
    |--------------------------------------------------------------------------
    |
    | Your LiveWave application key for client-side authentication.
    | This is the public key used by Laravel Echo to connect.
    |
    */

    'app_key' => env('LIVEWAVE_APP_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | LiveWave Application Secret
    |--------------------------------------------------------------------------
    |
    | Your LiveWave application secret for signing requests. This is used
    | to generate authentication signatures for private/presence channels.
    | Keep this secret - never expose it client-side!
    |
    */

    'app_secret' => env('LIVEWAVE_APP_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | LiveWave Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how your app connects to the LiveWave WebSocket server.
    | For self-hosted LiveWave, point this to your server's address.
    |
    */

    'server' => [
        'host' => env('LIVEWAVE_HOST', '127.0.0.1'),
        'port' => env('LIVEWAVE_PORT', 8080),
        'scheme' => env('LIVEWAVE_SCHEME', 'http'),
        'path' => env('LIVEWAVE_PATH', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | WebSocket Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the WebSocket connection (used by Laravel Echo).
    |
    */

    'websocket' => [
        'host' => env('LIVEWAVE_WS_HOST', env('LIVEWAVE_HOST', '127.0.0.1')),
        'port' => env('LIVEWAVE_WS_PORT', env('LIVEWAVE_PORT', 8080)),
        'scheme' => env('LIVEWAVE_WS_SCHEME', 'ws'),
        'encrypted' => env('LIVEWAVE_ENCRYPTED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for server-side API calls to LiveWave.
    |
    */

    'api' => [
        'base_url' => env('LIVEWAVE_API_URL', null), // Falls back to server config if null
        'timeout' => env('LIVEWAVE_TIMEOUT', 30),
    ],

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

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    |
    | Additional options for the LiveWave connection.
    |
    */

    'options' => [
        'cluster' => env('LIVEWAVE_CLUSTER', 'mt1'),
        'use_tls' => env('LIVEWAVE_USE_TLS', false),
        'verify_ssl' => env('LIVEWAVE_VERIFY_SSL', true),
    ],

];
