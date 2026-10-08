<?php

namespace App\Filament\Resources\TutorialResource\Pages;

use App\Filament\Resources\TutorialResource;
use App\Models\Tutorial;
use App\Services\YouTubeService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Throwable;

class CreateTutorial extends CreateRecord
{
    protected static string $resource = TutorialResource::class;

    protected function handleRecordCreation(array $data): Tutorial
    {
        $youtube = app(YouTubeService::class);

        $type = $data['youtube_type'];
        $url = trim($data['youtube_url']);

        try {
            if ($type === 'video') {
                $videoId = $this->extractVideoId($url);

                if (!$videoId) {
                    throw new \RuntimeException(
                        'Impossible de trouver l’identifiant de la vidéo YouTube.'
                    );
                }

                $video = $youtube->getVideo($videoId);

                return Tutorial::create($video);
            }

            if ($type === 'playlist') {
                $playlistId = $this->extractPlaylistId($url);

                if (!$playlistId) {
                    throw new \RuntimeException(
                        'Impossible de trouver l’identifiant de la playlist YouTube.'
                    );
                }

                $videos = $youtube->getPlaylistVideos($playlistId);

                if (empty($videos)) {
                    throw new \RuntimeException(
                        'Cette playlist ne contient aucune vidéo accessible.'
                    );
                }

                foreach ($videos as $video) {
                    Tutorial::updateOrCreate(
                        [
                            'youtube_video_id' => $video['youtube_video_id'],
                        ],
                        $video
                    );
                }

                Notification::make()
                    ->success()
                    ->title('Playlist importée')
                    ->body(count($videos) . ' tutoriel(s) importé(s) avec succès.')
                    ->send();

                /*
                 * CreateRecord attend un modèle retourné.
                 * On retourne le premier tutoriel importé.
                 */
                return Tutorial::where(
                    'youtube_video_id',
                    $videos[0]['youtube_video_id']
                )->firstOrFail();
            }

            throw new \RuntimeException(
                'Type de contenu YouTube invalide.'
            );

        } catch (Throwable $e) {
            Notification::make()
                ->danger()
                ->title('Erreur')
                ->body($e->getMessage())
                ->persistent()
                ->send();

            throw $e;
        }
    }

    private function extractVideoId(string $url): ?string
    {
        $parsedUrl = parse_url($url);

        if (!$parsedUrl) {
            return null;
        }

        // https://www.youtube.com/watch?v=VIDEO_ID
        if (
            isset($parsedUrl['query']) &&
            preg_match(
                '/(?:^|&)v=([^&]+)/',
                $parsedUrl['query'],
                $matches
            )
        ) {
            return $matches[1];
        }

        // https://youtu.be/VIDEO_ID
        if (
            isset($parsedUrl['host']) &&
            Str::contains($parsedUrl['host'], 'youtu.be')
        ) {
            return trim($parsedUrl['path'] ?? '/', '/');
        }

        // https://www.youtube.com/shorts/VIDEO_ID
        if (
            isset($parsedUrl['path']) &&
            preg_match(
                '#/shorts/([^/]+)#',
                $parsedUrl['path'],
                $matches
            )
        ) {
            return $matches[1];
        }

        return null;
    }

    private function extractPlaylistId(string $url): ?string
    {
        $parsedUrl = parse_url($url);

        if (
            !$parsedUrl ||
            empty($parsedUrl['query'])
        ) {
            return null;
        }

        parse_str($parsedUrl['query'], $query);

        return $query['list'] ?? null;
    }
}
