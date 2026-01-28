<?php

namespace App\Http\Controllers\Feelings;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateContentForFeeling;
use App\Repositories\Feelings\FeelingsRepository;
use Illuminate\Http\Request;

class GenerateContentController extends Controller
{
    public function generate(Request $request, FeelingsRepository $repo)
    {
        // 1. Validate the input
        $validated = $request->validate([
            'feeling_id' => 'required|exists:feelings,id'
        ]);

        // 2. Get the feeling from repository
        $feeling = $repo->getFeelingById($validated['feeling_id']);
        
        // 3. Dispatch async job to handle LLM + API calls
        // Think of this as "hiring a worker" to do the heavy lifting in background
        GenerateContentForFeeling::dispatch($feeling);
        
        // 4. Return immediately (don't wait for job to finish)
        return back()->with('message', 'Content generation started! We\'ll notify you when ready.');
    }
}
