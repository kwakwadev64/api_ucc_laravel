<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;

use App\Models\Course;
use App\Policies\CoursePolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        /*
        |--------------------------------------------------------------------------
        | Reset Password URL
        |--------------------------------------------------------------------------
        */

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {

            return config('app.frontend_url')
                . '/password-reset'
                . '?token=' . $token
                . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        });



        /*
        |--------------------------------------------------------------------------
        | Email Verification URL
        |--------------------------------------------------------------------------
        */

        VerifyEmail::createUrlUsing(function (object $notifiable) {

            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );


            return config('app.frontend_url')
                . '/verify-email?url='
                . urlencode($verificationUrl);

        });



        /*
        |--------------------------------------------------------------------------
        | Policies
        |--------------------------------------------------------------------------
        */

        Gate::policy(
            Course::class,
            CoursePolicy::class
        );

    }
}
