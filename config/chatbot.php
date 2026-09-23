<?php

/*
 * Sources used by the public chatbot.
 *
 * Each URL is chosen by the faculty. A visitor can never make the service
 * fetch a URL, follow a form, authenticate, or access an e-Acadé record.
 */
return [
    'allowed_hosts' => [
        'fsiucc.com',
        'frnagrmi.fsiucc.com',
        'ucc.ovh',
        'e-acade.ucc.ac.cd',
    ],

    'official_sources' => [
        // The FSI public site is a React application. These two public API
        // endpoints are its published data feeds; citations still point to
        // the corresponding public FSI pages.
        [
            'label' => 'FSI-UCC — Accueil et actualités',
            'url' => 'https://fsiucc.com/',
            'fetch_url' => 'https://frnagrmi.fsiucc.com/api/accueil-site',
            'type' => 'json',
            'max_pages' => 1,
        ],
        [
            'label' => 'FSI-UCC — Équipe',
            'url' => 'https://fsiucc.com/equipe',
            'fetch_url' => 'https://frnagrmi.fsiucc.com/api/equipes-site',
            'type' => 'json',
            'max_pages' => 1,
        ],

        // Exact public FSI pages requested by the faculty. They are retained
        // as source seeds and may yield content if the public site renders it
        // server-side in the future.
        ['label' => 'FSI-UCC — Études', 'url' => 'https://fsiucc.com/etude', 'type' => 'page', 'max_pages' => 1],
        ['label' => 'FSI-UCC — Contact', 'url' => 'https://fsiucc.com/contact', 'type' => 'page', 'max_pages' => 1],
        ['label' => 'FSI-UCC — Historique', 'url' => 'https://fsiucc.com/historique', 'type' => 'page', 'max_pages' => 1],
        ['label' => 'FSI-UCC — Galerie', 'url' => 'https://fsiucc.com/galerie', 'type' => 'page', 'max_pages' => 1],
        [
            'label' => 'FSI-UCC — Plan du site',
            'url' => 'https://fsiucc.com/sitemap.xml',
            'type' => 'sitemap',
            // One sitemap request plus its seven explicit public routes.
            'max_pages' => 8,
            'allowed_paths' => ['/', '/etude', '/equipe', '/historique', '/galerie', '/contact', '/delegue'],
        ],

        // Dedicated public page for the faculty delegation. The Team feed
        // remains the primary source for current public functions.
        ['label' => 'FSI-UCC — Délégation', 'url' => 'https://fsiucc.com/delegue', 'type' => 'page', 'max_pages' => 1],

        // Do not crawl the broad UCC sitemap: it contains unrelated and stale
        // pages. The faculty page is deliberately an explicit source.
        ['label' => 'UCC — Accueil', 'url' => 'https://ucc.ovh/', 'type' => 'page', 'max_pages' => 1],
        ['label' => 'UCC — Sciences informatiques', 'url' => 'https://ucc.ovh/sciences-informatiques/', 'type' => 'page', 'max_pages' => 1],

        // e-Acadé is read only. No link or form is followed from these pages.
        ['label' => 'e-Acadé UCC — Accueil', 'url' => 'https://e-acade.ucc.ac.cd/', 'type' => 'page', 'max_pages' => 1],
        ['label' => 'e-Acadé UCC — Choix de programme', 'url' => 'https://e-acade.ucc.ac.cd/registration/program', 'type' => 'page', 'max_pages' => 1],
        [
            'label' => 'e-Acadé UCC — Vue d’enrôlement',
            'url' => 'https://e-acade.ucc.ac.cd/home/enrollment-view',
            // This route redirects to the portal and is session-oriented.
            'enabled' => false,
        ],
    ],

    // The cached corpus is refreshed periodically; answers themselves are
    // never cached, so every question is independently grounded.
    'source_cache_seconds' => (int) env('CHATBOT_SOURCE_CACHE_SECONDS', 900),
    'crawl_max_pages' => (int) env('CHATBOT_CRAWL_MAX_PAGES', 12),
    'crawl_max_depth' => 0,
    'sitemap_max_urls' => (int) env('CHATBOT_SITEMAP_MAX_URLS', 12),
    'max_links_per_page' => 0,

    'minimum_document_characters' => (int) env('CHATBOT_MIN_DOCUMENT_CHARACTERS', 80),
    'max_source_characters' => (int) env('CHATBOT_MAX_SOURCE_CHARACTERS', 12000),
    'max_chunk_characters' => (int) env('CHATBOT_MAX_CHUNK_CHARACTERS', 1400),
    'max_context_chunks' => (int) env('CHATBOT_MAX_CONTEXT_CHUNKS', 6),
    'max_context_characters' => (int) env('CHATBOT_MAX_CONTEXT_CHARACTERS', 14000),

    'request_timeout' => (int) env('CHATBOT_SOURCE_TIMEOUT', 8),
    'connect_timeout' => (int) env('CHATBOT_SOURCE_CONNECT_TIMEOUT', 3),
    'max_response_bytes' => (int) env('CHATBOT_MAX_RESPONSE_BYTES', 1000000),
];
