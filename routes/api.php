<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth.token')->group(function () {
    Route::apiResource('blogs', BlogController::class);
    Route::apiResource('blogs.posts', PostController::class);
    Route::apiResource('posts.comments', CommentController::class)->only(['store']);
    Route::apiResource('posts.likes', LikeController::class)->only(['store']);
});