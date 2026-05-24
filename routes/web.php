<?php

use Illuminate\Support\Facades\Route;

Route::post('/create-user', [App\Http\Controllers\UserController::class, 'store']);
