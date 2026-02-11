<?php

namespace App\Jobs;

use App\Models\Artwork;
use App\Models\Book;
use App\Models\Feeling;
use App\Models\Moodboard;
use App\Models\Color;
use App\Models\MusicTrack;
use App\Models\Poem;
use App\Models\ArtisticPeriod;
use DateMalformedStringException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Partitech\PhpMistral\Clients\Mistral\MistralClient;
use Partitech\PhpMistral\Exceptions\MistralClientException;

class GenerateContentForFeeling implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Feeling $feeling,
        private Moodboard $moodboard,
    ) {}

    public function handle(): void
    {
        $apiKey = config('services.mistral.api_key');
        $client = new MistralClient($apiKey);
        
        $promptConfig = config('prompts.feeling_content_generation');
        
        $previousMoodboardsArt = $this->getPreviousMoodboardsArt();
        
        $userMessage = str_replace(
            ['{feeling}', '{userId}', '{previous_moodboards_art}', '{session_count}'],
            [
                $this->feeling->name,
                $this->moodboard->user_id,
                $previousMoodboardsArt,
            ],
            $promptConfig['user_template']
        );
        
        $messages = $client->getMessages()
            ->addSystemMessage($promptConfig['system'])
            ->addUserMessage($userMessage);

        Log::info('Generating content for feeling', [
            'feeling' => $this->feeling->name,
            'user_id' => $this->moodboard->user_id,
            'previous_moodboards_art' => $previousMoodboardsArt,
        ]);
        
        $params = [
            'model' => $promptConfig['model'],
            'temperature' => $promptConfig['temperature'],
            'top_p' => $promptConfig['top_p'],
            'max_tokens' => $promptConfig['max_tokens'],
            'safe_prompt' => false,
            'response_format' => ['type' => 'json_object'],
        ];

        try {
            $response = $client->chat(messages: $messages, params: $params, stream: false);
            $generatedContent = $response->getMessage();

            $parsedData = json_decode($generatedContent, true);
            
            $this->moodboard->update([
                'generation_context' => $parsedData,
            ]);

            $this->moodboard->refresh();

            ProcessGeneratedContentJob::dispatch($this->moodboard);

        } catch (MistralClientException|DateMalformedStringException $e) {
            Log::error('Failed to generate content for feeling', [
                'feeling' => $this->feeling->name,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Get previously suggested art for this user + feeling combination
     */
    private function getPreviousMoodboardsArt(): string
    {
       $previousMoodboards = Moodboard::where('user_id', $this->moodboard->user_id)
            ->where('feeling', $this->feeling->name)
            ->where('id', '!=', $this->moodboard->id)
            ->whereNotNull('artwork_ids')
            ->get();

        if ($previousMoodboards->isEmpty()) {
            return "  (None - this is their first time with this feeling)";
        }

        $artworkIds = $previousMoodboards->pluck('artwork_ids')->flatten()->unique()->values()->all();
        $bookIds = $previousMoodboards->pluck('book_ids')->flatten()->unique()->values()->all();
        $poemIds = $previousMoodboards->pluck('poem_id')->flatten()->unique()->values()->all();
        $musicIds = $previousMoodboards->pluck('music_ids')->flatten()->unique()->values()->all();
        $colorIds = $previousMoodboards->pluck('color_ids')->flatten()->unique()->values()->all();
        $periodIds = $previousMoodboards->pluck('artistic_period_id')->flatten()->unique()->values()->all();
        return $this->getPreviousContent($artworkIds, $bookIds, $poemIds, $musicIds, $colorIds, $periodIds);
    }

    private function getPreviousArtworks(array $artworkIds): string
    {
        $artworks = Artwork::whereIn('id', $artworkIds)
            ->get()
            ->map(fn($artwork) => "  - \"{$artwork->title}\" by {$artwork->artist}")
            ->join("\n");

        return $artworks;
    }

    private function getPreviousBooks(array $bookIds): string
    {
        $books = Book::whereIn('id', $bookIds)
            ->get()
            ->map(fn($book) => "  - \"{$book->title}\" by {$book->author}")
            ->join("\n");

        return $books;
    }

    private function getPreviousPoems(array $poemIds): string
    {
        $poems = Poem::whereIn('id', $poemIds)
            ->get()
            ->map(fn($poem) => "  - \"{$poem->name}\" by {$poem->author}")
            ->join("\n");

        return $poems;
    }

    private function getPreviousMusic(array $musicIds): string
    {
        $music = MusicTrack::whereIn('id', $musicIds)
            ->get()
            ->map(fn($track) => "  - \"{$track->title}\" by {$track->artist}")
            ->join("\n");

        return $music;
    }

    private function getPreviousColors(array $colorIds): string
    {
        $colors = Color::whereIn('id', $colorIds)
            ->get()
            ->map(fn($color) => "  - \"{$color->name}\" (Hex: {$color->hex})")
            ->join("\n");

        return $colors;
    }

    private function getPreviousArtisticPeriods(array $periodIds): string
    {
        $periods = ArtisticPeriod::whereIn('id', $periodIds)
            ->get()
            ->map(fn($period) => "  - \"{$period->name}\" ({$period->years})")
            ->join("\n");

        return $periods;
    }

    private function getPreviousContent(array $artworkIds, array $bookIds, array $poemIds, array $musicIds, array $colorIds, array $periodIds): string
    {
        return "Previous Artworks:\n" . $this->getPreviousArtworks($artworkIds) .
            "\n\nPrevious Books:\n" . $this->getPreviousBooks($bookIds) .
            "\n\nPrevious Poems:\n" . $this->getPreviousPoems($poemIds) .
            "\n\nPrevious Music:\n" . $this->getPreviousMusic($musicIds) .
            "\n\nPrevious Colors:\n" . $this->getPreviousColors($colorIds) .
            "\n\nPrevious Artistic Periods:\n" . $this->getPreviousArtisticPeriods($periodIds);
    }
}
