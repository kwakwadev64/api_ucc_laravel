<?php

return [
    'paths' => [
        'api/public/chatbot/*',
        'api/chatbot/*',
    ],

    'allowed_methods' => ['POST', 'OPTIONS'],

    'allowed_origins' => [
        'https://fsiucc.com',
        'https://www.fsiucc.com',
        'http://localhost:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 600,

    'supports_credentials' => false,
];
