<?php

namespace App\Services\Artwork;

use App\Models\Artwork;
use Illuminate\Support\Facades\Log;

class ArtworkEnrichmentService
{
    public function __construct(
        private WikidataArtworkService $wikidataService,
        private WikipediaArtworkService $wikipediaService
    ) {}

    /**
     * Process artwork - check DB first, enrich if needed, create or return existing
     */
    public function processArtwork(array $painting): Artwork
    {
        $title = $painting['title'] ?? 'Unknown';
        $artist = $painting['artist'] ?? 'Unknown';

        $query = Artwork::where('title', $title);
        
        if ($artist !== 'Unknown') {
            $query->where('artist', $artist);
        }
        
        $existingArtwork = $query->first();
        
        if ($existingArtwork) {
            return $existingArtwork;
        }
        
        return $this->enrichAndCreate($title, $artist, $painting);
    }

    /**
     * Enrich artwork data with Wikidata/Wikipedia and create new record
     */
    private function enrichAndCreate(string $title, string $artist, array $llmData): Artwork
    {
        $wikidataData = null;
        $wikipediaData = null;
        $verificationStatus = false;
        
        if ($title !== 'Unknown') {
            try {
                $wikidataData = $this->wikidataService->findArtwork($title);
                
                if (!$wikidataData || empty($wikidataData['image_url'])) {
                    $wikipediaData = $this->wikipediaService->fetchArtworkData($title, $artist);
                }
                
                if (($wikidataData && !empty($wikidataData['image_url'])) || 
                    ($wikipediaData && !empty($wikipediaData['image_url']))) {
                    $verificationStatus = true;
                } else {
                    Log::warning('No image found for artwork in Wikidata or Wikipedia', [
                        'title' => $title,
                        'artist' => $artist,
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Data enrichment failed, continuing with LLM data', [
                    'title' => $title,
                    'artist' => $artist,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $dataSource = 'llm';
        if ($wikidataData && !empty($wikidataData['image_url'])) {
            $dataSource = 'wikidata';
        } elseif ($wikipediaData && !empty($wikipediaData['image_url'])) {
            $dataSource = 'wikipedia';
        }
        
        $artworkData = [
            'title' => $wikidataData['title'] ?? $wikipediaData['title'] ?? $title,
            'artist' => $wikidataData['artist'] ?? $wikipediaData['artist'] ?? $artist,
            'image_url' => $wikidataData['image_url'] ?? $wikipediaData['image_url'] ?? null,
            'source' => $dataSource,
            'museum_source' => $wikidataData['museum'] ?? $llmData['museum'] ?? null,
            'style' => $wikidataData['movement'] ?? $wikipediaData['style'] ?? null,
            'verified' => $verificationStatus,
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
