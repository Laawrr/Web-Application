<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LostItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminController;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications', [NotificationController::class, 'store']);
});

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
});

// Admin routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/users-log', [AdminController::class, 'usersLog'])->name('admin.usersLog');
    Route::get('/admin/reported-items', [AdminController::class, 'reportedItems'])->name('admin.reportedItems');
});

// Lost Items routes
Route::middleware(['auth'])->group(function () {
    Route::get('/lost-items', [LostItemController::class, 'index'])->name('lost-items.index');
    Route::post('/lost-items', [LostItemController::class, 'store'])->name('lost-items.store');
});

require __DIR__.'/auth.php';
