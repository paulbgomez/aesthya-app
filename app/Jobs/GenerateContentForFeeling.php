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
        
        $messages = $client->getMessages()
    ->addSystemMessage(
        "You are an expert in emotional expression through art. " .
        "Your task is to generate a JSON response for a given mood, focusing on **accuracy, emotional depth, and cultural diversity**. " .
        "**Never invent or guess artwork titles.** Only include works that are **widely documented and verifiable in authoritative sources** (e.g., Wikidata, MoMA, Louvre, Met)."
    )
    ->addUserMessage(
        "Generate a JSON response for the mood: {$this->feeling->name}. " .
        "\n\n**STRICT ACCURACY RULES:**\n" .
        "- **Only use the exact, official title as listed in Wikidata or major museum collections.**\n" .
        "- **Never add extra text, translations, or parentheses** (e.g., use 'Le Suicidé', not 'Le Suicidé (L'Homme mort)')]\n" .
        "- **If a title is ambiguous or unverified, replace it with a confirmed alternative.**\n" .
        "- **Do not include artist names** in the 'paintings' array.\n" .
        "\n\n**EMOTIONAL APPROACH:**\n" .
        "- Choose works that **express introspection and psychological depth** matching the mood.\n" .
        "- Avoid self-help framing.\n" .
        "\n\n**DIVERSITY REQUIREMENT:**\n" .
        "- Include at least **one work from non-Western cultures or underrepresented artists** (women, POC, indigenous, LGBTQ+).\n" .
        "\n\n**REQUIRED JSON STRUCTURE:**\n" .
        "{\n" .
        "  \"paintings\": [{\"title\": \"exact, verified title from Wikidata/museums\"}] (3 examples, **no artist names**),\n" .
        "  \"classical_music\": [{\"title\": \"composition name\", \"composer\": \"full name\", \"conductor\": \"name (optional)\", \"year\": XXXX}] (2 examples, avoid Beethoven and Mozart),\n" .
        "  \"music\": [{\"title\": \"song/album name\", \"artist\": \"artist/band name\", \"year\": XXXX}] (3 examples, modern/contemporary only, include minorities),\n" .
        "  \"colors\": [{\"hex\": \"#XXXXXX\", \"pantone\": \"XXX-X\", \"name\": \"color name\", \"explanation\": \"why this color\"}] (3 colors),\n" .
        "  \"book\": {\"title\": \"exact title\", \"author\": \"full name\",},\n" .
        "  \"poem\": {\"title\": \"exact title\", \"author\": \"full name\",},\n" .
        "  \"comic\": {\"title\": \"exact title\", \"authors\": [\"name1\", \"name2\"]},\n" .
        "  \"photo\": {\"title\": \"exact title\", \"photographer\": \"full name\"},\n" .
        "  \"sentence\": {\"text\": \"the quote\", \"author\": \"full name\", \"source\": \"work/book title\"},\n" .
        "  \"artistic_period\": {\"name\": \"period name\", \"years\": \"timeframe\", \"description\": \"brief context\"}\n" .
        "}\n" .
        "\n\n**VERIFICATION CHECKLIST BEFORE RESPONDING:**\n" .
        "1. Are all painting titles **exact matches from Wikidata or major museum collections**? ✓\n" .
        "2. Have I **excluded artist names** from the 'paintings' array? ✓\n" .
        "3. Have I included **diverse cultural representation**? ✓\n" .
        "\nKeep all descriptions **concise and evocative**. Total response under 5000 tokens."
    );

        
        $params = [
            'model' => 'mistral-large-2512',
            // 'model' => 'labs-mistral-small-creative',
            'temperature' => 0.7,
            'top_p' => 0.9,
            'max_tokens' => 3000,
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