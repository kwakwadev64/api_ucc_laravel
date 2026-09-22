<?php

return [
    'official_sources' => [
        [
            'label' => 'Accueil FSI-UCC',
            'url' => 'https://www.fsiucc.com/',
            'keywords' => ['accueil', 'contact', 'faculte', 'ucc'],
        ],
        [
            'label' => 'Études FSI-UCC',
            'url' => 'https://www.fsiucc.com/etude',
            'keywords' => ['etude', 'filiere', 'cours', 'annale', 'horaire', 'planning'],
        ],
        [
            'label' => 'Équipe FSI-UCC',
            'url' => 'https://www.fsiucc.com/equipe',
            'keywords' => ['equipe', 'direction', 'doyen', 'enseignant', 'professeur'],
        ],
        [
            'label' => 'Historique FSI-UCC',
            'url' => 'https://www.fsiucc.com/historique',
            'keywords' => ['historique', 'delegue', 'delegation', 'promotion', 'chef'],
        ],
        [
            'label' => 'Galerie FSI-UCC',
            'url' => 'https://www.fsiucc.com/galerie',
            'keywords' => ['galerie', 'photo', 'image'],
        ],
    ],

    'source_cache_seconds' => (int) env('CHATBOT_SOURCE_CACHE_SECONDS', 21600),
    'max_source_characters' => (int) env('CHATBOT_MAX_SOURCE_CHARACTERS', 8000),
    'max_sources' => (int) env('CHATBOT_MAX_SOURCES', 3),
    'max_context_characters' => (int) env('CHATBOT_MAX_CONTEXT_CHARACTERS', 18000),
];
