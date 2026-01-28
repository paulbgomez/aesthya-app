<?php

namespace App\Jobs;

use App\Models\Feeling;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateContentForFeeling implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Feeling $feeling)
    {
    }

    public function handle(): void
    {
        // TODO: Implement content generation logic here
        Log::info("Generating content for feeling: {$this->feeling->name}");
    }
}
