<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chat Application Settings
    |--------------------------------------------------------------------------
    */

    'app_name' => env('APP_NAME', 'Multilingual Chat'),
    'supported_locales' => explode(',', env('APP_SUPPORTED_LOCALES', 'vi,en')),

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    */

    'email_verification_required' => env('EMAIL_VERIFICATION_REQUIRED', true),
    'phone_verification_required' => env('PHONE_VERIFICATION_REQUIRED', false),
    'two_factor_enabled' => env('TWO_FACTOR_ENABLED', false),
    'password_min_length' => env('PASSWORD_MIN_LENGTH', 8),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        'message' => env('RATE_LIMIT_MESSAGE', 60), // messages per minute
        'message_vip' => env('RATE_LIMIT_MESSAGE_VIP', 'unlimited'),
        'api' => env('RATE_LIMIT_API', 100), // requests per minute
        'login' => env('RATE_LIMIT_LOGIN', 5), // attempts per minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Coin System
    |--------------------------------------------------------------------------
    */

    'coin_to_vnd_rate' => env('COIN_TO_VND_RATE', 100), // 1 coin = 100 VND
    'minimum_withdrawal' => env('MINIMUM_WITHDRAWAL', 500000), // VND
    'withdrawal_fee_percent' => env('WITHDRAWAL_FEE_PERCENT', 5),
    'streamer_commission_percent' => env('STREAMER_COMMISSION_PERCENT', 40),

    /*
    |--------------------------------------------------------------------------
    | VIP Pricing (VND)
    |--------------------------------------------------------------------------
    */

    'vip_prices' => [
        1 => env('VIP_BRONZE_PRICE', 100000),
        2 => env('VIP_SILVER_PRICE', 200000),
        3 => env('VIP_GOLD_PRICE', 500000),
        4 => env('VIP_PLATINUM_PRICE', 1000000),
        5 => env('VIP_DIAMOND_PRICE', 2000000),
        6 => env('VIP_KING_PRICE', 5000000),
        7 => env('VIP_EMPEROR_PRICE', 10000000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Daily Rewards (Coins)
    |--------------------------------------------------------------------------
    */

    'daily_rewards' => [
        'login' => env('DAILY_LOGIN_COINS', 100),
        'message' => env('DAILY_MESSAGE_COINS', 50),
        'gift' => env('DAILY_GIFT_COINS', 200),
        'room' => env('DAILY_ROOM_COINS', 100),
        'livestream' => env('DAILY_LIVESTREAM_COINS', 150),
        'online_hours' => env('DAILY_ONLINE_HOURS_COINS', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'max_upload_size' => env('MAX_UPLOAD_SIZE', 52428800), // 50MB
        'max_video_size' => env('MAX_VIDEO_SIZE', 104857600), // 100MB
        'allowed_image_types' => explode(',', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif,webp')),
        'allowed_video_types' => explode(',', env('ALLOWED_VIDEO_TYPES', 'mp4,webm,mov')),
        'allowed_audio_types' => explode(',', env('ALLOWED_AUDIO_TYPES', 'mp3,wav,ogg,m4a')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Video Processing
    |--------------------------------------------------------------------------
    */

    'video' => [
        'ffmpeg_path' => env('FFMPEG_BINARIES', '/usr/bin/ffmpeg'),
        'ffprobe_path' => env('FFPROBE_BINARIES', '/usr/bin/ffprobe'),
        'qualities' => [
            'sd' => env('VIDEO_QUALITY_SD', 480),
            'hd' => env('VIDEO_QUALITY_HD', 720),
            'fhd' => env('VIDEO_QUALITY_FHD', 1080),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Service
    |--------------------------------------------------------------------------
    */

    'translation' => [
        'enabled' => env('TRANSLATION_ENABLED', true),
        'cache_enabled' => env('TRANSLATION_CACHE_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Moderation
    |--------------------------------------------------------------------------
    */

    'moderation' => [
        'enabled' => env('CONTENT_MODERATION_ENABLED', true),
        'ai_api_key' => env('AI_MODERATION_API_KEY'),
        'banned_words' => explode(',', env('BANNED_WORDS', 'fuck,shit,damn')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    */

    'features' => [
        'live_streaming' => env('FEATURE_LIVE_STREAMING', true),
        'video_call' => env('FEATURE_VIDEO_CALL', true),
        'voice_rooms' => env('FEATURE_VOICE_ROOMS', true),
        'karaoke' => env('FEATURE_KARAOKE', true),
        'mini_games' => env('FEATURE_MINI_GAMES', true),
        'short_videos' => env('FEATURE_SHORT_VIDEOS', true),
        'dating' => env('FEATURE_DATING', true),
        'guilds' => env('FEATURE_GUILDS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Agora.io Configuration
    |--------------------------------------------------------------------------
    */

    'agora' => [
        'app_id' => env('AGORA_APP_ID'),
        'app_certificate' => env('AGORA_APP_CERTIFICATE'),
        'customer_id' => env('AGORA_CUSTOMER_ID'),
        'customer_secret' => env('AGORA_CUSTOMER_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WebRTC Configuration
    |--------------------------------------------------------------------------
    */

    'webrtc' => [
        'stun_server' => env('WEBRTC_STUN_SERVER', 'stun:stun.l.google.com:19302'),
        'turn_server' => env('WEBRTC_TURN_SERVER'),
        'turn_username' => env('WEBRTC_TURN_USERNAME'),
        'turn_password' => env('WEBRTC_TURN_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    */

    'vnpay' => [
        'tmn_code' => env('VNPAY_TMN_CODE'),
        'hash_secret' => env('VNPAY_HASH_SECRET'),
        'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => env('VNPAY_RETURN_URL'),
    ],

    'momo' => [
        'partner_code' => env('MOMO_PARTNER_CODE'),
        'access_key' => env('MOMO_ACCESS_KEY'),
        'secret_key' => env('MOMO_SECRET_KEY'),
        'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
        'return_url' => env('MOMO_RETURN_URL'),
        'notify_url' => env('MOMO_NOTIFY_URL'),
    ],

    'zalopay' => [
        'app_id' => env('ZALOPAY_APP_ID'),
        'key1' => env('ZALOPAY_KEY1'),
        'key2' => env('ZALOPAY_KEY2'),
        'endpoint' => env('ZALOPAY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/create'),
        'callback_url' => env('ZALOPAY_CALLBACK_URL'),
    ],

];
