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
            str_replace(' ', '_', $title),                       // Spaces to underscores (Wikipedia standard)
            $title,                                              // Original title
            ucfirst(str_replace(' ', '_', $title)),              // Capitalize first letter
            str_replace([' ', '-'], ['_', '–'], $title),         // En-dash variant
        ];

        foreach ($attempts as $attempt) {
            $url = self::ENDPOINT_URL . $attempt;

            try {
                /** @var \Illuminate\Http\Client\Response $response */
                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'Aesthya/1.0 (https://github.com/paulbgomez)',
                    ])
                    ->withOptions(['allow_redirects' => true])
                    ->get($url);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['type']) && $data['type'] === 'standard') {
                        return [
                            'description' => $data['extract'] ?? null,
                            'image_url' => $data['thumbnail']['source'] ?? $data['originalimage']['source'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error('Wikipedia API error', ['title' => $title, 'attempt' => $attempt, 'url' => $url, 'error' => $e->getMessage()]);
                continue;
            }
        }

        Log::warning('Wikipedia lookup failed for all title variants', ['title' => $title, 'attempts' => $attempts]);
        return null;
    }
}
