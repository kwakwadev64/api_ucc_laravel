<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisterOptionController;
use App\Http\Controllers\Api\CourseController;


require __DIR__.'/auth.php';



/*
|--------------------------------------------------------------------------
| Options inscription
|--------------------------------------------------------------------------
*/

Route::get(
    '/register-options',
    [RegisterOptionController::class, 'index']
);






/*
|--------------------------------------------------------------------------
| Cours
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {


    Route::get(
        '/courses',
        [CourseController::class, 'index']
    );


    Route::get(
        '/courses/{course}',
        [CourseController::class, 'show']
    );


    Route::post(
        '/courses',
        [CourseController::class, 'store']
    );


    Route::put(
        '/courses/{course}',
        [CourseController::class, 'update']
    );


    Route::delete(
        '/courses/{course}',
        [CourseController::class, 'destroy']
    );


    Route::get(
        '/courses/{course}/download',
        [CourseController::class, 'download']
    );

});
