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

    public function handle(): void
    {
        $apiKey = config('services.mistral.api_key');
        $client = new MistralClient($apiKey);
        
        $promptConfig = config('prompts.feeling_content_generation');
        
        $userMessage = str_replace(
            '{feeling}',
            $this->feeling->name,
            $promptConfig['user_template']
        );
        
        $messages = $client->getMessages()
            ->addSystemMessage($promptConfig['system'])
            ->addUserMessage($userMessage);
        
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
                'feeling_id' => $this->feeling->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}