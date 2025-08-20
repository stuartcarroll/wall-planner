<?php

use App\Http\Controllers\Api\PaintApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProjectApiController;
use App\Http\Controllers\Api\ProjectImageApiController;
use App\Http\Controllers\Api\PaintBundleApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\DashboardApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::post('/auth/register', [AuthApiController::class, 'register']);
Route::post('/auth/logout', [AuthApiController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/user', [AuthApiController::class, 'user'])->middleware('auth:sanctum');
Route::get('/auth/csrf-token', [AuthApiController::class, 'csrfToken']);

// Paint API routes
Route::apiResource('paints', PaintApiController::class);

// Project API routes
Route::apiResource('projects', ProjectApiController::class)->middleware('auth:sanctum');
Route::post('/projects/{project}/members', [ProjectApiController::class, 'addMember'])->middleware('auth:sanctum');
Route::delete('/projects/{project}/members/{user}', [ProjectApiController::class, 'removeMember'])->middleware('auth:sanctum');
Route::get('/users', [ProjectApiController::class, 'getUsers'])->middleware('auth:sanctum');

// Project Images API routes
Route::get('/projects/{project}/images', [ProjectImageApiController::class, 'index'])->middleware('auth:sanctum');
Route::post('/projects/{project}/images', [ProjectImageApiController::class, 'store'])->middleware('auth:sanctum');
Route::put('/projects/{project}/images/{projectImage}', [ProjectImageApiController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/projects/{project}/images/{projectImage}', [ProjectImageApiController::class, 'destroy'])->middleware('auth:sanctum');

// Paint Bundle API routes
Route::apiResource('paint-bundles', PaintBundleApiController::class)->middleware('auth:sanctum');
Route::post('/paint-bundles/{paintBundle}/paints', [PaintBundleApiController::class, 'addPaint'])->middleware('auth:sanctum');
Route::delete('/paint-bundles/{paintBundle}/paints/{paint}', [PaintBundleApiController::class, 'removePaint'])->middleware('auth:sanctum');

// Admin User API routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('admin/users', UserApiController::class);
    Route::apiResource('admin/user-groups', \App\Http\Controllers\Api\UserGroupApiController::class);
    Route::post('/admin/user-groups/{userGroup}/users/{user}', [\App\Http\Controllers\Api\UserGroupApiController::class, 'addUser']);
    Route::delete('/admin/user-groups/{userGroup}/users/{user}', [\App\Http\Controllers\Api\UserGroupApiController::class, 'removeUser']);
});

// Dashboard API
Route::get('/dashboard', [DashboardApiController::class, 'index'])->middleware('auth:sanctum');

// Test route without auth
Route::get('/test', function() {
    return response()->json(['message' => 'API is working!']);
});