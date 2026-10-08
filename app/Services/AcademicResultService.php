<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AcademicResultService
{
    private const BASE_URL = 'https://e-acade.ucc.ac.cd';

    public function check(string $matricule): array
    {
        $matricule = trim($matricule);

        if ($matricule === '') {
            throw new RuntimeException('Le matricule est obligatoire.');
        }

        $response = Http::timeout(20)
            ->withHeaders([
                'Accept' => 'text/html,application/xhtml+xml',
                'User-Agent' => 'Mozilla/5.0',
            ])
            ->get(self::BASE_URL . '/deliberations/result', [
                'number' => $matricule,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Le portail académique UCC est momentanément indisponible.'
            );
        }

        $contentType = strtolower(
            $response->header('Content-Type', '')
        );

        /*
         * Cas où le portail renverrait directement un PDF.
         */
        if (str_contains($contentType, 'application/pdf')) {
            return [
                'success' => true,
                'type' => 'pdf',
                'message' => 'Résultat trouvé.',
                'content' => $response->body(),
            ];
        }

        return $this->parseHtmlResponse(
            $response->body(),
            $matricule
        );
    }

    private function parseHtmlResponse(
        string $html,
        string $matricule
    ): array {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);

        $dom->loadHTML(
            mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8')
        );

        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        /*
         * CAS 1 : aucun résultat
         *
         * Le portail UCC affiche actuellement son message
         * dans un élément .alert-danger.
         */
        $nodes = $xpath->query(
            "//*[contains(
                concat(' ', normalize-space(@class), ' '),
                ' alert-danger '
            )]"
        );

        if ($nodes !== false && $nodes->length > 0) {
            $message = trim($nodes->item(0)->textContent);

            return [
                'success' => false,
                'type' => 'message',
                'message' => preg_replace('/\s+/', ' ', $message),
            ];
        }

        /*
         * CAS 2 : résultat disponible
         *
         * On ne parse pas le bulletin.
         * On indique simplement au frontend que le résultat existe
         * et on lui fournit l'URL officielle à afficher dans une WebView.
         */
        return [
            'success' => true,
            'type' => 'result',
            'message' => 'Résultat trouvé.',
            'url' => self::BASE_URL . '/deliberations/result?number=' .
                urlencode($matricule),
        ];
    }
}
