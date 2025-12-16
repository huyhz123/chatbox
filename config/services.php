<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
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
    'sms' => [
        'gateway' => env('SMS_GATEWAY', 'twilio'),
        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],
    ],
    'chatbot' => [
        'enabled' => env('CHATBOT_ENABLED', true),
        'openai_key' => env('OPENAI_API_KEY'),
        'model' => env('CHATBOT_MODEL', 'gpt-3.5-turbo'),
    ],
    'dhru' => [
        'api_url' => env('DHRU_API_URL'),
        'api_key' => env('DHRU_API_KEY'),
        'username' => env('DHRU_USERNAME'),
    ],
    'gsm' => [
        'api_url' => env('GSM_API_URL'),
        'api_key' => env('GSM_API_KEY'),
    ],
];
