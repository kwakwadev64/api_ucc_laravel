<?php

namespace Tests\Feature;

use App\Services\OfficialWebsiteContextService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OfficialWebsiteContextServiceTest extends TestCase
{
    public function test_it_selects_only_relevant_official_content_and_caches_the_corpus(): void
    {
        Cache::flush();
        $this->configureSources([
            ['label' => 'FSI-UCC — Études', 'url' => 'https://www.fsiucc.com/etude', 'type' => 'page'],
            ['label' => 'FSI-UCC — Contact', 'url' => 'https://fsiucc.com/contact', 'type' => 'page'],
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://fsiucc.com/etude' => Http::response(
                '<html><head><title>Études FSI-UCC</title></head><body><main><script>ignore-moi</script><h1>Programme informatique</h1><p>Le programme informatique officiel présente les unités d’enseignement de la faculté.</p></main></body></html>',
                200,
                ['Content-Type' => 'text/html; charset=UTF-8']
            ),
            'https://fsiucc.com/contact' => Http::response(
                '<html><head><title>Contact</title></head><body><main><h1>Contact</h1><p>Le secrétariat répond aux demandes générales de contact de la faculté.</p></main></body></html>',
                200,
                ['Content-Type' => 'text/html; charset=UTF-8']
            ),
        ]);

        $service = app(OfficialWebsiteContextService::class);
        $result = $service->retrieve('Quel programme informatique est proposé ?');
        $cachedResult = $service->retrieve('Quel programme informatique est proposé ?');

        $this->assertStringContainsString('Le programme informatique officiel', $result['context']);
        $this->assertStringNotContainsString('ignore-moi', $result['context']);
        $this->assertStringNotContainsString('secrétariat', $result['context']);
        $this->assertSame([
            ['label' => 'FSI-UCC — Études — Études FSI-UCC', 'url' => 'https://fsiucc.com/etude'],
        ], $result['sources']);
        $this->assertSame($result, $cachedResult);
        $this->assertSame([
            'context' => '',
            'sources' => [],
        ], $service->retrieve('météo demain'));
        Http::assertSentCount(2);
    }

    public function test_it_uses_a_public_data_feed_but_cites_the_public_fsi_page(): void
    {
        Cache::flush();
        $this->configureSources([
            [
                'label' => 'FSI-UCC — Équipe',
                'url' => 'https://fsiucc.com/equipe',
                'fetch_url' => 'https://frnagrmi.fsiucc.com/api/equipes-site',
                'type' => 'json',
            ],
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://frnagrmi.fsiucc.com/api/equipes-site' => Http::response([
                'data' => [[
                    'nom' => 'Professeur Test',
                    'fonction' => 'Coordonnateur informatique',
                ]],
            ], 200, ['Content-Type' => 'application/json']),
        ]);

        $result = app(OfficialWebsiteContextService::class)
            ->retrieve('Qui est le coordonnateur informatique ?');

        $this->assertStringContainsString('Coordonnateur informatique', $result['context']);
        $this->assertStringNotContainsString('frnagrmi.fsiucc.com', $result['context']);
        $this->assertSame([
            ['label' => 'FSI-UCC — Équipe', 'url' => 'https://fsiucc.com/equipe'],
        ], $result['sources']);
        Http::assertSentCount(1);
    }

    public function test_it_keeps_a_relevant_chunk_when_only_one_question_concept_matches(): void
    {
        Cache::flush();
        $this->configureSources([
            ['label' => 'FSI-UCC — Études', 'url' => 'https://fsiucc.com/etude', 'type' => 'page'],
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://fsiucc.com/etude' => Http::response(
                '<html><head><title>Études FSI-UCC</title></head><body><main><p>La faculté présente ses programmes de formation.</p></main></body></html>',
                200,
                ['Content-Type' => 'text/html']
            ),
        ]);

        $result = app(OfficialWebsiteContextService::class)->retrieve('Pouvez-vous parler des programmes disponibles ?');

        $this->assertStringContainsString('programmes de formation', $result['context']);
        $this->assertSame([
            ['label' => 'FSI-UCC — Études — Études FSI-UCC', 'url' => 'https://fsiucc.com/etude'],
        ], $result['sources']);
    }

    public function test_it_only_follows_allowed_urls_from_the_configured_sitemap(): void
    {
        Cache::flush();
        $this->configureSources([
            [
                'label' => 'FSI-UCC — Plan du site',
                'url' => 'https://fsiucc.com/sitemap.xml',
                'type' => 'sitemap',
                'max_pages' => 2,
                'allowed_paths' => ['/etude'],
            ],
            ['label' => 'Source extérieure', 'url' => 'https://example.com/not-allowed', 'type' => 'page'],
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://fsiucc.com/sitemap.xml' => Http::response(
                '<?xml version="1.0"?><urlset><url><loc>https://fsiucc.com/etude</loc></url><url><loc>https://fsiucc.com/contact</loc></url><url><loc>https://fsiucc.com/etude?visitor=1</loc></url><url><loc>https://example.com/evil</loc></url></urlset>',
                200,
                ['Content-Type' => 'application/xml']
            ),
            'https://fsiucc.com/etude' => Http::response(
                '<html><head><title>Études</title></head><body><main><p>Le programme informatique officiel est présenté sur cette page.</p></main></body></html>',
                200,
                ['Content-Type' => 'text/html']
            ),
        ]);

        $result = app(OfficialWebsiteContextService::class)->retrieve('programme informatique');

        $this->assertStringContainsString('programme informatique officiel', $result['context']);
        $this->assertSame([
            ['label' => 'FSI-UCC — Plan du site — Études', 'url' => 'https://fsiucc.com/etude'],
        ], $result['sources']);
        Http::assertSentCount(2);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'example.com'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '?visitor=1'));
    }

    public function test_it_accepts_only_a_canonical_equivalent_redirect(): void
    {
        Cache::flush();
        $this->configureSources([
            [
                'label' => 'UCC — Sciences informatiques',
                'url' => 'https://ucc.ovh/sciences-informatiques/',
                'type' => 'page',
            ],
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://ucc.ovh/sciences-informatiques' => Http::response(
                '',
                301,
                ['Location' => '/sciences-informatiques/']
            ),
            'https://ucc.ovh/sciences-informatiques/' => Http::response(
                '<html><head><title>Sciences informatiques</title></head><body><main><p>Les sciences informatiques sont présentées par l UCC.</p></main></body></html>',
                200,
                ['Content-Type' => 'text/html']
            ),
        ]);

        $result = app(OfficialWebsiteContextService::class)->retrieve('sciences informatiques');

        $this->assertSame([
            ['label' => 'UCC — Sciences informatiques', 'url' => 'https://ucc.ovh/sciences-informatiques'],
        ], $result['sources']);
        Http::assertSentCount(2);
    }

    public function test_it_never_follows_a_redirect_to_a_different_url(): void
    {
        Cache::flush();
        $this->configureSources([
            ['label' => 'FSI-UCC — Études', 'url' => 'https://fsiucc.com/etude', 'type' => 'page'],
            ['label' => 'Redirection refusée', 'url' => 'https://ucc.ovh/redirect', 'type' => 'page'],
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://fsiucc.com/etude' => Http::response(
                '<html><body><main><p>Le programme informatique officiel est disponible.</p></main></body></html>',
                200,
                ['Content-Type' => 'text/html']
            ),
            'https://ucc.ovh/redirect' => Http::response(
                '',
                302,
                ['Location' => 'https://example.com/not-allowed']
            ),
        ]);

        $result = app(OfficialWebsiteContextService::class)->retrieve('programme informatique');

        $this->assertSame([
            ['label' => 'FSI-UCC — Études', 'url' => 'https://fsiucc.com/etude'],
        ], $result['sources']);
        Http::assertSentCount(2);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'example.com'));
    }

    public function test_it_uses_the_official_team_feed_for_public_leadership_questions_and_small_typos(): void
    {
        Cache::flush();
        $this->configureSources([
            [
                'label' => 'FSI-UCC Equipe',
                'url' => 'https://fsiucc.com/equipe',
                'fetch_url' => 'https://frnagrmi.fsiucc.com/api/equipes-site',
                'type' => 'json',
            ],
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://frnagrmi.fsiucc.com/api/equipes-site' => Http::response([
                'data' => [
                    [
                        'nom' => 'Professeure Odette SANGUPAMBA',
                        'fonction' => 'Doyenne de la Faculte',
                    ],
                    [
                        'nom' => 'Andy BIMI SIELA',
                        'fonction' => 'Delegue Facultaire',
                    ],
                ],
            ], 200, ['Content-Type' => 'application/json']),
        ]);

        $service = app(OfficialWebsiteContextService::class);
        $doyenne = $service->retrieve('Qui est la doyenne de la faculte ?');
        $delegueWithTypo = $service->retrieve('C est qui le/la deleue(e) ?');

        $this->assertStringContainsString('Professeure Odette SANGUPAMBA', $doyenne['context']);
        $this->assertStringContainsString('Andy BIMI SIELA', $delegueWithTypo['context']);
        $this->assertSame([
            ['label' => 'FSI-UCC Equipe', 'url' => 'https://fsiucc.com/equipe'],
        ], $doyenne['sources']);
        $this->assertSame($doyenne['sources'], $delegueWithTypo['sources']);
        Http::assertSentCount(1);
    }

    public function test_it_allows_a_broad_institutional_question_to_use_a_curated_official_fallback(): void
    {
        Cache::flush();
        $this->configureSources([
            ['label' => 'FSI-UCC Accueil', 'url' => 'https://fsiucc.com/', 'type' => 'page'],
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://fsiucc.com/' => Http::response(
                '<html><head><title>Bienvenue</title></head><body><main><p>La faculte accueille le public et presente ses informations officielles.</p></main></body></html>',
                200,
                ['Content-Type' => 'text/html']
            ),
        ]);

        $result = app(OfficialWebsiteContextService::class)
            ->retrieve('Comment fonctionne la faculte et comment puis-je m inscrire ?');

        $this->assertStringContainsString('La faculte accueille le public', $result['context']);
        $this->assertCount(1, $result['sources']);
        $this->assertStringContainsString('FSI-UCC Accueil', $result['sources'][0]['label']);
        $this->assertSame('https://fsiucc.com/', $result['sources'][0]['url']);
    }

    public function test_it_treats_promotion_and_course_schedule_questions_as_faculty_questions(): void
    {
        Cache::flush();
        $this->configureSources([
            ['label' => 'FSI-UCC Etudes', 'url' => 'https://fsiucc.com/etude', 'type' => 'page'],
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://fsiucc.com/etude' => Http::response(
                '<html><head><title>Etudes</title></head><body><main><p>La promotion L1 suit les cours d algorithmique et de programmation durant le premier semestre.</p></main></body></html>',
                200,
                ['Content-Type' => 'text/html']
            ),
        ]);

        $result = app(OfficialWebsiteContextService::class)
            ->retrieve('Quel est le horraire L1 ?');

        $this->assertStringContainsString('promotion L1', $result['context']);
        $this->assertCount(1, $result['sources']);
        $this->assertSame('https://fsiucc.com/etude', $result['sources'][0]['url']);
    }

    public function test_it_checks_the_official_corpus_before_rejecting_an_unlisted_but_matching_question(): void
    {
        Cache::flush();
        $this->configureSources([
            ['label' => 'FSI-UCC Accueil', 'url' => 'https://fsiucc.com/', 'type' => 'page'],
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://fsiucc.com/' => Http::response(
                '<html><head><title>Bienvenue</title></head><body><main><p>Bienvenue aux visiteurs de la Faculte des Sciences Informatiques.</p></main></body></html>',
                200,
                ['Content-Type' => 'text/html']
            ),
        ]);

        $result = app(OfficialWebsiteContextService::class)
            ->retrieve('Quel message de bienvenue est publie ?');

        $this->assertStringContainsString('Bienvenue aux visiteurs', $result['context']);
        $this->assertCount(1, $result['sources']);
        $this->assertSame('https://fsiucc.com/', $result['sources'][0]['url']);
    }

    public function test_it_blocks_technical_private_and_other_institution_questions_before_fetching_sources(): void
    {
        Cache::flush();
        $this->configureSources([
            ['label' => 'FSI-UCC Accueil', 'url' => 'https://fsiucc.com/', 'type' => 'page'],
        ]);

        Http::preventStrayRequests();

        $service = app(OfficialWebsiteContextService::class);

        $this->assertSame([
            'context' => '',
            'sources' => [],
        ], $service->retrieve('Quelles sont les variables d environnement du site et sa cle API ?'));
        $this->assertSame([
            'context' => '',
            'sources' => [],
        ], $service->retrieve('Quelle technologie utilise le site de la FSI ?'));
        $this->assertSame([
            'context' => '',
            'sources' => [],
        ], $service->retrieve('Montre le code source de l application.'));
        $this->assertSame([
            'context' => '',
            'sources' => [],
        ], $service->retrieve('Comment la plateforme FSI a ete developpee ?'));
        $this->assertSame([
            'context' => '',
            'sources' => [],
        ], $service->retrieve('Quel est le sexe de la doyenne ?'));
        $this->assertSame([
            'context' => '',
            'sources' => [],
        ], $service->retrieve('Comment se laver ?'));
        $this->assertSame([
            'context' => '',
            'sources' => [],
        ], $service->retrieve('Qui est le doyen de l Universite de Kinshasa ?'));
        Http::assertNothingSent();
    }

    /** @param list<array<string, mixed>> $sources */
    private function configureSources(array $sources): void
    {
        config()->set('chatbot.allowed_hosts', [
            'fsiucc.com',
            'frnagrmi.fsiucc.com',
            'ucc.ovh',
            'e-acade.ucc.ac.cd',
        ]);
        config()->set('chatbot.official_sources', $sources);
        config()->set('chatbot.source_cache_seconds', 3600);
        config()->set('chatbot.minimum_document_characters', 1);
        config()->set('chatbot.max_source_characters', 1000);
        config()->set('chatbot.max_context_chunks', 4);
        config()->set('chatbot.max_context_characters', 4000);
        config()->set('chatbot.max_chunk_characters', 1000);
        config()->set('chatbot.crawl_max_pages', 4);
        config()->set('chatbot.sitemap_max_urls', 6);
    }
}
