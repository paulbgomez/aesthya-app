<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WikipediaArtworkService
{
    public function fetchArtworkData(string $title): ?array
    {
        $url = "https://en.wikipedia.org/api/rest_v1/page/summary/" . urlencode($title);

        try {
            $response = file_get_contents($url);
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
            Log::error('Wikipedia API error', ['title' => $title, 'error' => $e->getMessage()]);
            return null;
        }

        return null;
    }
}