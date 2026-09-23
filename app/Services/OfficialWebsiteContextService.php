<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Builds a small, cited corpus from sources explicitly approved by the faculty.
 *
 * URLs never come from a visitor or from Gemini. This prevents the chatbot from
 * browsing arbitrary hosts, submitting forms, or treating a model suggestion as
 * a source of truth.
 */
class OfficialWebsiteContextService
{
    private const CACHE_KEY = 'chatbot:official-corpus:v6';

    /**
     * Backwards-compatible shortcut for callers that only need the prompt text.
     */
    public function build(string $question = ''): string
    {
        // This method is also used by the authenticated student chatbot. Its
        // historical contract was to return the available official corpus, so
        // preserve that behaviour when no public question is supplied.
        if (trim($question) === '') {
            $documents = $this->documents();

            if ($documents === []) {
                throw new RuntimeException('Les sources officielles du chatbot sont indisponibles.');
            }

            return $this->formatRetrieval(array_map(
                fn (array $document): array => [
                    'label' => $document['label'],
                    'url' => $document['url'],
                    'text' => $document['text'],
                    'score' => 0,
                ],
                $documents
            ))['context'];
        }

        return $this->retrieve($question)['context'];
    }

    /**
     * @return array{
     *     context: string,
     *     sources: list<array{label: string, url: string}>
     * }
     */
    public function retrieve(string $question): array
    {
        $documents = $this->documents();

        if ($documents === []) {
            throw new RuntimeException('Les sources officielles du chatbot sont indisponibles.');
        }

        $chunks = $this->relevantChunks($documents, $question);

        return $this->formatRetrieval($chunks);
    }

    /**
     * @param list<array{label: string, url: string, text: string, score: int}> $chunks
     * @return array{
     *     context: string,
     *     sources: list<array{label: string, url: string}>
     * }
     */
    private function formatRetrieval(array $chunks): array
    {
        if ($chunks === []) {
            return ['context' => '', 'sources' => []];
        }

        $context = [];
        $sources = [];
        $seenSources = [];
        $contextLength = 0;
        $maximumContextLength = $this->integerConfig('chatbot.max_context_characters', 14000, 1000, 30000);

        foreach ($chunks as $chunk) {
            $entry = sprintf(
                "[SOURCE OFFICIELLE : %s | %s]\n%s",
                $chunk['label'],
                $chunk['url'],
                $chunk['text']
            );

            if ($contextLength + mb_strlen($entry) > $maximumContextLength) {
                continue;
            }

            $context[] = $entry;
            $contextLength += mb_strlen($entry);

            if (!isset($seenSources[$chunk['url']])) {
                $sources[] = [
                    'label' => $chunk['label'],
                    'url' => $chunk['url'],
                ];
                $seenSources[$chunk['url']] = true;
            }
        }

        return ['context' => implode("\n\n", $context), 'sources' => $sources];
    }

    /**
     * @return list<array{label: string, title: string, url: string, text: string}>
     */
    private function documents(): array
    {
        $cachedDocuments = Cache::get(self::CACHE_KEY);

        if (is_array($cachedDocuments) && $cachedDocuments !== []) {
            return $cachedDocuments;
        }

        $documents = $this->collectDocuments();

        // Do not turn a temporary network incident into a fifteen-minute
        // outage by caching an empty corpus.
        if ($documents !== []) {
            Cache::put(
                self::CACHE_KEY,
                $documents,
                now()->addSeconds($this->integerConfig('chatbot.source_cache_seconds', 900, 60, 86400))
            );
        }

        return $documents;
    }

    /**
     * @return list<array{label: string, title: string, url: string, text: string}>
     */
    private function collectDocuments(): array
    {
        $documents = [];

        foreach (config('chatbot.official_sources', []) as $source) {
            if (!is_array($source) || ($source['enabled'] ?? true) === false) {
                continue;
            }

            foreach ($this->crawlSource($source) as $document) {
                $documents[$document['url']] ??= $document;
            }
        }

        return array_values($documents);
    }

