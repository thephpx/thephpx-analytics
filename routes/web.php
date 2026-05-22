<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', Controllers\Dashboard\Index::class)->name('dashboard');

    // Sites
    Route::get('/sites', Controllers\Sites\Index::class)->name('sites.index');
    Route::match(['GET', 'POST'], '/sites/create', Controllers\Sites\Create::class)->name('sites.create');
    Route::match(['GET', 'POST'], '/sites/{site}/edit', Controllers\Sites\Edit::class)->name('sites.edit');
    Route::delete('/sites/{site}', Controllers\Sites\Delete::class)->name('sites.delete');

    // Profile (Breeze)
    Route::get('/profile', [Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
