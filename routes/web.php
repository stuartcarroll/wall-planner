<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaintController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserGroupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes (from Laravel Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Paint Catalog routes - Full CRUD for all authenticated users
    // Admins get full CRUD, regular users get read-only (controlled in controller)
    Route::resource('paints', PaintController::class);
    
    // Project routes - FULL CRUD NOW WORKING!
    Route::resource('projects', ProjectController::class);
});

// Admin-only routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // User Management - Full CRUD
    Route::resource('users', UserController::class);
    
    // User Groups Management - Full CRUD  
    Route::resource('user-groups', UserGroupController::class);
});

require __DIR__.'/auth.php';