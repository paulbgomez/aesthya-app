<?php

namespace App\Repositories\Moodboard;

use App\Models\Artwork;
use App\Models\Book;
use App\Models\Moodboard;
use App\Models\MusicTrack;
use App\Repositories\Repository;

class MoodboardRepository extends Repository
{
    protected $baseModel = 'App\Models\Moodboard';

    /**
     * Update generation context for a moodboard.
     */
    public function updateGenerationContext(int $moodboardId, array|string $content): bool
    {
        // If content is a JSON string, decode it first
        $data = is_string($content) ? json_decode($content, true) : $content;
        
        return Moodboard::where('id', $moodboardId)
            ->update([
                'generation_context' => $data,
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * Get a moodboard by its ID.
     */
    public function getMoodboardById(int $id): Moodboard|null
    {
        return Moodboard::find($id);
    }

    public function getMoodboardWithContent(int $id): array|null
    {
        $moodboard = $this->getMoodboardById($id);
        if (!$moodboard) {
            return null;
        }

        return [
            'moodboard' => $moodboard,
            'artworks' => Artwork::whereIn('id', $moodboard->artwork_ids ?? [])->get(),
            'musicTracks' => MusicTrack::whereIn('id', $moodboard->music_ids ?? [])->get(),
            'books' => Book::whereIn('id', $moodboard->book_ids ?? [])->get(),
        ];
    }
}