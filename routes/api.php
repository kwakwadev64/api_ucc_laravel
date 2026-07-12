<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisterOptionController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\ScheduleController;


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
| Routes protégées
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {



    /*
    |--------------------------------------------------------------------------
    | Cours
    |--------------------------------------------------------------------------
    */

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





    /*
    |--------------------------------------------------------------------------
    | Horaires des cours
    |--------------------------------------------------------------------------
    */

    Route::prefix('course-schedules')->group(function () {


        Route::get(
            '/',
            [ScheduleController::class, 'indexCourses']
        );


        Route::post(
            '/',
            [ScheduleController::class, 'storeCourse']
        );

    });






    /*
    |--------------------------------------------------------------------------
    | Horaires des examens
    |--------------------------------------------------------------------------
    */

    Route::prefix('exam-schedules')->group(function () {


        Route::get(
            '/',
            [ScheduleController::class, 'indexExams']
        );


        Route::post(
            '/',
            [ScheduleController::class, 'storeExam']
        );

    });






    /*
    |--------------------------------------------------------------------------
    | Gestion commune des horaires
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/schedules/{schedule}',
        [ScheduleController::class, 'show']
    );


    Route::put(
        '/schedules/{schedule}',
        [ScheduleController::class, 'update']
    );


    Route::delete(
        '/schedules/{schedule}',
        [ScheduleController::class, 'destroy']
    );



});
