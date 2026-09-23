<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GeminiService;
use App\Services\PublicChatbotContextService;
use App\Services\StudentChatbotContextService;
use Tests\TestCase;

class ChatbotEndpointTest extends TestCase
{
    public function test_public_chatbot_accepts_cors_preflight_from_the_website(): void
    {
        $this->call('OPTIONS', '/api/public/chatbot/message', [], [], [], [
            'HTTP_ORIGIN' => 'https://fsiucc.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ])
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'https://fsiucc.com');
    }

    public function test_public_chatbot_accepts_a_message_without_authentication(): void
    {
        $this->app->instance(PublicChatbotContextService::class, new class extends PublicChatbotContextService
        {
            public function __construct() {}

            public function retrieve(string $question): array
            {
                return [
                    'context' => 'Contexte public de test.',
                    'sources' => [[
                        'label' => 'FSI-UCC — Test',
                        'url' => 'https://fsiucc.com/test',
                    ]],
                ];
            }
        });

        $gemini = new class extends GeminiService
        {
            public array $calls = [];

            public function askPublic(string $message, string $context): array
            {
                $this->calls[] = compact('message', 'context');

                return ['message' => 'Réponse publique de test.'];
            }
        };
        $this->app->instance(GeminiService::class, $gemini);

        $response = $this->postJson('/api/public/chatbot/message', [
            'message' => 'Comment puis-je m’inscrire ?',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sources.0.label', 'FSI-UCC — Test')
            ->assertJsonPath('data.sources.0.url', 'https://fsiucc.com/test');

        $this->assertStringContainsString('Réponse publique de test.', $response->json('data.message'));
        $this->assertStringContainsString('[FSI-UCC — Test](https://fsiucc.com/test)', $response->json('data.message'));
        $this->assertSame([[
            'message' => 'Comment puis-je m’inscrire ?',
            'context' => 'Contexte public de test.',
        ]], $gemini->calls);
    }

    public function test_public_chatbot_refuses_when_no_official_source_supports_the_question(): void
    {
        $this->app->instance(PublicChatbotContextService::class, new class extends PublicChatbotContextService
        {
            public function __construct() {}

            public function retrieve(string $question): array
            {
                return ['context' => '', 'sources' => []];
            }
        });

        $this->app->instance(GeminiService::class, new class extends GeminiService
        {
            public function askPublic(string $message, string $context): array
            {
                throw new \RuntimeException('Gemini ne doit pas être appelé sans source.');
            }
        });

        $this->postJson('/api/public/chatbot/message', [
            'message' => 'Quelle est la météo de demain ?',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'data.message',
                'Je ne peux pas confirmer cette information à partir des pages officielles fournies.'
            )
            ->assertJsonPath('data.sources', []);
    }

    public function test_student_chatbot_accepts_an_authenticated_student(): void
    {
        $this->app->instance(StudentChatbotContextService::class, new class extends StudentChatbotContextService
        {
            public function __construct() {}

            public function build(User $student): string
            {
                return 'Contexte étudiant de test.';
            }
        });

        $this->app->instance(GeminiService::class, new class extends GeminiService
        {
            public function askStudent(string $message, string $context): array
            {
                return ['message' => 'Réponse étudiant de test.'];
            }
        });

        $student = new User([
            'role' => 'student',
            'is_active' => true,
        ]);
        $student->id = 1;
        $student->exists = true;

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/chatbot/message', [
                'message' => 'Quels sont mes horaires ?',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.message', 'Réponse étudiant de test.');
    }

    public function test_student_chatbot_rejects_non_student_users(): void
    {
        $teacher = new User([
            'role' => 'teacher',
            'is_active' => true,
        ]);
        $teacher->id = 2;
        $teacher->exists = true;

        $this->actingAs($teacher, 'sanctum')
            ->postJson('/api/chatbot/message', [
                'message' => 'Quels sont mes horaires ?',
            ])
            ->assertForbidden();
    }
}
