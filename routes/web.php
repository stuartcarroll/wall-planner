<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaintController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PaintBundleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserGroupController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/home', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'auth' => [
            'user' => auth()->user()->load('userGroups')
        ],
        'stats' => [
            'paintCount' => App\Models\Paint::count(),
            'userProjectCount' => App\Models\Project::where('owner_id', auth()->id())->count(),
            'userCount' => App\Models\User::count(),
        ]
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// Test routes without auth
Route::get('/paints-test', function() {
    return 'Paint route working - no auth required';
});

Route::get('/vue-test', function() {
    return Inertia::render('Test', [
        'message' => 'Vue/Inertia is working!'
    ]);
});

// Test paint catalog without auth (for debugging)
Route::get('/paints-vue-noauth', function() {
    $paints = collect([
        (object)[
            'id' => 1,
            'product_name' => 'Heritage Red',
            'maker' => 'Farrow & Ball',
            'product_code' => 'FB-294',
            'form' => 'estate emulsion',
            'hex_color' => '#B91927',
            'price_gbp' => 89.00,
            'volume_ml' => 2500,
            'color_description' => 'A deep, sophisticated red'
        ],
        (object)[
            'id' => 2,
            'product_name' => 'Duck Egg Blue',
            'maker' => 'Farrow & Ball',
            'product_code' => 'FB-203',
            'form' => 'modern emulsion',
            'hex_color' => '#9EB8D0',
            'price_gbp' => 89.00,
            'volume_ml' => 2500,
            'color_description' => 'A timeless blue-green'
        ]
    ]);
    
    return Inertia::render('Paints/Index', [
        'paints' => $paints,
        'auth' => [
            'user' => (object)[
                'name' => 'Test User', 
                'email' => 'test@example.com',
                'role' => 'admin'
            ]
        ]
    ]);
});

Route::middleware('auth')->group(function () {
    // Profile routes (from Laravel Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Paint Catalog routes - Full CRUD for all authenticated users
    // Admins get full CRUD, regular users get read-only (controlled in controller)
    Route::resource('paints', PaintController::class);
    Route::post('/paints/bulk-delete', [PaintController::class, 'bulkDelete'])->name('paints.bulk-delete');
    Route::post('/paints/csv-import', [PaintController::class, 'csvImport'])->name('paints.csv-import');
    
    // New Inertia/Vue paint catalog for testing
    Route::get('/paints-vue', [PaintController::class, 'vueIndex'])->name('paints.vue');
    
    // Project routes - FULL CRUD NOW WORKING!
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/bulk-delete', [ProjectController::class, 'bulkDelete'])->name('projects.bulk-delete');
    
    // Paint Bundle routes
    Route::resource('paint-bundles', PaintBundleController::class);
    Route::post('/paint-bundles/add-to-bundle', [PaintBundleController::class, 'addToPaintBundle'])->name('paint-bundles.add-to-bundle');
    
    // Authenticated project permalink route
    Route::get('/p/{permalink}', [ProjectController::class, 'showByPermalink'])->name('projects.permalink');

    // Debug route
    Route::get('/debug-projects', function() {
        $projects = App\Models\Project::all();
        $user = auth()->user();
        
        return response()->json([
            'authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user_email' => $user ? $user->email : null,
            'user_name' => $user ? $user->name : null,
            'is_admin' => $user ? $user->isAdmin() : false,
            'projects_in_db' => $projects->count(),
            'projects' => $projects->pluck('name', 'id')->toArray(),
            'route_works' => true
        ]);
    });
});

// Admin-only routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // User Management - Full CRUD
    Route::resource('users', UserController::class);
    
    // User Groups Management - Full CRUD  
    Route::resource('user-groups', UserGroupController::class);
    Route::get('user-groups/{userGroup}/manage-users', [UserGroupController::class, 'manageUsers'])->name('user-groups.manage-users');
    Route::post('user-groups/{userGroup}/add-user', [UserGroupController::class, 'addUser'])->name('user-groups.add-user');
    Route::post('user-groups/{userGroup}/remove-user', [UserGroupController::class, 'removeUser'])->name('user-groups.remove-user');
});

require __DIR__.'/auth.php';