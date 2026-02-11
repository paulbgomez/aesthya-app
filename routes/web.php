<?php

use App\Http\Controllers\Feelings\FeelingsController;
use App\Http\Controllers\Feelings\GenerateContentController;
use App\Http\Controllers\Moodboard\MoodboardController;
use App\Services\Music\SoundCloudService;
use App\Services\Poem\PoetryDBService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/test', function () {
    $trackData = (new SoundCloudService)->searchTrackByTitle('nuevayol');
    
    if (!$trackData || !$trackData['stream_url']) {
        return 'No stream URL found';
    }
    
    $streamUrl = $trackData['stream_url'];
    $souncloudUrl = $trackData['soundcloud_url'];
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Stream Test</title>
</head>
<body>
    <h2>{$trackData['title']}</h2>
    <p><strong>Stream URL:</strong> {$streamUrl}</p>
    <p><strong>SoundCloud URL:</strong> {$souncloudUrl}</p>
    
    <iframe 
        width="100%" 
        height="166" 
        scrolling="no" 
        frameborder="no"
        src="https://w.soundcloud.com/player/?url={$souncloudUrl}&auto_play=true&hide_related=true&show_comments=false"
    ></iframe>
    
    <hr>
    <h3>Full Track Data:</h3>
</body>
</html>
HTML;
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::group(['middleware' => ['auth', 'verified']], function () {
    // Dashboard Route
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Feelings Routes
    Route::get('feelings', [FeelingsController::class, 'index'])
        ->name('feelings.index');
    Route::post('feelings/generate-content', [GenerateContentController::class, 'generate'])
        ->name('feelings.generate-content');

    // Moodboard Routes
    Route::get('moodboards', [MoodboardController::class, 'index'])
        ->name('moodboard');
    Route::get('moodboards/{id}', [MoodboardController::class, 'show'])
        ->name('moodboard.show');
    Route::get('/moodboards/{moodboard}/status', [MoodboardController::class, 'status'])->name('moodboard.status');
});

require __DIR__.'/settings.php';
