<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * Feature 5: password reset. The "Forgot your password?" link emails a one-time link.
 * Laravel stores a hashed token in the password_reset_tokens table and deletes it once used.
 */
class PasswordResetController extends Controller
{
    /** The "Forgot your password?" page. */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /** Emails the one-time link. */
    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withErrors(['email' => 'Easy there. Please wait a minute before asking for another link.'])
                ->onlyInput('email');
        }

        // Same answer whether or not the email has an account, so nobody can probe who is registered.
        return back()->with('status', 'If that email belongs to a RunSheet account, a reset link is on its way.');
    }

    /** The page the emailed link opens. */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** Saves the new password. The token is deleted when it works, so the link can't be reused. */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $account, string $password) {
                $account->forceFill([
                    'password' => $password, // hashed by the cast on the User model
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($account));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Password updated. Sign in with your new password.');
        }

        return back()
            ->withErrors(['email' => 'That reset link is invalid or has expired. Please ask for a new one.'])
            ->onlyInput('email');
    }
}
