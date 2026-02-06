<?php

namespace App\Services\Artwork;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WikidataArtworkService
{
    private const ENDPOINT_URL = 'https://query.wikidata.org/sparql';
    private const TIMEOUT = 10;

    /**
     * Query Wikidata for artwork details by title
     */
    public function findArtwork(string $title): ?array
    {
        $sparqlQuery = $this->buildArtworkQuery($title);
        try {
            $result = $this->executeQuery($sparqlQuery);

            if (empty($result['results']['bindings'])) {
                Log::info('No Wikidata results found for artwork', [
                    'title' => $title,
                ]);
                return null;
            }
            
            return $this->parseArtworkResult($result['results']['bindings'][0]);
        } catch (\Exception $e) {
            Log::error('Wikidata query failed', [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function buildArtworkQuery(string $title): string
    {
        $escapedTitle = str_replace('"', '\\"', $title);

        return <<<SPARQL
            SELECT ?painting ?paintingLabel ?creatorLabel ?image ?inception ?collectionLabel ?materialLabel ?movementLabel ?genreLabel ?description WHERE {
            ?painting wdt:P31 wd:Q3305213 .        # instance of painting
            ?painting wdt:P170 ?creator .          # get the creator entity
            ?painting rdfs:label "$escapedTitle"@en .
            
            OPTIONAL { ?painting wdt:P18 ?image }          # image
            OPTIONAL { ?painting wdt:P571 ?inception }     # date created
            OPTIONAL { ?painting wdt:P195 ?collection }    # collection
            OPTIONAL { ?painting wdt:P186 ?material }      # material used
            OPTIONAL { ?painting wdt:P135 ?movement }      # art movement
            OPTIONAL { ?painting wdt:P136 ?genre }         # genre
            OPTIONAL {
                ?painting schema:description ?description .
                FILTER(LANG(?description) = "en")
            }
            
            SERVICE wikibase:label { bd:serviceParam wikibase:language "en" }
            }
        SPARQL;
    }

    private function executeQuery(string $sparqlQuery): array
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders([
                    'Accept' => 'application/sparql-results+json',
                    'User-Agent' => 'AesthyaApp/1.0 (Laravel; +https://aesthya.app)',
                ])
                ->get(self::ENDPOINT_URL, [
                    'query' => $sparqlQuery,
                ]);

            if ($response->failed()) {
                throw new \Exception('Wikidata query failed: ' . $response->status());
            }

            $data = $response->json();

            return $data;
            
        } catch (\Exception $e) {
            Log::error('Wikidata query error', [
                'query' => $sparqlQuery,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    private function parseArtworkResult(array $binding): array
    {
        $wikidataId = null;
        if (isset($binding['painting']['value'])) {
            // Extract Q-ID from URI like "http://www.wikidata.org/entity/Q19897995"
            if (preg_match('/\/entity\/(Q\d+)/', $binding['painting']['value'], $matches)) {
                $wikidataId = $matches[1];
            }
        }
        
        return [
            'wikidata_id' => $wikidataId,
            'title' => $binding['paintingLabel']['value'] ?? null,
            'artist' => $binding['creatorLabel']['value'] ?? null,
            'image_url' => $binding['image']['value'] ?? null,
            'year' => $this->extractYear($binding['inception']['value'] ?? null),
            'museum' => $binding['collectionLabel']['value'] ?? null,
            'material' => $binding['materialLabel']['value'] ?? null,
            'movement' => $binding['movementLabel']['value'] ?? null,
            'genre' => $binding['genreLabel']['value'] ?? null,
            'description' => $binding['description']['value'] ?? null,
        ];
    }

    /**
     * Extract year from date string
     */
    private function extractYear(?string $date): ?int
    {
        if (!$date) {
            return null;
        }

        if (preg_match('/^(\d{4})/', $date, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
