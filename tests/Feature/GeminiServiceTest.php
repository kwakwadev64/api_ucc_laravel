<?php

namespace Tests\Feature;

use App\Services\GeminiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    public function test_it_sends_a_public_request_and_extracts_text_steps(): void
    {
        config()->set('services.gemini.public.api_key', 'public-test-key');
        config()->set('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/interactions' => Http::response([
                'steps' => [
                    [
                        'type' => 'model_output',
                        'content' => [
                            ['type' => 'text', 'text' => 'Bienvenue à l’UCC.'],
                            ['type' => 'text', 'text' => ' Consultez les admissions.'],
                        ],
                    ],
                ],
            ]),
        ]);

        $answer = app(GeminiService::class)->askPublic(
            'Comment puis-je m’inscrire ?',
            'Contexte public de test.'
        );

        $this->assertSame(
            'Bienvenue à l’UCC.'."\n".'Consultez les admissions.',
            $answer['message']
        );

        Http::assertSent(function ($request) {
            return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/interactions'
                && $request->hasHeader('x-goog-api-key', 'public-test-key')
                && $request['store'] === false
                && $request['model'] === config('services.gemini.model');
        });
    }
}