    /**
     * @param array<string, mixed> $source
     * @return list<array{label: string, title: string, url: string, text: string}>
     */
    private function crawlSource(array $source): array
    {
        $publicUrl = $this->canonicalUrl($source['url'] ?? null);
        $fetchUrl = $this->canonicalUrl($source['fetch_url'] ?? $source['url'] ?? null);

        if ($publicUrl === null || $fetchUrl === null || !$this->isAllowedUrl($publicUrl) || !$this->isAllowedUrl($fetchUrl)) {
            return [];
        }

        $maximumPages = min(
            $this->integerConfig('chatbot.crawl_max_pages', 12, 1, 30),
            max(1, (int) ($source['max_pages'] ?? $this->integerConfig('chatbot.crawl_max_pages', 12, 1, 30)))
        );
        $maximumDepth = min(
            $this->integerConfig('chatbot.crawl_max_depth', 1, 0, 2),
            max(0, (int) ($source['max_depth'] ?? 0))
        );
        $followLinks = (bool) ($source['follow_links'] ?? false);

        $queue = [[
            'fetch_url' => $fetchUrl,
            'citation_url' => $publicUrl,
            'depth' => 0,
        ]];
        $visited = [];
        $documents = [];

        // `visited` counts requests, including a sitemap request. This keeps a
        // malformed sitemap from turning into an unbounded crawl.
        while ($queue !== [] && count($visited) < $maximumPages) {
            $item = array_shift($queue);

            if (!is_array($item) || isset($visited[$item['fetch_url']])) {
                continue;
            }

            $visited[$item['fetch_url']] = true;

            try {
                $resource = $this->fetchResource($item['fetch_url']);
            } catch (ConnectionException|RequestException|RuntimeException $exception) {
                Log::warning('Official chatbot source is unavailable.', [
                    'source' => $source['label'] ?? $publicUrl,
                    'url' => $item['fetch_url'],
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);

                continue;
            }

            if ($this->isSitemap($source, $resource['content_type'], $resource['url'])) {
                foreach ($this->sitemapUrls($resource['body']) as $url) {
                    if (!$this->canCrawlSourceUrl($source, $url)) {
                        continue;
                    }

                    $queue[] = [
                        'fetch_url' => $url,
                        'citation_url' => $url,
                        'depth' => 0,
                    ];
                }

                continue;
            }

            $text = $this->extractText($resource['body'], $resource['content_type']);

            if (mb_strlen($text) >= $this->integerConfig('chatbot.minimum_document_characters', 80, 1, 1000)) {
                $title = $this->documentTitle($resource['body'], $resource['content_type']);

                $documents[] = [
                    'label' => $this->documentLabel($source, $resource['body'], $resource['content_type']),
                    // JSON feeds often do not have an HTML title. The trusted
                    // source label is still useful for matching a question
                    // such as “l’équipe”.
                    'title' => $title !== '' ? $title : (string) ($source['label'] ?? ''),
                    'url' => $item['citation_url'],
                    'text' => Str::limit(
                        $text,
                        $this->integerConfig('chatbot.max_source_characters', 12000, 500, 30000),
                        '…'
                    ),
                ];
            }

            if (!$followLinks || $item['depth'] >= $maximumDepth || !$this->isHtml($resource['content_type'])) {
                continue;
            }

            foreach ($this->links($resource['body'], $resource['url']) as $url) {
                if (!$this->canCrawlSourceUrl($source, $url)) {
                    continue;
                }

                $queue[] = [
                    'fetch_url' => $url,
                    'citation_url' => $url,
                    'depth' => $item['depth'] + 1,
                ];
            }
        }

        return $documents;
    }

    /**
     * @return array{url: string, body: string, content_type: string}
     */
    private function fetchResource(string $url): array
    {
        $currentUrl = $url;

        // A source is a fully reviewed URL. The only redirect accepted is a
        // canonical-equivalent URL (for example adding a trailing slash); it
        // cannot change host, path, query string, or protocol.
        for ($redirects = 0; $redirects <= 1; $redirects++) {
            $response = Http::accept('text/html, application/xhtml+xml, application/xml, text/xml, application/json;q=0.9')
                ->withHeaders([
                    'User-Agent' => 'FSI-UCC-Official-Chatbot/1.0',
                ])
                ->withOptions(['allow_redirects' => false])
                ->timeout($this->integerConfig('chatbot.request_timeout', 8, 2, 20))
                ->connectTimeout($this->integerConfig('chatbot.connect_timeout', 3, 1, 10))
                ->get($currentUrl);

            if ($response->status() >= 300 && $response->status() < 400) {
                $redirectUrl = $this->equivalentRedirectUrl($currentUrl, (string) $response->header('Location'));

                if ($redirectUrl === null) {
                    throw new RuntimeException('La redirection de la source officielle est refusée.');
                }

                $currentUrl = $redirectUrl;

                continue;
            }

            $response->throw();

            $contentLength = (int) $response->header('Content-Length', 0);
            $maximumResponseBytes = $this->integerConfig('chatbot.max_response_bytes', 1000000, 10000, 2000000);

            if ($contentLength > $maximumResponseBytes) {
                throw new RuntimeException('La réponse de la source officielle est trop volumineuse.');
            }

            $body = $response->body();

            if (strlen($body) > $maximumResponseBytes) {
                throw new RuntimeException('La réponse de la source officielle est trop volumineuse.');
            }

            return [
                'url' => $currentUrl,
                'body' => $body,
                'content_type' => strtolower((string) $response->header('Content-Type')),
            ];
        }

        throw new RuntimeException('La source officielle redirige trop souvent.');
    }

    /**
     * @param list<array{label: string, title: string, url: string, text: string}> $documents
     * @return list<array{label: string, url: string, text: string, score: int}>
     */
    private function relevantChunks(array $documents, string $question): array
    {
        $terms = $this->keywords($question);

        if ($terms === []) {
            return [];
        }

        $chunks = [];

        foreach ($documents as $document) {
            foreach ($this->chunks($document['text']) as $text) {
                $score = $this->relevanceScore(
                    $terms,
                    $document['label'].' '.$document['title'],
                    $text
                );

                if ($score === 0) {
                    continue;
                }

                $chunks[] = [
                    'label' => $document['label'],
                    'url' => $document['url'],
                    'text' => $text,
                    'score' => $score,
                ];
            }
        }

        usort($chunks, fn (array $left, array $right): int => $right['score'] <=> $left['score']);

        return array_slice(
            $chunks,
            0,
            $this->integerConfig('chatbot.max_context_chunks', 6, 1, 10)
        );
    }

    /** @return list<string> */
    private function chunks(string $text): array
    {
        $maximumLength = $this->integerConfig('chatbot.max_chunk_characters', 1400, 300, 3000);
        $sentences = preg_split('/(?<=[.!?])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [$text];
        $chunks = [];
        $current = '';

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);

            if ($sentence === '') {
                continue;
            }

            if (mb_strlen($sentence) > $maximumLength) {
                if ($current !== '') {
                    $chunks[] = $current;
                    $current = '';
                }

                foreach ($this->splitLongText($sentence, $maximumLength) as $part) {
                    $chunks[] = $part;
                }

                continue;
            }

            $candidate = $current === '' ? $sentence : $current.' '.$sentence;

            if (mb_strlen($candidate) > $maximumLength) {
                $chunks[] = $current;
                $current = $sentence;
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }

    /** @return list<string> */
    private function splitLongText(string $text, int $maximumLength): array
    {
        $parts = [];

        while (mb_strlen($text) > $maximumLength) {
            $slice = mb_substr($text, 0, $maximumLength);
            $cut = mb_strrpos($slice, ' ');
            $cut = $cut === false || $cut < (int) ($maximumLength / 2) ? $maximumLength : $cut;
            $parts[] = trim(mb_substr($text, 0, $cut));
            $text = trim(mb_substr($text, $cut));
        }

        if ($text !== '') {
            $parts[] = $text;
        }

        return $parts;
    }

    /** @param list<string> $terms */
    private function relevanceScore(array $terms, string $title, string $text): int
    {
        $titleWords = $this->words($title);
        $textWords = $this->words($text);
        $score = 0;
        $matchedTerms = 0;

        foreach ($terms as $term) {
            // A concise, faculty-supplied source title/label is a much better
            // relevance signal than repeated words in a long news item or
            // staff list. Count presence, not frequency, to avoid drowning a
            // precisely named page in unrelated verbose content.
            $titleMatches = array_filter($titleWords, fn (string $word): bool => $this->wordsMatch($term, $word));
            $textMatches = array_filter($textWords, fn (string $word): bool => $this->wordsMatch($term, $word));

            if ($titleMatches !== [] || $textMatches !== []) {
                $matchedTerms++;
            }

            $score += $titleMatches === [] ? 0 : 5;
            $score += $textMatches === [] ? 0 : 1;
        }

        return $matchedTerms === 0 ? 0 : $score;
    }

    private function wordsMatch(string $left, string $right): bool
    {
        if ($left === $right) {
            return true;
        }

        $minimumStemLength = 5;

        return mb_strlen($left) >= $minimumStemLength
            && mb_strlen($right) >= $minimumStemLength
            && (str_starts_with($left, mb_substr($right, 0, $minimumStemLength))
                || str_starts_with($right, mb_substr($left, 0, $minimumStemLength)));
    }

    /** @return list<string> */
    private function keywords(string $question): array
    {
        $stopWords = [
            'avec', 'avoir', 'cela', 'chez', 'comment', 'dans', 'des', 'donc', 'elle', 'elles',
            'enfin', 'entre', 'est', 'etre', 'faire', 'font', 'leurs', 'leur', 'mais', 'mes',
            'nous', 'notre', 'pour', 'pouvez', 'quel', 'quelle', 'quels', 'quelles', 'sans',
            'sont', 'sur', 'tous', 'tout', 'une', 'vous', 'votre', 'veux', 'peux', 'puis',
        ];

        return array_values(array_unique(array_filter(
            $this->words($question),
            fn (string $word): bool => mb_strlen($word) >= 3 && !in_array($word, $stopWords, true)
        )));
    }

    /** @return list<string> */
    private function words(string $text): array
    {
        $normalised = Str::lower(Str::ascii($text));
        $words = preg_split('/[^a-z0-9]+/', $normalised, -1, PREG_SPLIT_NO_EMPTY);

        return $words === false ? [] : $words;
    }

    private function extractText(string $body, string $contentType): string
    {
        if ($this->isJson($contentType)) {
            return $this->jsonText($body);
        }

        if (!$this->isHtml($contentType)) {
            return '';
        }

        $document = $this->htmlDocument($body);

        if ($document === null) {
            return '';
        }

        $xpath = new DOMXPath($document);

        // Form controls are read as static text only. We never submit forms,
        // follow their actions, persist cookies, or send visitor data; keeping
        // labels and options lets the chatbot cite public e-Acadé programmes.
        foreach ($xpath->query('//script | //style | //noscript | //svg | //iframe | //nav | //footer') ?: [] as $node) {
            $node->parentNode?->removeChild($node);
        }

        // DOMDocument::textContent joins adjacent block elements without a
        // separator (for example an h1 immediately followed by a p). Add a
        // text boundary before collecting the visible content.
        foreach ($xpath->query('//br | //p | //div | //li | //h1 | //h2 | //h3 | //h4 | //h5 | //h6 | //section | //article | //main | //tr') ?: [] as $node) {
            $node->parentNode?->insertBefore($document->createTextNode(' '), $node->nextSibling);
        }

        $main = $xpath->query('//main | //article | //*[@role="main"]')?->item(0);
        $bodyNode = $main ?? $xpath->query('//body')?->item(0) ?? $document->documentElement;

        return Str::squish(html_entity_decode(
            $bodyNode?->textContent ?? '',
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        ));
    }

    private function jsonText(string $body): string
    {
        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return '';
        }

        $lines = [];
        $append = function (mixed $value, ?string $key = null) use (&$append, &$lines): void {
            if (is_array($value)) {
                foreach ($value as $childKey => $childValue) {
                    $append($childValue, is_string($childKey) ? $childKey : null);
                }

                return;
            }

            if (!is_string($value) && !is_int($value) && !is_float($value)) {
                return;
            }

            $text = trim((string) $value);

            if ($text === '' || filter_var($text, FILTER_VALIDATE_URL)) {
                return;
            }

            $lines[] = $key === null ? $text : Str::headline($key).': '.$text;
        };

        $append($decoded);

        return Str::squish(implode("\n", $lines));
    }

    private function documentTitle(string $body, string $contentType): string
    {
        if ($this->isJson($contentType)) {
            return '';
        }

        $document = $this->htmlDocument($body);

        if ($document === null) {
            return '';
        }

        return Str::squish($document->getElementsByTagName('title')->item(0)?->textContent ?? '');
    }

    /** @param array<string, mixed> $source */
    private function documentLabel(array $source, string $body, string $contentType): string
    {
        $label = trim((string) ($source['label'] ?? 'Source officielle'));
        $title = $this->documentTitle($body, $contentType);

        return $title !== '' && !str_contains(Str::lower($label), Str::lower($title))
            ? $label.' — '.$title
            : $label;
    }

    /** @return list<string> */
    private function sitemapUrls(string $xml): array
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (!$loaded) {
            return [];
        }

        $xpath = new DOMXPath($document);
        $urls = [];

        foreach ($xpath->query('//*[local-name()="loc"]') ?: [] as $node) {
            $url = $this->canonicalUrl($node->textContent);

            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return array_slice(array_values(array_unique($urls)), 0, $this->integerConfig('chatbot.sitemap_max_urls', 12, 1, 30));
    }

    /** @return list<string> */
    private function links(string $html, string $baseUrl): array
    {
        $document = $this->htmlDocument($html);

        if ($document === null) {
            return [];
        }

        $xpath = new DOMXPath($document);
        $links = [];

        foreach ($xpath->query('//a[@href]') ?: [] as $node) {
            $url = $this->resolveUrl($baseUrl, $node->getAttribute('href'));

            if ($url !== null) {
                $links[] = $url;
            }
        }

        return array_slice(array_values(array_unique($links)), 0, $this->integerConfig('chatbot.max_links_per_page', 12, 1, 30));
    }

    private function htmlDocument(string $html): ?DOMDocument
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return $loaded ? $document : null;
    }

