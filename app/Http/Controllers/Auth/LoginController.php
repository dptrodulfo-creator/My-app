<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'That email and password don’t match a RunSheet account.'])
                ->onlyInput('email');
        }

        $account = Auth::user();

        // A dispatcher can put a driver's account on hold. Held accounts can't sign in.
        if ($account->isOnHold()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'This account is on hold. Please contact the dispatch desk.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate(); // new session id after sign-in
        $account->forceFill(['last_login_at' => now()])->save();

        $message = "Signed in. Good to see you, {$account->name}.";

        // Accounts that haven't clicked their email link yet go to the "check your inbox" page.
        if (! $account->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')->with('status', $message);
        }

        return redirect()->intended(route('home'))->with('status', $message);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate(); // wipes the whole session, including the zone pick
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Signed out. Drive safe!');
    }
}
