<?php

namespace App\Services\Poem;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PoetryDBService
{
    private const API_ENDPOINT = 'https://poetrydb.org/author,title/';

    public function fetchPoemByAuthorAndTitle(string $author, string $title): ?array
    {
        $params = $author.';'.$title;
        $output = '/lines';

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(10)->get(self::API_ENDPOINT.$params.$output);

            if ($response->successful()) {
                $data = $response->json();

                if (! is_array($data)) {
                    return null;
                }

                Log::info('Successfully fetched poem from PoetryDB: '.$author.' - '.$title);

                return $data;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error fetching poem from PoetryDB: '.$e->getMessage());

            return null;
        }
    }
}
