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
                && $request['model'] === config('services.gemini.model')
                && str_contains($request['input'], 'CONTEXTE AUTORISÉ :'."\n".'Contexte public de test.')
                && str_contains($request['input'], 'QUESTION :'."\n".'Comment puis-je m’inscrire ?')
                && str_contains($request['system_instruction'], 'Refuse uniquement les demandes sans lien avec l’UCC ou la FSI-UCC.')
                && !str_contains($request['system_instruction'], 'Les questions de conseils generaux')
                && !str_contains($request['system_instruction'], 'Wikipédia');
        });
    }

    public function test_student_instruction_allows_university_questions_about_grades(): void
    {
        config()->set('services.gemini.student.api_key', 'student-test-key');
        config()->set('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/interactions' => Http::response([
                'steps' => [[
                    'type' => 'model_output',
                    'content' => [[
                        'type' => 'text',
                        'text' => 'Consultez votre compte e-Acadé.',
                    ]],
                ]],
            ]),
        ]);

        app(GeminiService::class)->askStudent(
            'Comment vérifier mes points ?',
            'Étudiant connecté à la FSI-UCC.'
        );

        Http::assertSent(fn ($request) =>
            str_contains($request['system_instruction'], 'les notes, les résultats et les démarches étudiantes')
            && str_contains($request['system_instruction'], 'https://e-acade.ucc.ac.cd')
            && str_contains($request['system_instruction'], 'Refuse uniquement les demandes sans lien')
        );
    }
}
