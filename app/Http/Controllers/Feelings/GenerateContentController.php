<?php

namespace App\Http\Controllers\Feelings;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateContentForFeeling;
use App\Models\Feeling;
use App\Models\Journal;
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
            'journal_id' => Journal::create([
                'user_id' => $user->id,
            ])->id,
            'generation_context' => null,
        ]);

        GenerateContentForFeeling::dispatch($feeling, $moodboard);

        return redirect()->route('moodboard.show', $moodboard->id)
            ->with('status', 'Content generation started for ' . $feeling->name);
    }
}
