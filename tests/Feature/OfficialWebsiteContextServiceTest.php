<?php

namespace Tests\Feature;

use App\Services\OfficialWebsiteContextService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OfficialWebsiteContextServiceTest extends TestCase
{
    public function test_it_uses_only_the_configured_official_sources_and_caches_them(): void
    {
        Cache::flush();

        config()->set('chatbot.source_cache_seconds', 3600);
        config()->set('chatbot.max_source_characters', 1000);

        Http::preventStrayRequests();
        Http::fake([
            'https://www.fsiucc.com/' => Http::response(
                '<html><body><h1>Accueil FSI</h1><script>ignore-moi</script><p>Information officielle.</p></body></html>'
            ),
            'https://www.fsiucc.com/etude' => Http::response(
                '<html><body><h1>Études</h1><p>Programme officiel.</p></body></html>'
            ),
            'https://www.fsiucc.com/equipe' => Http::response(
                '<html><body><h1>Équipe</h1><p>Équipe officielle.</p></body></html>'
            ),
            'https://www.fsiucc.com/historique' => Http::response(
                '<html><body><h1>Historique</h1><p>Historique officiel.</p></body></html>'
            ),
            'https://www.fsiucc.com/galerie' => Http::response(
                '<html><body><h1>Galerie</h1><p>Galerie officielle.</p></body></html>'
            ),
        ]);

        $service = app(OfficialWebsiteContextService::class);
        $context = $service->build();
        $cachedContext = $service->build();

        $this->assertStringContainsString('Accueil FSI Information officielle.', $context);
        $this->assertStringNotContainsString('ignore-moi', $context);
        $this->assertSame($context, $cachedContext);
        Http::assertSentCount(5);
    }

    public function test_it_rejects_sources_outside_the_five_official_urls(): void
    {
        Cache::flush();

        config()->set('chatbot.official_sources', [
            [
                'label' => 'Accueil FSI-UCC',
                'url' => 'https://www.fsiucc.com/',
            ],
            [
                'label' => 'Source non autorisée',
                'url' => 'https://example.com/not-allowed',
            ],
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://www.fsiucc.com/' => Http::response('<html><body>Source autorisée.</body></html>'),
        ]);

        $context = app(OfficialWebsiteContextService::class)->build();

        $this->assertStringContainsString('Source autorisée.', $context);
        $this->assertStringNotContainsString('Source non autorisée', $context);
        Http::assertSentCount(1);
    }
}
