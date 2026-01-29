<?php

namespace App\Jobs;

use App\Models\Feeling;
use App\Models\Moodboard;
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

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateContentForFeeling job failed', [
            'feeling_id' => $this->feeling->id ?? null,
            'moodboard_id' => $this->moodboard->id ?? null,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    public function handle(): void
    {
        Log::info('GenerateContentForFeeling job started', [
            'feeling_id' => $this->feeling->id,
            'feeling_name' => $this->feeling->name,
            'moodboard_id' => $this->moodboard->id,
        ]);

        $apiKey = config('services.mistral.api_key');
        
        if (!$apiKey) {
            Log::error('Mistral API key not configured', ['feeling_id' => $this->feeling->id]);
            throw new \Exception('Mistral API key not configured');
        }

        $client = new MistralClient($apiKey);
        
        $messages = $client->getMessages()
            ->addSystemMessage(
                "You are an expert in emotional expression through art. " .
                "For each emotion, **always provide unique, tailored suggestions** that match its psychological depth. " .
                "Prioritize **niche, or culturally specific** works when possible."
            )
            ->addUserMessage(
                "Generate a JSON response for the mood: {$this->feeling->name}. " .
                "The goal is NOT to soothe, or reduce the mood. " .
                "The goal is to **explore, inhabit, and understand the emotional experience**. " .
                "Choose artworks that express introspection, or psychological depth matching the feeling. " .
                "Avoid self-help framing. " .
                "Include the following keys: " .
                "colors (3 colors with hexcode and pantone reflecting the emotion and explanation), " .
                "book (title and author), " .
                "poem (title and author), " .
                "comic (title and author/artist), " .
                "photo (title and photographer), " .
                "sentence (a philosophically or psychologically relevant sentence with author), " .
                "artistic_period (name and description), " .
                "paintings (2 examples: title and artist), " .
                "sculpture (title and artist), " .
                "classical_music (2 examples: title and composer), " .
                "music (3 examples: title and artist), " .
                "Keep responses evocative, introspective, psychologically honest, and concise."
            );

        $params = [
            'model' => 'labs-mistral-small-creative',
            'temperature' => 1.0,
            'top_p' => 0.97,
            'max_tokens' => 3000,
            'safe_prompt' => false,
            'response_format' => ['type' => 'json_object'],
        ];

        try {
            $generatedContent = '';
            
            foreach ($client->chat(messages: $messages, params: $params, stream: true) as $chunk) {
                $generatedContent .= $chunk->getChunk();
            }

            Log::info('Raw Mistral response', [
                'content' => $generatedContent,
                'length' => strlen($generatedContent),
            ]);

            // Extract JSON from markdown code blocks if present
            $jsonContent = $this->extractJson($generatedContent);
            
            Log::info('Extracted JSON content', [
                'content' => $jsonContent,
                'length' => strlen($jsonContent),
            ]);
            
            // Validate it's proper JSON
            $decodedJson = json_decode($jsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode failed', [
                    'error' => json_last_error_msg(),
                    'raw_content' => $generatedContent,
                    'extracted_json' => $jsonContent,
                ]);
                throw new \Exception('Invalid JSON response from Mistral: ' . json_last_error_msg());
            }

            Moodboard::where('id', $this->moodboard->id)
            ->update([
                'generation_context' => $decodedJson,
            ]);

            Log::info('Content generated for feeling', [
                'feeling_id' => $this->feeling->id,
                'moodboard_id' => $this->moodboard->id,
                'content' => $decodedJson,
            ]);
        } catch (MistralClientException|DateMalformedStringException $e) {
            Log::error('Failed to generate content for feeling', [
                'feeling_id' => $this->feeling->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Extract JSON from markdown code blocks or raw text
     */
    private function extractJson(string $content): string
    {
        // Remove markdown code blocks (```json ... ``` or ``` ... ```)
        $content = trim($content);
        
        // Remove opening code fence
        $content = preg_replace('/^```(?:json)?\s*/s', '', $content);
        
        // Remove closing code fence
        $content = preg_replace('/\s*```$/s', '', $content);
        
        return trim($content);
    }
}