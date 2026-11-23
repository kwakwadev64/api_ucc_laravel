<?php

return [



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

    'gemini' => [
        'base_url' => env(
            'GEMINI_BASE_URL',
            'https://generativelanguage.googleapis.com/v1beta'
        ),
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash-lite'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 30),
        'public' => [
            'api_key' => env('GEMINI_PUBLIC_API_KEY'),
        ],
        'student' => [
            'api_key' => env('GEMINI_STUDENT_API_KEY'),
        ],
    ],

    'youtube' => [
    'api_key' => env('YOUTUBE_API_KEY'),
],

'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],

'facebook' => [
    'client_id'       => env('FACEBOOK_CLIENT_ID'),
    'client_secret'   => env('FACEBOOK_CLIENT_SECRET'),
    'redirect'        => env('FACEBOOK_REDIRECT_URI'),
    'mobile_redirect' => env('FACEBOOK_MOBILE_REDIRECT', 'fsi-hub://auth/callback'),],
];
