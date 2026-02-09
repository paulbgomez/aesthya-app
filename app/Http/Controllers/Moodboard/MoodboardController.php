<?php

namespace App\Http\Controllers\Moodboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArtisticPeriodResource;
use App\Http\Resources\ArtworkResource;
use App\Http\Resources\BookResource;
use App\Http\Resources\ColorResource;
use App\Http\Resources\MoodboardResource;
use App\Http\Resources\MusicTrackResource;
use App\Http\Resources\PoemResource;
use App\Models\Moodboard;
use App\Repositories\Moodboard\MoodboardRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MoodboardController extends Controller
{
    public function __construct(
        private MoodboardRepository $moodboardRepository
    ) {}

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
        $data = $this->moodboardRepository->getMoodboardWithContent($id);
        
        if (!$data) {
            abort(404, 'Moodboard not found');
        }

        return Inertia::render('Moodboards/Show', [
            'moodboard' => (new MoodboardResource($data['moodboard']))->resolve(),
            'artworks' => ArtworkResource::collection($data['artworks'])->resolve(),
            'musicTracks' => MusicTrackResource::collection($data['musicTracks'])->resolve(),
            'books' => BookResource::collection($data['books'])->resolve(),
            'colors' => ColorResource::collection($data['colors'])->resolve(),
            'artisticPeriod' => $data['artisticPeriod']
                ? (new ArtisticPeriodResource($data['artisticPeriod']))->resolve()
                : null,
            'poem' => $data['poem']
                ? (new PoemResource($data['poem']))->resolve()
                : null,
        ]);
    }

    public function status(Moodboard $moodboard)
    {
        return response()->json(['status' => $moodboard->job_status]);
    }
}