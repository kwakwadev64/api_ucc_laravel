<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AcademicResultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AcademicResultController extends Controller
{
    public function __construct(
        private AcademicResultService $academicResultService
    ) {
    }

    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'matricule' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        try {
            $result = $this->academicResultService->check(
                $validated['matricule']
            );

            /*
             * Si le portail renvoie directement un PDF.
             */
            if ($result['type'] === 'pdf') {
                return response()->json([
                    'success' => true,
                    'type' => 'pdf',
                    'message' => $result['message'],
                ]);
            }

            /*
             * Aucun résultat disponible.
             */
            if (
                $result['success'] === false
                && $result['type'] === 'message'
            ) {
                return response()->json([
                    'success' => false,
                    'type' => 'message',
                    'message' => $result['message'],
                ]);
            }

            /*
             * Résultat disponible.
             *
             * L'URL officielle sera utilisée par la WebView
             * du frontend.
             */
            if (
                $result['success'] === true
                && $result['type'] === 'result'
            ) {
                return response()->json([
                    'success' => true,
                    'type' => 'result',
                    'message' => $result['message'],
                    'url' => $result['url'],
                ]);
            }

            /*
             * Cas inattendu.
             */
            return response()->json([
                'success' => false,
                'type' => 'unknown',
                'message' => 'La réponse du portail académique n’a pas pu être interprétée.',
            ], 502);

        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'type' => 'service_unavailable',
                'message' => $e->getMessage(),
            ], 503);
        }
    }
}
