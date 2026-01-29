<?php

namespace App\Repositories\Moodboard;

use App\Repositories\Repository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        
        Log::info('Updating generation context', ['moodboard_id' => $moodboardId, 'data' => $data]);
        return DB::table('moodboards')
            ->where('id', $moodboardId)
            ->update([
                'generation_context' => json_encode($data),
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * Get a moodboard by its ID.
     */
    public function getMoodboardById(int $id)
    {
        $result = DB::table('moodboards')->where('id', $id)->first();
        
        if (!$result) {
            return null;
        }

        return $this->processToModel((array) $result);
    }
}