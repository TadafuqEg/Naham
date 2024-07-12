<?php

use BeyondCode\LaravelWebSockets\Dashboard\Http\Middleware\Authorize;

return [

    'apps' => [
        [
            'id' => env('PUSHER_APP_ID', 'local'),
            'name' => env('APP_NAME', 'Z-chat'),
            'key' => env('PUSHER_APP_KEY', 'localtyjtfhl554rthrtgff'),
            'secret' => env('PUSHER_APP_SECRET', 'localtrdfgdfh5465fthrthtrhrge'),
            'path' => env('PUSHER_APP_PATH', '/'),
            'capacity' => null,
            'enable_client_messages' => false,
            'enable_statistics' => true,
        ],
    ],

    'allowed_origins' => [
        // '*',
    ],

    'max_request_size_in_kb' => 250,

    'path' => 'laravel-websockets',

    'middleware' => [
        'web',
        \BeyondCode\LaravelWebSockets\Dashboard\Http\Middleware\Authorize::class,
    ],

    'statistics' => [
        'model' => \BeyondCode\LaravelWebSockets\Statistics\Models\WebSocketsStatisticsEntry::class,

        'logger' => BeyondCode\LaravelWebSockets\Statistics\Logger\HttpStatisticsLogger::class,

        'interval_in_seconds' => 60,

        'delete_statistics_older_than_days' => 60,
    ],

    'ssl' => [
        'local_cert' => null,
        'local_pk' => null,
        'passphrase' => null,
    ],

    'channel_manager' => \BeyondCode\LaravelWebSockets\WebSockets\Channels\ChannelManagers\ArrayChannelManager::class,
];
