<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::prefix('artigos')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::post('/', [ArticleController::class, 'store']);
    Route::get('/search/{query}', [ArticleController::class, 'search']);
    Route::post('/chat', [ArticleController::class, 'chat']);
    Route::post('/summarize', [ArticleController::class, 'summarize']);
    Route::get('/{id}', [ArticleController::class, 'show']);
    Route::delete('/{id}', [ArticleController::class, 'destroy']);
});
