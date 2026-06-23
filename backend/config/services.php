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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'izipay' => [
        'client_id' => env('IZIPAY_CLIENT_ID', '18265624'),
        'client_secret' => env('IZIPAY_CLIENT_SECRET', 'testpassword_QX9wBlcyq805SNI76F6CjluRvRrDa7VSZQLo5AX2EQQVS'),
        'public_key' => env('IZIPAY_PUBLIC_KEY', '18265624:testpublickey_hBeKMJ3VoHvaIBJBnNvpMHgWkzrMkjt4m7Oxzo3m8eWK2'),
        'hmac_key' => env('IZIPAY_HMAC_KEY', 'C4qwclxyquNaSPBegmriqkG1VaxcUhyKIJz3lPdVbCf3w'),
        'endpoint' => env('IZIPAY_ENDPOINT', 'https://api.micuentaweb.pe'),
    ],

];
