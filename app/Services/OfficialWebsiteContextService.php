<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class OfficialWebsiteContextService
{
    private const OFFICIAL_URLS = [
        'https://www.fsiucc.com/',
        'https://www.fsiucc.com/etude',
        'https://www.fsiucc.com/equipe',
        'https://www.fsiucc.com/historique',
        'https://www.fsiucc.com/galerie',
    ];

    public function build(): string
    {
        $sources = collect(config('chatbot.official_sources', []))
            ->filter(fn (array $source) => $this->isAllowedSource($source))
            ->map(fn (array $source) => $this->sourceContext($source))
            ->filter()
            ->values();

        if ($sources->isEmpty()) {
            throw new RuntimeException('Les sources officielles du chatbot sont indisponibles.');
        }

        return $sources->implode("\n\n");
    }

    private function sourceContext(array $source): ?string
    {
        $url = $source['url'];
        $label = $source['label'] ?? $url;

        try {
            $content = Cache::remember(
                'chatbot:official-source:'.sha1($url),
                now()->addSeconds($this->cacheSeconds()),
                fn () => $this->fetchText($url)
            );
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('Official chatbot source is unavailable.', [
                'source' => $label,
                'exception' => $exception::class,
            ]);

            return null;
        }

        if (blank($content)) {
            return null;
        }

        return sprintf(
            "[SOURCE OFFICIELLE : %s | %s]\n%s",
            $label,
            $url,
            $content
        );
    }

    private function fetchText(string $url): string
    {
        $response = Http::accept('text/html')
            ->withOptions(['allow_redirects' => false])
            ->timeout(10)
            ->connectTimeout(5)
            ->get($url);

        $response->throw();

        $html = preg_replace(
            '/<(script|style|noscript)[^>]*>.*?<\/\\1>/is',
            ' ',
            $response->body()
        ) ?? $response->body();

        $text = html_entity_decode(
            preg_replace('/<[^>]+>/', ' ', $html) ?? $html,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        return Str::limit(
            Str::squish($text),
            (int) config('chatbot.max_source_characters', 8000),
            '…'
        );
    }

    private function isAllowedSource(array $source): bool
    {
        $url = $source['url'] ?? null;

        return is_string($url) && in_array($url, self::OFFICIAL_URLS, true);
    }

    private function cacheSeconds(): int
    {
        return max(60, (int) config('chatbot.source_cache_seconds', 21600));
    }
}
