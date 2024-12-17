<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LostItemController; 
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\FoundItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\SessionController;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// User Routes
Route::resource('users', UserController::class);
Route::get('/user-id', [UserController::class, 'getID'])->name('user.id');
// API route to log user activity
Route::post('/log-activity', [ActivityLogController::class, 'logUserActivity']);

// Claims Routes
Route::post('/claims', [ClaimController::class, 'store']);
Route::put('/claims/{id}', [ClaimController::class, 'update']);
Route::get('/claims/{id}', [ClaimController::class, 'show']);

// Notification Routes (Authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications', [NotificationController::class, 'store']);
});

// Home Route
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard Route
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/users-log', [AdminController::class, 'usersLog'])->name('admin.usersLog');
    Route::get('/admin/reported-items', [AdminController::class, 'reportedItems'])->name('admin.reportedItems');
});

// Lost Items Routes
// Route::resource('lost-items', LostItemController::class);
Route::prefix('lost-items')->group(function () {
    Route::get('/', [LostItemController::class, 'index'])->name('lost-items.index'); // List all lost items
    Route::get('{id}', [LostItemController::class, 'show'])->name('lost-items.show'); // Display a specific lost item
    Route::post('/', [LostItemController::class, 'store'])->name('lost-items.store'); // Store a new lost item
    Route::put('{id}', [LostItemController::class, 'update'])->name('lost-items.update');// Update an existing lost item
    Route::delete('{id}', [LostItemController::class, 'destroy'])->name('lost-items.destroy'); // Delete a lost item
});

// Found Items Routes
// Route::resource('found-items', FoundItemController::class);
Route::prefix('found-items')->group(function () {
    Route::get('/', [FoundItemController::class, 'index'])->name('found-items.index'); // List all found items
    Route::get('{id}', [FoundItemController::class, 'show'])->name('found-items.show'); // Display a specific found item
    Route::post('/', [FoundItemController::class, 'store'])->name('found-items.store'); // Store a new found item
    Route::put('{id}', [FoundItemController::class, 'update'])->name('found-items.update'); // Update an existing found item
    Route::delete('{id}', [FoundItemController::class, 'destroy'])->name('found-items.destroy'); // Delete a found item
});

Route::get('/session', [SessionController::class, 'show']); // To view session details
Route::delete('/session', [SessionController::class, 'destroy']); // To destroy session

require __DIR__.'/auth.php';
