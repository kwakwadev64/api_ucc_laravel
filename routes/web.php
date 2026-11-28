<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController\Auth;
use App\Http\Controllers\Auth\FacebookAuthController;



Route::redirect('/', '/admin');

require __DIR__.'/auth.php';


Route::view('/politique-confidentialite', 'politique-confidentialite');

Route::view('/suppression-donnees', 'suppression-donnees');

Route::view('/conditions-utilisation', 'conditions-utilisation')
    ->name('conditions.utilisation');


Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])
    ->name('google.redirect');

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
    ->name('google.callback');


Route::get('/auth/facebook', [FacebookAuthController::class, 'redirect'])
    ->name('facebook.redirect');

Route::get('/auth/facebook/callback', [FacebookAuthController::class, 'callback'])
    ->name('facebook.callback');

Route::get('/password-reset', function () {
    $token = request()->query('token');
    $email = request()->query('email');

    abort_unless(
        is_string($token) &&
        $token !== '' &&
        is_string($email) &&
        filter_var($email, FILTER_VALIDATE_EMAIL),
        400
    );

    return view('auth.password-reset', [
        'token' => $token,
        'email' => $email,
    ]);
})->name('password.reset.page');
