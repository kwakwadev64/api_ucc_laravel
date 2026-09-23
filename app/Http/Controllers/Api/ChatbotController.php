<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPublicChatbotMessageRequest;
use App\Http\Requests\SendStudentChatbotMessageRequest;
use App\Services\GeminiService;
use App\Services\PublicChatbotContextService;
use App\Services\StudentChatbotContextService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ChatbotController extends Controller
{
    private const PUBLIC_NO_CONFIRMATION = 'Je ne peux pas confirmer cette information à partir des pages officielles fournies.';

    public function publicMessage(
        SendPublicChatbotMessageRequest $request,
        PublicChatbotContextService $contextService,
        GeminiService $gemini
    ): JsonResponse {
        try {
            $question = $request->string('message')->trim()->toString();
            $retrieval = $contextService->retrieve($question);

            // Do not call the model unless an official, relevant source was
            // found. This makes the mandatory refusal deterministic.
            if ($retrieval['context'] === '' || $retrieval['sources'] === []) {
                return $this->success(self::PUBLIC_NO_CONFIRMATION);
            }

            $answer = $gemini->askPublic(
                $question,
                $retrieval['context']
            );

            return $this->success(
                $this->withOfficialSources($answer['message'], $retrieval['sources']),
                $retrieval['sources']
            );
        } catch (Throwable $exception) {
            return $this->error($exception, 'public');
        }
    }

    public function studentMessage(
        SendStudentChatbotMessageRequest $request,
        StudentChatbotContextService $contextService,
        GeminiService $gemini
    ): JsonResponse {
        try {
            $answer = $gemini->askStudent(
                $request->string('message')->trim()->toString(),
                $contextService->build($request->user())
            );

            return $this->success($answer['message']);
        } catch (Throwable $exception) {
            return $this->error($exception, 'student');
        }
    }

    /**
     * @param list<array{label: string, url: string}> $sources
     */
    private function success(string $message, array $sources = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'message' => $message,
                'sources' => $sources,
            ],
        ]);
    }

    /**
     * The model never chooses citations. The backend appends the exact,
     * allowlisted sources used to build its context.
     *
     * @param list<array{label: string, url: string}> $sources
     */
    private function withOfficialSources(string $message, array $sources): string
    {
        $message = trim($message);

        if ($message === self::PUBLIC_NO_CONFIRMATION) {
            return $message;
        }

        $lines = [];

        foreach ($sources as $source) {
            $label = trim((string) ($source['label'] ?? ''));
            $url = trim((string) ($source['url'] ?? ''));

            if ($label === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
                continue;
            }

            $label = str_replace(['[', ']'], ['\\[', '\\]'], $label);
            $lines[$url] = sprintf('- [%s](%s)', $label, $url);
        }

        return $lines === []
            ? $message
            : $message."\n\n**Sources officielles :**\n".implode("\n", array_values($lines));
    }

    private function error(Throwable $exception, string $audience): JsonResponse
    {
        $status = $exception instanceof RequestException
            ? $exception->response->status()
            : null;

        Log::warning('Chatbot request failed.', [
            'audience' => $audience,
            'exception' => $exception::class,
            'provider_status' => $status,
            'exception_message' => $exception->getMessage(),
        ]);

        if ($status === 429) {
            return response()->json([
                'success' => false,
                'message' => 'Le chatbot est temporairement très sollicité. Réessayez dans quelques instants.',
            ], 429);
        }

        if (
            $exception instanceof RequestException
            || $exception instanceof ConnectionException
            || $exception instanceof RuntimeException
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Le chatbot est momentanément indisponible. Réessayez plus tard.',
            ], 503);
        }

        return response()->json([
            'success' => false,
            'message' => 'Impossible de traiter votre demande pour le moment.',
        ], 500);
    }
}