    /** @param array<string, mixed> $source */
    private function isSitemap(array $source, string $contentType, string $url): bool
    {
        // `type => sitemap` applies only to the configured sitemap seed.
        // Descendant HTML pages must be extracted as documents, not parsed as
        // XML again.
        $sourceFetchUrl = $this->canonicalUrl($source['fetch_url'] ?? $source['url'] ?? null);

        if (($source['type'] ?? null) !== 'sitemap') {
            return false;
        }

        return $sourceFetchUrl === $this->canonicalUrl($url)
            || str_contains($contentType, 'xml')
            || str_ends_with(parse_url($url, PHP_URL_PATH) ?: '', '.xml');
    }

    private function isHtml(string $contentType): bool
    {
        return str_contains($contentType, 'text/html') || str_contains($contentType, 'application/xhtml+xml');
    }

    private function isJson(string $contentType): bool
    {
        return str_contains($contentType, 'application/json') || str_contains($contentType, '+json');
    }

    /** @param array<string, mixed> $source */
    private function canCrawlSourceUrl(array $source, string $url): bool
    {
        $url = $this->canonicalUrl($url);
        $sourceUrl = $this->canonicalUrl($source['url'] ?? null);

        if ($url === null || $sourceUrl === null || !$this->isAllowedUrl($url)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $sourceHost = parse_url($sourceUrl, PHP_URL_HOST);

        if ($host !== $sourceHost) {
            return false;
        }

        $paths = $source['allowed_paths'] ?? [];

        if (!is_array($paths) || $paths === []) {
            return true;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        return in_array($path, $paths, true);
    }

    private function isAllowedUrl(string $url): bool
    {
        $parts = parse_url($url);

        if ($parts === false
            || ($parts['scheme'] ?? null) !== 'https'
            || isset($parts['user'], $parts['pass'], $parts['query'], $parts['fragment'])
            || (isset($parts['port']) && (int) $parts['port'] !== 443)
        ) {
            return false;
        }

        $host = $this->canonicalHost((string) ($parts['host'] ?? ''));

        return in_array($host, config('chatbot.allowed_hosts', []), true);
    }

    private function canonicalUrl(mixed $url): ?string
    {
        if (!is_string($url) || trim($url) === '') {
            return null;
        }

        $parts = parse_url(trim($url));

        if ($parts === false || !isset($parts['scheme'], $parts['host']) || isset($parts['query'], $parts['fragment'])) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);
        $host = $this->canonicalHost($parts['host']);
        $path = '/'.ltrim((string) ($parts['path'] ?? ''), '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');

        if ($scheme !== 'https' || $host === '' || isset($parts['user'], $parts['pass']) || (isset($parts['port']) && (int) $parts['port'] !== 443)) {
            return null;
        }

        return $scheme.'://'.$host.$path;
    }

    private function canonicalHost(string $host): string
    {
        $host = strtolower(trim($host));

        return in_array($host, ['www.fsiucc.com', 'fsiucc.com'], true) ? 'fsiucc.com' : $host;
    }

    private function resolveUrl(string $baseUrl, string $href): ?string
    {
        $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($href === '' || str_starts_with($href, '#') || preg_match('/^(?:data|javascript|mailto|tel):/i', $href)) {
            return null;
        }

        if (str_starts_with($href, '//')) {
            return $this->canonicalUrl('https:'.$href);
        }

        if (preg_match('/^https?:\/\//i', $href)) {
            return $this->canonicalUrl($href);
        }

        $parts = parse_url($baseUrl);

        if ($parts === false || !isset($parts['host'])) {
            return null;
        }

        if (str_starts_with($href, '/')) {
            return $this->canonicalUrl('https://'.$parts['host'].$href);
        }

        $basePath = $parts['path'] ?? '/';
        $directory = rtrim(str_replace('\\', '/', dirname($basePath)), '/');

        return $this->canonicalUrl('https://'.$parts['host'].($directory === '' ? '/' : $directory.'/').$href);
    }

    private function equivalentRedirectUrl(string $currentUrl, string $location): ?string
    {
        $location = trim(html_entity_decode($location, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($location === '' || str_starts_with($location, '//')) {
            return null;
        }

        if (preg_match('/^https:\/\//i', $location)) {
            $candidate = $location;
        } elseif (str_starts_with($location, '/')) {
            $parts = parse_url($currentUrl);

            if ($parts === false || !isset($parts['host'])) {
                return null;
            }

            $candidate = 'https://'.$parts['host'].$location;
        } else {
            return null;
        }

        $currentCanonical = $this->canonicalUrl($currentUrl);
        $candidateCanonical = $this->canonicalUrl($candidate);

        return $currentCanonical !== null
            && $candidateCanonical !== null
            && $currentCanonical === $candidateCanonical
            && $this->isAllowedUrl($candidateCanonical)
            ? $candidate
            : null;
    }

    private function integerConfig(string $key, int $default, int $minimum, int $maximum): int
    {
        return min($maximum, max($minimum, (int) config($key, $default)));
    }
}
