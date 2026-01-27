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

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('feelings', [FeelingsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('feelings');

require __DIR__.'/settings.php';
