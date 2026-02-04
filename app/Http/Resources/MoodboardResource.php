<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MoodboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'feeling' => $this->feeling,
            'userId' => $this->user_id,
            'journalId' => $this->journal_id,
            'artworkIds' => $this->artwork_ids ?? [],
            'musicTrackIds' => $this->music_ids ?? [],
            'videoIds' => $this->video_ids ?? [],
            'bookIds' => $this->book_ids ?? [],
            'generationContext' => $this->generation_context ?? null,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'jobStatus' => $this->job_status,
        ];
    }
}
