<?php

namespace App\Services;

class PublicChatbotContextService
{
    public function __construct(
        private OfficialWebsiteContextService $officialWebsiteContextService
    ) {}

    public function build(): string
    {
        return $this->officialWebsiteContextService->build();
    }
}
