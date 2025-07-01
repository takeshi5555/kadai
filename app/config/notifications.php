<?php

return [

    'default' => env('NOTIFICATION_CHANNEL', 'mail'),

    'channels' => [

        'mail' => [
            'transport' => 'smtp',
        ],

        'database' => [
            'driver' => 'database',
        ],

        'broadcast' => [
            'driver' => 'broadcast',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

        // ここにwebpushチャンネルを追加
        'webpush' => [
         'driver' => 'webpush',
         \NotificationChannels\WebPush\WebPushChannel::class,
        ]
    ],

];