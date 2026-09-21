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
    public function publicMessage(
        SendPublicChatbotMessageRequest $request,
        PublicChatbotContextService $contextService,
        GeminiService $gemini
    ): JsonResponse {
        try {
            $answer = $gemini->askPublic(
                $request->string('message')->trim()->toString(),
                $contextService->build()
            );

            return $this->success($answer['message']);
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

    private function success(string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'message' => $message,
            ],
        ]);
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
