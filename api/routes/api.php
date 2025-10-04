<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('artigos')->group(function () {
    Route::post('/', [ArticleController::class, 'store']);
    Route::get('/', [ArticleController::class, 'index']);
    Route::get('/search/{query}', [ArticleController::class, 'search']);
    Route::post('/chat', [ChatController::class, 'chat']);
});
