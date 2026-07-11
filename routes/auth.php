<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ActualiteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [RegisteredUserController::class, 'store'])
    ->name('register');



Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->name('login');



Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('password.email');



Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->name('password.store');


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', function (Request $request) {
        return response()->json([
            'user' => $request->user()
        ]);
    });


    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');



    Route::post('/email/verification-notification', [
        EmailVerificationNotificationController::class,
        'store'
    ])
    ->middleware('throttle:6,1')
    ->name('verification.send');

    Route::get('/home', [HomeController::class, 'getHomeData']);
    Route::get('/actualites', [ActualiteController::class, 'index']);
    Route::get('/actualites/{id}', [ActualiteController::class, 'show']);
});


Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
