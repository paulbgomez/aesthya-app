<?php

namespace App\Http\Controllers\Feelings;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateContentForFeeling;
use App\Models\Feeling;
use App\Models\Moodboard;
use Illuminate\Http\Request;

class GenerateContentController extends Controller
{
    public function generate(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'User must be authenticated');
            }

        $validated = $request->validate([
            'feeling_id' => 'required|integer|exists:feelings,id',
        ]);

        $feeling = Feeling::findOrFail($validated['feeling_id']);
        
        $moodboard = Moodboard::create([
            'feeling' => $feeling->name,
            'user_id' => $user->id,
            // TODO fix this    
            'journal_id' => 1,
            'generation_context' => null,
        ]);

        GenerateContentForFeeling::dispatch($feeling, $moodboard);
        
        return back()->with('message', 'Content generation started! We\'ll notify you when ready.');
    }
}
