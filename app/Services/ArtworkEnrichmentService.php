<?php

namespace App\Services;

use App\Models\Artwork;
use Illuminate\Support\Facades\Log;

class ArtworkEnrichmentService
{
    public function __construct(
        private WikidataArtworkService $wikidataService,
        private WikipediaArtworkService $wikipediaService
    ) {}

    /**
     * Process artwork - check DB first, enrich with Wikidata if needed, create or return existing
     */
    public function processArtwork(array $painting): Artwork
    {
        $title = $painting['title'] ?? 'Unknown';
        $artist = $painting['artist'] ?? 'Unknown';

        // check if we have it on db
        $existingArtwork = Artwork::where('title', $title)
            ->where('artist', $artist)
            ->first();
        
        if ($existingArtwork) {
            Log::info('Using existing artwork from database', [
                'artwork_id' => $existingArtwork->id,
                'title' => $title,
                'artist' => $artist,
            ]);
            
            return $existingArtwork;
        }
        
        return $this->enrichAndCreate($title, $artist, $painting);
    }

    /**
     * Enrich artwork data with Wikidata and create new record
     */
    private function enrichAndCreate(string $title, string $artist, array $llmData): Artwork
    {
        $wikidataData = null;
        $wikipediaData = null;
        $verificationStatus = 'unverified';
        
        if ($title !== 'Unknown') {
            try {
                $wikidataData = $this->wikidataService->findArtwork($title);
                
                Log::info('Wikidata response for artwork', [
                    'title' => $title,
                    'has_wikidata' => $wikidataData !== null,
                    'has_image' => isset($wikidataData['image_url']) && !empty($wikidataData['image_url']),
                ]);
                
                // If Wikidata didn't return an image, try Wikipedia
                if (!$wikidataData || empty($wikidataData['image_url'])) {
                    Log::info('Attempting Wikipedia lookup for artwork', [
                        'title' => $title,
                        'artist' => $artist,
                    ]);
                    
                    $wikipediaData = $this->wikipediaService->fetchArtworkData($title, $artist);
                    
                    Log::info('Wikipedia response for artwork', [
                        'title' => $title,
                        'has_wikipedia' => $wikipediaData !== null,
                        'has_image' => isset($wikipediaData['image_url']) && !empty($wikipediaData['image_url']),
                    ]);
                }
                
                // Determine verification status based on available data
                if (($wikidataData && !empty($wikidataData['image_url'])) || 
                    ($wikipediaData && !empty($wikipediaData['image_url']))) {
                    $verificationStatus = 'verified';
                } else {
                    $verificationStatus = 'not_verified';
                    Log::warning('No image found for artwork in Wikidata or Wikipedia', [
                        'title' => $title,
                        'artist' => $artist,
                    ]);
                }
            } catch (\Exception $e) {
                $verificationStatus = 'verification_failed';
                Log::warning('Data enrichment failed, continuing with LLM data', [
                    'title' => $title,
                    'artist' => $artist,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Merge data: Wikidata preferred, then Wikipedia, then LLM data
        $artworkData = [
            'title' => $wikidataData['title'] ?? $wikipediaData['title'] ?? $title,
            'artist' => $wikidataData['artist'] ?? $wikipediaData['artist'] ?? $artist,
            'image_url' => $wikidataData['image_url'] ?? $wikipediaData['image_url'] ?? null,
            'source' => $wikidataData['museum'] ?? $wikipediaData['source'] ?? $llmData['museum'] ?? null,
            'style' => $wikidataData['movement'] ?? $wikipediaData['style'] ?? null,
            'metadata' => array_filter([
                'year' => $wikidataData['year'] ?? $wikipediaData['year'] ?? $llmData['year'] ?? null,
                'material' => $wikidataData['material'] ?? null,
                'genre' => $wikidataData['genre'] ?? null,
                'description' => $wikidataData['description'] ?? $wikipediaData['description'] ?? null,
                'llm_source' => 'mistral',
                'verification_status' => $verificationStatus,
            ]),
        ];
        
        $artwork = Artwork::create($artworkData);
        
        Log::info('Artwork created', [
            'artwork_id' => $artwork->id,
            'title' => $artwork->title,
            'verification_status' => $verificationStatus,
            'has_image' => !empty($artworkData['image_url']),
            'image_url' => $artworkData['image_url'] ?? null,
        ]);
        
        return $artwork;
    }
}
