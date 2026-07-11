<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterOptionController;

require __DIR__.'/auth.php';
Route::get('/register-options', 
    [RegisterOptionController::class, 'index']);
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/create-user', [App\Http\Controllers\UserController::class, 'store']);
Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\UserController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/home', [\App\Http\Controllers\HomeController::class, 'getHomeData']);
Route::get('/actualites', [\App\Http\Controllers\ActualiteController::class, 'index']);
Route::get('/actualites/{id}', [\App\Http\Controllers\ActualiteController::class, 'show']);

