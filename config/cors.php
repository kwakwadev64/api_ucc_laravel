<?php

return [
    'paths' => [
        'api/actualites',
        'api/actualites/*',
        'api/accueil-site',
        'api/public/chatbot/*',
        'api/chatbot/*',
    ],

    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],

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
