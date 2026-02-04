<?php

namespace App\Http\Controllers\Moodboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\MoodboardResource;
use App\Models\Moodboard;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MoodboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'User must be authenticated');
        }

        $moodboards = Moodboard::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Moodboards/Index', [
            'moodboards' => MoodboardResource::collection($moodboards),
        ]);
    }

    public function show(int $id): Response
    {
        $moodboard = Moodboard::findOrFail((int)$id);

        return Inertia::render('Moodboards/Show', [
            'moodboard' => (new MoodboardResource($moodboard))->resolve(),
        ]);
    }

    public function status(Moodboard $moodboard)
    {
        return response()->json(['status' => $moodboard->job_status]);
    }
}