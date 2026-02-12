<?php

use App\Http\Controllers\Feelings\FeelingsController;
use App\Http\Controllers\Feelings\GenerateContentController;
use App\Http\Controllers\Moodboard\MoodboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

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
