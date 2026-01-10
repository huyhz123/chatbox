<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Reverb Configuration
    |--------------------------------------------------------------------------
    */

    'apps' => [
        [
            'id' => env('REVERB_APP_ID'),
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'options' => [
                'host' => env('REVERB_HOST', '0.0.0.0'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'max_request_size' => 10000,
            ],
        ],
    ],

];
