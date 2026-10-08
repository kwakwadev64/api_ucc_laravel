<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class YouTubeService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key');
    }

    public function getVideo(string $videoId): array
    {
        $response = Http::get('https://www.googleapis.com/youtube/v3/videos', [
            'part' => 'snippet,contentDetails',
            'id' => $videoId,
            'key' => $this->apiKey,
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Erreur lors de la communication avec YouTube.'
            );
        }

        $data = $response->json();

        if (empty($data['items'])) {
            throw new RuntimeException(
                'Vidéo YouTube introuvable.'
            );
        }

        $video = $data['items'][0];

        return [
            'youtube_video_id' => $video['id'],
            'title' => $video['snippet']['title'],
            'description' => $video['snippet']['description'] ?? null,
            'thumbnail' => $video['snippet']['thumbnails']['high']['url']
                ?? $video['snippet']['thumbnails']['default']['url']
                ?? null,
            'youtube_url' => 'https://www.youtube.com/watch?v=' . $video['id'],
        ];
    }


    public function getPlaylistVideos(string $playlistId): array
{
    $videos = [];
    $pageToken = null;

    do {
        $response = Http::get(
            'https://www.googleapis.com/youtube/v3/playlistItems',
            [
                'part' => 'snippet,contentDetails',
                'playlistId' => $playlistId,
                'maxResults' => 50,
                'pageToken' => $pageToken,
                'key' => $this->apiKey,
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'Erreur lors de la récupération de la playlist YouTube.'
            );
        }

        $data = $response->json();

        foreach ($data['items'] ?? [] as $item) {
            $videoId = $item['contentDetails']['videoId']
                ?? $item['snippet']['resourceId']['videoId']
                ?? null;

            // Vidéo supprimée ou inaccessible
            if (!$videoId) {
                continue;
            }

            $videos[] = [
                'youtube_video_id' => $videoId,
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'] ?? null,
                'thumbnail' =>
                    $item['snippet']['thumbnails']['high']['url']
                    ?? $item['snippet']['thumbnails']['default']['url']
                    ?? null,
                'youtube_url' => 'https://www.youtube.com/watch?v=' . $videoId,
                'youtube_playlist_id' => $playlistId,
                'position' => $item['snippet']['position'],
            ];
        }

        $pageToken = $data['nextPageToken'] ?? null;

    } while ($pageToken);

    return $videos;
}
}
