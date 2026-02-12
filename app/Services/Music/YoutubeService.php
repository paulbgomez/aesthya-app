<?php

namespace App\Services\Music;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YoutubeService
{
    private const API_BASE_URL = 'https://www.googleapis.com/youtube/v3';

    public function __construct(
    ) {}

    public function searchTrackByTitle(string $title, string $artist): ?array
    {
        $apiKey = config('services.youtube.api_key');

        if (! $apiKey) {
            Log::error('Cannot search track: No YouTube API key available');
            return null;
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'Accept' => 'application/json; charset=utf-8',
            ])->get(self::API_BASE_URL . '/search', [
                'part' => 'snippet',
                'q' => $title . ' - ' . $artist,
                'type' => 'video',
                'videoDefinition' => 'high',
                'maxResults' => 1,
                'key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $items = $data['items'] ?? null;
                $track = is_array($items) ? ($items[0] ?? null) : null;

                if (! is_array($track)) {
                    return null;
                }

                return [
                    'youtube_url' => data_get($track, 'id.videoId')
                        ? 'https://www.youtube.com/watch?v='.data_get($track, 'id.videoId')
                        : null,
                    'video_id' => data_get($track, 'id.videoId') ?? $track['id'] ?? null,
                    'thumbnail_url' => data_get($track, 'snippet.thumbnails.default.url'),
                ];
            } else {
                Log::error('Failed to search tracks on YouTube. Status: '.$response->status().' Query: '.$title);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Error searching tracks on YouTube: '.$e->getMessage().' Query: '.$title);
            return null;
        }
    }
}