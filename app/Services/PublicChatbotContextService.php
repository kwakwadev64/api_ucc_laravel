<?php

namespace App\Services;

class PublicChatbotContextService
{
    public function __construct(
        private OfficialWebsiteContextService $officialWebsiteContextService
    ) {}

    /**
     * @return array{
     *     context: string,
     *     sources: list<array{label: string, url: string}>
     * }
     */
    public function retrieve(string $question): array
    {
        return $this->officialWebsiteContextService->retrieve($question);
    }

    public function build(string $question = ''): string
    {
        return $this->officialWebsiteContextService->build($question);
    }

    // Kept for callers from the previous public-chatbot integration.
    public function buildForQuestion(string $question): string
    {
        return $this->retrieve($question)['context'];
    }
}
