<?php

return [
    'default_currency' => 'VND',
    'currencies' => [
        'VND' => ['symbol' => '₫', 'rate' => 1],
        'USD' => ['symbol' => '$', 'rate' => 0.000041],
        'EUR' => ['symbol' => '€', 'rate' => 0.000038],
        'USDT' => ['symbol' => 'USDT', 'rate' => 0.000041],
    ],

    'gateways' => [
        'vnpay' => [
            'enabled' => true,
            'name' => 'VNPay',
            'tmn_code' => env('VNPAY_TMN_CODE'),
            'hash_secret' => env('VNPAY_HASH_SECRET'),
            'url' => env('VNPAY_URL'),
            'return_url' => env('VNPAY_RETURN_URL'),
            'currencies' => ['VND'],
        ],
        'momo' => [
            'enabled' => true,
            'name' => 'MoMo',
            'partner_code' => env('MOMO_PARTNER_CODE'),
            'access_key' => env('MOMO_ACCESS_KEY'),
            'secret_key' => env('MOMO_SECRET_KEY'),
            'endpoint' => env('MOMO_ENDPOINT'),
            'return_url' => env('MOMO_RETURN_URL'),
            'notify_url' => env('MOMO_NOTIFY_URL'),
            'currencies' => ['VND'],
        ],
        'zalopay' => [
            'enabled' => true,
            'name' => 'ZaloPay',
            'app_id' => env('ZALOPAY_APP_ID'),
            'key1' => env('ZALOPAY_KEY1'),
            'key2' => env('ZALOPAY_KEY2'),
            'endpoint' => env('ZALOPAY_ENDPOINT'),
            'callback_url' => env('ZALOPAY_CALLBACK_URL'),
            'currencies' => ['VND'],
        ],
        'stripe' => [
            'enabled' => true,
            'name' => 'Stripe',
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currencies' => ['USD', 'EUR'],
        ],
        'paypal' => [
            'enabled' => true,
            'name' => 'PayPal',
            'mode' => env('PAYPAL_MODE', 'sandbox'),
            'sandbox' => [
                'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID'),
                'secret' => env('PAYPAL_SANDBOX_SECRET'),
            ],
            'live' => [
                'client_id' => env('PAYPAL_LIVE_CLIENT_ID'),
                'secret' => env('PAYPAL_LIVE_SECRET'),
            ],
            'currencies' => ['USD', 'EUR'],
        ],
        'usdt' => [
            'enabled' => true,
            'name' => 'USDT Crypto',
            'wallet_address' => env('USDT_WALLET_ADDRESS'),
            'network' => env('USDT_NETWORK', 'TRC20'),
            'api_key' => env('USDT_API_KEY'),
            'currencies' => ['USDT'],
        ],
        'fake' => [
            'enabled' => true,
            'name' => 'Fake Payment (Test)',
            'currencies' => ['VND', 'USD', 'EUR', 'USDT'],
        ],
    ],
];
