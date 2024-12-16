<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LostItemController;  // Add this line
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

});

Route::get('/edit-profile', [ProfileController::class, 'edit'])->name('editProfile');

// Add the route for LostItemController
Route::get('/lost-items', [LostItemController::class, 'index'])->name('lost-items.index');  // For GET request
Route::post('/lost-items', [LostItemController::class, 'store'])->name('lost-items.store');  // For POST request


require __DIR__.'/auth.php';
