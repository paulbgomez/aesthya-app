<?php

namespace App\Jobs;

use App\Models\Artwork;
use App\Models\Book;
use App\Models\Moodboard;
use App\Models\MusicTrack;
use App\Services\WikidataArtworkService;
use ArrayObject;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessGeneratedContentJob implements ShouldQueue
{
    use Queueable;

    private Artwork $artwork;
    private MusicTrack $musicTrack;
    private Book $book;

    public function __construct(
        private Moodboard $moodboard,
    ) {}

    public function handle(): void
    {
        $data = $this->moodboard->generation_context;
        
        if (!$data) {
            Log::warning('No generation context found for moodboard', [
                'moodboard_id' => $this->moodboard->id,
            ]);
            return;
        }

        Log::info('Processing generated content', [
            'moodboard_id' => $this->moodboard->id,
            'data_type' => get_class($data),
            'full_context' => $data,
        ]);

        $content = $data instanceof ArrayObject ? $data->getArrayCopy() : (array) $data;
        
        if (!isset($content['paintings']) && !isset($content['music']) && !isset($content['book'])) {
            Log::info('Data appears nested, extracting first element', [
                'moodboard_id' => $this->moodboard->id,
                'top_level_keys' => array_keys($content),
            ]);
            $content = array_values($content)[0] ?? [];
        }

        $artworkIds = $this->createArtworks($content['paintings'] ?? []);
        $musicIds = $this->createMusicTracks($content['music'] ?? []);
        $bookIds = $this->createBooks([$content['book'] ?? []]);
        
        // update moodboard with the ids
        $this->moodboard->update([
            'artwork_ids' => collect($artworkIds),
            'music_ids' => collect($musicIds),
            'book_ids' => collect($bookIds),
        ]);
    }

    private function createArtworks(array $artworks): array
    {
        $wikidataService = new WikidataArtworkService();
        $ids = [];
        
        foreach ($artworks as $index => $painting) {
            $title = $painting['title'] ?? 'Unknown';
            $artist = $painting['artist'] ?? 'Unknown';
            
            Log::info('Processing artwork', [
                'moodboard_id' => $this->moodboard->id,
                'index' => $index,
                'title' => $title,
                'artist' => $artist,
                'llm_data' => $painting,
            ]);
            
            $wikidataData = null;
            $verificationStatus = 'unverified';
            
            if ($title !== 'Unknown') {
                try {
                    $wikidataData = $wikidataService->findArtwork($title);
                    
                    if ($wikidataData) {
                        $verificationStatus = 'verified';
                        Log::info('Wikidata enrichment successful', [
                            'title' => $title,
                            'wikidata_id' => $wikidataData['wikidata_id'],
                            'has_image' => !empty($wikidataData['img_url']),
                        ]);
                    } else {
                        $verificationStatus = 'not_found';
                        Log::warning('Artwork not found in Wikidata, using LLM data only', [
                            'title' => $title,
                            'artist' => $artist,
                        ]);
                    }
                } catch (\Exception $e) {
                    $verificationStatus = 'verification_failed';
                    Log::warning('Wikidata enrichment failed, continuing with LLM data', [
                        'title' => $title,
                        'artist' => $artist,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            $artworkData = [
                'title' => $wikidataData['title'] ?? $title,
                'artist' => $wikidataData['artist'] ?? $artist,
                'image_url' => $wikidataData['image_url'] ?? null,
                'source' => $wikidataData['museum'] ?? $painting['museum'] ?? null,
                'style' => $wikidataData['movement'] ?? null,
                'metadata' => array_filter([
                    'year' => $wikidataData['year'] ?? $painting['year'] ?? null,
                    'material' => $wikidataData['material'] ?? null,
                    'genre' => $wikidataData['genre'] ?? null,
                    'description' => $wikidataData['description'] ?? null,
                    'llm_source' => 'mistral',
                    'verification_status' => $verificationStatus, // verified | not_found | verification_failed | unverified
                ]),
            ];
            
            $artwork = Artwork::create($artworkData);
            
            Log::info('Artwork created', [
                'artwork_id' => $artwork->id,
                'title' => $artwork->title,
                'verification_status' => $verificationStatus,
                'has_image' => !empty($artwork->img_url),
            ]);
            
            $ids[] = $artwork->id;
        }
        
        return $ids;
    }

    private function createMusicTracks(array $musicTracks): array
    {
        $ids = [];
        foreach ($musicTracks as $track) {
            $musicTrack = MusicTrack::create([
                'title' => $track['title'] ?? 'Unknown',
                'artist' => $track['artist'] ?? 'Unknown',
            ]);
            
            $ids[] = $musicTrack->id;
        }
        
        return $ids;
    }

    private function createBooks(array $books): array
    {
        $ids = [];
        foreach ($books as $book) {
            $bookModel = Book::create([
                'title' => $book['title'] ?? 'Unknown',
                'author' => $book['author'] ?? 'Unknown',
            ]);
            
            $ids[] = $bookModel->id;
        }
        
        return $ids;
    }
}