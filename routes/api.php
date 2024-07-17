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
// Route::middleware('auth.token')->group(function () {
//     Route::apiResource('blogs', BlogController::class);
//     Route::apiResource('blogs.posts', PostController::class);
//     Route::apiResource('posts.comments', CommentController::class)->only(['store']);
//     Route::apiResource('posts.likes', LikeController::class)->only(['store']);
// });

Route::middleware('auth.token')->group(function () {
    // Blogs
    Route::get('blogs', [BlogController::class, 'index']);
    Route::post('blogs', [BlogController::class, 'store']);
    Route::get('blogs/{blog}', [BlogController::class, 'show']);
    Route::put('blogs/{blog}', [BlogController::class, 'update']);
    Route::delete('blogs/{blog}', [BlogController::class, 'destroy']);

    // Posts under a Blog
    Route::get('blogs/{blog}/posts', [PostController::class, 'index']);
    Route::post('blogs/{blog}/posts', [PostController::class, 'store']);
    Route::get('blogs/{blog}/posts/{post}', [PostController::class, 'show']);
    Route::put('blogs/{blog}/posts/{post}', [PostController::class, 'update']);
    Route::delete('blogs/{blog}/posts/{post}', [PostController::class, 'destroy']);

    // Comments under a Post
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);

    // Likes under a Post
    Route::post('posts/{post}/likes', [LikeController::class, 'store']);
});