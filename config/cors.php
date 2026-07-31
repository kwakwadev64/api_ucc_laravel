<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    'allowed_origins' => [
        'https://fsiucc.com',
        'https://www.fsiucc.com',
        'http://localhost:5173',
    ],
    
    'allowed_origins' => ['*'],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 600, // Cache le preflight 10 min
    
    'supports_credentials' => false, // Mets true si tu utilises Sanctum/cookies
];