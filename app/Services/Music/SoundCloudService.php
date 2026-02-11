<?php

namespace App\Services\Music;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SoundCloudService
{
    private const API_BASE_URL = 'https://api.soundcloud.com';
    
    public function __construct(
    ) {}

    private function getAccessToken(): ?string
    {
        return  Cache::remember('soundcloud_access_token', now()->addHours(24), function () {
            $clientId = env('SOUNDCLOUD_CLIENT_ID');
            $clientSecret = env('SOUNDCLOUD_CLIENT_SECRET');

            try {
                /** @var \Illuminate\Http\Client\Response $response */
                $response = Http::asForm()->post(self::API_BASE_URL . '/oauth2/token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'client_credentials',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['access_token'] ?? null;
                } else {
                    Log::error('Failed to obtain SoundCloud access token. Status: '.$response->status());
                    return null;
                }
            } catch (\Exception $e) {
                Log::error('Error obtaining SoundCloud access token: '.$e->getMessage());
                return null;
            }
        });
    }

    public function searchTrackByTitle(string $title): ?array
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            Log::error('Cannot search track: No access token available');
            return null;
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'Accept' => 'application/json; charset=utf-8',
                'Authorization' => 'OAuth ' . $accessToken,
            ])->get(self::API_BASE_URL . '/tracks', [
                'q' => $title,
                'limit' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $track = $data[0];

                return [
                    'title' => $track['title'] ?? null,
                    'soundcloud_url' => $track['permalink_url'] ?? null,
                    'artwork_url' => $track['artwork_url'] ?? null,
                    'duration' => $track['duration'] ?? null,
                    'stream_url' => $track['stream_url'] ?? null,
                ];
            } else {
                Log::error('Failed to search tracks on SoundCloud. Status: '.$response->status().' Title: '.$title);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Error searching tracks on SoundCloud: '.$e->getMessage().' Title: '.$title);
            return null;
        }
    }
}