<?php

return [
    'official_sources' => [
        [
            'label' => 'Accueil FSI-UCC',
            'url' => 'https://www.fsiucc.com/',
        ],
        [
            'label' => 'Études FSI-UCC',
            'url' => 'https://www.fsiucc.com/etude',
        ],
        [
            'label' => 'Équipe FSI-UCC',
            'url' => 'https://www.fsiucc.com/equipe',
        ],
        [
            'label' => 'Historique FSI-UCC',
            'url' => 'https://www.fsiucc.com/historique',
        ],
        [
            'label' => 'Galerie FSI-UCC',
            'url' => 'https://www.fsiucc.com/galerie',
        ],
    ],

    'source_cache_seconds' => (int) env('CHATBOT_SOURCE_CACHE_SECONDS', 21600),
    'max_source_characters' => (int) env('CHATBOT_MAX_SOURCE_CHARACTERS', 8000),
];
