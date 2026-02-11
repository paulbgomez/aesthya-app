<?php

namespace App\Services\Poem;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PoetryDBService
{
    private const API_ENDPOINT = 'https://poetrydb.org/author,title/';

    public function fetchPoemByAuthorAndTitle(string $author, string $title): ?array
    {
        $params = rawurlencode($author).';'.rawurlencode($title);
        $output = '/lines';

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(10)->get(self::API_ENDPOINT.$params.$output);

            if ($response->successful()) {
                $data = $response->json();

                if (! is_array($data)) {
                    return null;
                }

                if (array_key_exists('status', $data) || array_key_exists('reason', $data)) {
                    return null;
                }

                $poem = $data[0] ?? null;

                if (! is_array($poem) || ! array_key_exists('lines', $poem)) {
                    return null;
                }

                Log::info('Successfully fetched poem from PoetryDB: '.$author.' - '.$title);

                return $poem['lines'];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error fetching poem from PoetryDB: '.$e->getMessage());

            return null;
        }
    }
}
