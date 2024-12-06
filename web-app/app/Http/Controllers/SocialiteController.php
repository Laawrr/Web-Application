<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Function: google login
     * description: this function will redirect to google
     * @param NA
     * @return void
     */
    public function googleLogin()
    {
        try {
            return Socialite::driver('google')
                ->stateless()
                ->with([
                    'prompt' => 'select_account',
                    'access_type' => 'offline'
                ])
                ->redirect();
        } catch (Exception $e) {
            \Log::error('Google Login Error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['error' => 'Unable to connect to Google. Please try again.']);
        }
    }

    /**
     * Handle Google callback.
     */
    public function googleAuthentication()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(24))
                ]
            );

            Auth::login($user);

            return redirect()->route('dashboard');
        } catch (Exception $e) {
            \Log::error('Google Authentication Error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['error' => 'Google authentication failed. Please try again.']);
        }
    }
}
