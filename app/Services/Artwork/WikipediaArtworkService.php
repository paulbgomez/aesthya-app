<?php

namespace App\Services\Artwork;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WikipediaArtworkService
{
    private const ENDPOINT_URL = 'https://en.wikipedia.org/api/rest_v1/page/summary/';

    public function fetchArtworkData(string $title): ?array
    {
        $attempts = [
            str_replace(' ', '_', $title),
            str_replace([' ', '-'], ['_', '–'], $title),
        ];

        foreach ($attempts as $attempt) {
            $url = self::ENDPOINT_URL . rawurlencode($attempt);

            try {
                $response = Http::get($url);

                if ($response->successful()) {
                    $data = $response->json();

                    return [
                        'description' => $data['extract'] ?? null,
                        'image_url' => $data['thumbnail']['source'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Wikipedia API error', ['title' => $title, 'attempt' => $attempt, 'error' => $e->getMessage()]);
                continue;
            }
        }

        Log::warning('Wikipedia lookup failed for all title variants', ['title' => $title]);
        return null;
    }
}
