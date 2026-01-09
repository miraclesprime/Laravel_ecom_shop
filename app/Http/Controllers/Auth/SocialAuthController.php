<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to OAuth provider
     */
    public function redirect(string $provider)
    {
        if ($provider !== 'google') {
            abort(404);
        }

        // Ensure provider is configured before redirecting
        $service = config('services.' . $provider);
        if (
            empty($service['client_id']) ||
            empty($service['client_secret']) ||
            empty($service['redirect'])
        ) {
            $envPrefix = strtoupper($provider);

            return redirect()->route('login')
                ->with('status', sprintf(
                    '%s OAuth is not configured. Please set %s_CLIENT_ID, %s_CLIENT_SECRET, and %s_REDIRECT_URI in your .env.',
                    ucfirst($provider),
                    $envPrefix,
                    $envPrefix,
                    $envPrefix
                ));
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth provider callback
     */
    public function callback(string $provider)
    {
        if ($provider !== 'google') {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();

            $user = User::where('email', $socialUser->getEmail())->first();

            if (! $user) {
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user, remember: true);

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('status', 'Unable to login with ' . ucfirst($provider) . '. Please try again.');
        }
    }
}
