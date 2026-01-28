<?php

use App\Http\Controllers\Feelings\FeelingsController;
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
});

require __DIR__.'/settings.php';
