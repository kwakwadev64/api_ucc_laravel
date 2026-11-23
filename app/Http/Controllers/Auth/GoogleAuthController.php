<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\RedirectsToMobileApp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    use RedirectsToMobileApp;

    protected function mobileRedirectUrl(): string
    {
        return config('services.google.mobile_redirect');
    }

    public function redirect()
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        // Annulation ou refus côté Google (?error=access_denied).
        if ($request->filled('error')) {
            return $this->backToApp(['error' => 'cancelled']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $e) {
            report($e);
            return $this->backToApp(['error' => 'invalid_state']);
        } catch (\Throwable $e) {
            report($e);
            return $this->backToApp(['error' => 'google_failed']);
        }

        $email = $googleUser->getEmail();

        if (!$email) {
            return $this->backToApp(['error' => 'email_missing']);
        }

        $emailVerified = $googleUser->user['verified_email']
            ?? $googleUser->user['email_verified']
            ?? false;

        if (!$emailVerified) {
            return $this->backToApp(['error' => 'email_not_verified']);
        }

        $code = Str::random(64);

        Cache::put(
            'google_oauth_code:' . hash('sha256', $code),
            [
                'google_id'     => $googleUser->getId(),
                'email'         => $email,
                'first_name'    => $googleUser->user['given_name'] ?? null,
                'last_name'     => $googleUser->user['family_name'] ?? null,
                'profile_photo' => $googleUser->getAvatar(),
            ],
            now()->addMinutes(5)
        );

        return $this->backToApp(['code' => $code]);
    }
}
