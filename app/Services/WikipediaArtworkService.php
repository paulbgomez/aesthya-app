<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WikipediaArtworkService
{
    public function fetchArtworkData(string $title): ?array
    {
        $attempts = [
            str_replace(' ', '_', $title),
            str_replace([' ', '-'], ['_', '–'], $title),
        ];

        foreach ($attempts as $attempt) {
            $url = "https://en.wikipedia.org/api/rest_v1/page/summary/" . rawurlencode($attempt);

            try {
                $response = @file_get_contents($url);
                if ($response === false) {
                    continue;
                }
                
                $response = json_decode($response);

                $response = json_decode($response);

                if (isset($response->title)) {
                    Log::info('Wikipedia response for artwork', [
                        'title' => $response->title,
                        'description' => $response->extract ?? null,
                        'image_url' => $response->thumbnail->source ?? null,
                    ]);

                    return [
                        'description' => $response->extract ?? null,
                        'image_url' => $response->thumbnail->source ?? null,
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