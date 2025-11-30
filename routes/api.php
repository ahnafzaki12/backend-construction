<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\admin\ServiceController;
use App\Http\Controllers\admin\TempImageController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\front\ServiceController as FrontServiceController;
use App\Http\Controllers\front\ProjectController as FrontProjectController;
use App\Http\Controllers\front\PostController as FrontPostController;
use Illuminate\Support\Facades\Route;

Route::post('authenticate', [AuthenticationController::class, 'authenticate']);

// services front
Route::get('get-services', [FrontServiceController::class, 'index']);
Route::get('get-latest-services', [FrontServiceController::class, 'latestServices']);

// projects front
Route::get('get-projects', [FrontProjectController::class, 'index']);
Route::get('get-latest-projects', [FrontProjectController::class, 'latestProjects']);

// posts front
Route::get('get-posts', [FrontPostController::class, 'index']);
Route::get('get-latest-posts', [FrontPostController::class, 'latestposts']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('logout', [AuthenticationController::class, 'logout']);

    // service
    Route::post('services', [ServiceController::class, 'store']);
    Route::get('services', [ServiceController::class, 'index']);
    Route::put('services/{id}', [ServiceController::class, 'update']);
    Route::get('services/{id}', [ServiceController::class, 'show']);
    Route::delete('services/{id}', [ServiceController::class, 'destroy']);

    // project
    Route::post('projects', [ProjectController::class, 'store']);
    Route::get('projects', [ProjectController::class, 'index']);
    Route::put('projects/{id}', [ProjectController::class, 'update']);
    Route::get('projects/{id}', [ProjectController::class, 'show']);
    Route::delete('projects/{id}', [ProjectController::class, 'destroy']);

    // posts
    Route::post('posts', [PostController::class, 'store']);
    Route::get('posts', [PostController::class, 'index']);
    Route::delete('posts/{id}', [PostController::class, 'destroy']);
    Route::put('posts/{id}', [PostController::class, 'update']);
    Route::get('posts/{id}', [PostController::class, 'show']);




    // Temp images
    Route::post('temp-images', [TempImageController::class, 'store']);
});
