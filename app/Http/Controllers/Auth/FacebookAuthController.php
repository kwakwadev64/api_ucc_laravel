<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\RedirectsToMobileApp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class FacebookAuthController extends Controller
{
    use RedirectsToMobileApp;

    protected function mobileRedirectUrl(): string
    {
        return config('services.facebook.mobile_redirect');
    }

    /**
     * Rediriger l'utilisateur vers Facebook.
     */
    public function redirect()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Recevoir la réponse de Facebook et ramener l'utilisateur vers l'app.
     */
    public function callback(Request $request)
    {
        // Annulation ou refus côté Facebook.
        if ($request->filled('error')) {
            return $this->backToApp(['error' => 'cancelled']);
        }

        try {
            $facebookUser = Socialite::driver('facebook')->user();
        } catch (InvalidStateException $e) {
            report($e);
            return $this->backToApp(['error' => 'invalid_state']);
        } catch (\Throwable $e) {
            report($e);
            return $this->backToApp(['error' => 'facebook_failed']);
        }

        $email = $facebookUser->getEmail();

        // Facebook peut ne pas fournir l'adresse email.
        if (!$email) {
            return $this->backToApp(['error' => 'email_missing']);
        }

        $code = Str::random(64);

        Cache::put(
            'facebook_oauth_code:' . hash('sha256', $code),
            [
                'facebook_id'   => $facebookUser->getId(),
                'email'         => $email,
                'first_name'    => $facebookUser->user['first_name'] ?? null,
                'last_name'     => $facebookUser->user['last_name'] ?? null,
                'profile_photo' => $facebookUser->getAvatar(),
            ],
            now()->addMinutes(5)
        );

        // Seul le code voyage dans l'URL, jamais les données du profil.
        return $this->backToApp(['code' => $code]);
    }
}
