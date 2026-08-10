<?php

namespace Modules\Admin\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect to Google/GitHub for authentication.
     */
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from Google/GitHub.
     */
    public function callback(string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                    'email_verified_at' => now(),
                    'avatar' => $this->getAvatar($socialUser, $provider),
                    'is_active' => true,
                ]
            );

            // Check if user is admin
            if (!$user->is_admin) {
                return redirect()->route('admin.login')->withErrors([
                    'credential' => 'شما دسترسی به پنل ادمین را ندارید.',
                ]);
            }

            // Login the user
            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Socialite error: ' . $e->getMessage());
            return redirect()->route('admin.login')->withErrors([
                'credential' => 'خطایی در ورود با ' . $provider . ' رخ داد.',
            ]);
        }
    }

    /**
     * Get avatar from social provider.
     */
    protected function getAvatar($socialUser, string $provider): ?string
    {
        try {
            $avatarUrl = $socialUser->getAvatar();
            if ($avatarUrl) {
                // Download and store avatar
                $avatarContent = file_get_contents($avatarUrl);
                $extension = pathinfo(parse_url($avatarUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename = time() . '_' . $provider . '.' . $extension;
                \Illuminate\Support\Facades\Storage::disk('public')->put('avatars/' . $filename, $avatarContent);
                return $filename;
            }
        } catch (\Exception $e) {
            // Fallback to default avatar
        }
        return null;
    }
}