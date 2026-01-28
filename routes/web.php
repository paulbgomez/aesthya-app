<?php

use App\Http\Controllers\Feelings\FeelingsController;
use App\Http\Controllers\Feelings\GenerateContentController;
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
});

require __DIR__.'/settings.php';
