<?php

namespace App\Jobs;

use App\Models\Moodboard;
use App\Models\MusicTrack;
use App\Services\Artwork\ArtworkEnrichmentService;
use App\Services\Book\BookService;
use App\Services\Color\ColorService;
use ArrayObject;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessGeneratedContentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Moodboard $moodboard,
    ) {}

    public function handle(ArtworkEnrichmentService $artworkService, BookService $bookService): void
    {
        $data = $this->moodboard->generation_context;
        
        if (!$data) {
            Log::warning('No generation context found for moodboard', [
                'moodboard_id' => $this->moodboard->id,
            ]);
            return;
        }

        $content = $data instanceof ArrayObject ? $data->getArrayCopy() : (array) $data;
        
        if (!isset($content['paintings']) && !isset($content['music']) && !isset($content['book'])) {
            Log::info('Data appears nested, extracting first element', [
                'moodboard_id' => $this->moodboard->id,
                'top_level_keys' => array_keys($content),
            ]);
            $content = array_values($content)[0] ?? [];
        }

        $artworkIds = $this->processArtworks($content['paintings'] ?? [], $artworkService);
        $musicIds = $this->processMusicTracks($content['music'] ?? []);
        $bookIds = $this->processBooks([$content['book'] ?? []], $bookService);
        $colors = $this->processColors($content['colors'] ?? [], new ColorService());
        
        $this->moodboard->update([
            'artwork_ids' => collect($artworkIds),
            'music_ids' => collect($musicIds),
            'book_ids' => collect($bookIds),
            'job_status' => 'completed',
        ]);
    }

    private function processArtworks(array $artworks, ArtworkEnrichmentService $service): array
    {
        $ids = [];
        
        foreach ($artworks as $painting) {
            $artwork = $service->processArtwork($painting);
            $ids[] = $artwork->id;
        }
        
        return $ids;
    }

    private function processMusicTracks(array $musicTracks): array
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

    private function processBooks(array $books, BookService $bookService): array
    {
        $ids = [];
        
        foreach ($books as $book) {
            $result = $bookService->processBookData(
                $book['author'] ?? 'Unknown',
                $book['title'] ?? 'Unknown'
            );
            
            if ($result) {
                $ids[] = $result->id;
            } else {
                Log::warning('Book processing returned null', ['book' => $book]);
            }
        }
        
        return $ids;
    }

    private function processColors(array $colors, ColorService $service): array
    {
        $ids = [];
        
        foreach ($colors as $color) {
            $color = $service->processColorData($color);
            if ($color) {
                $ids[] = $color->id;
            } else {
                Log::warning('Color processing returned null', ['color' => $color]);
            }
        }
        
        return $ids;
    }
}