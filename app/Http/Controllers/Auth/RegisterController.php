<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // Only these three fields are ever read from the form. There is no role field.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $account = new User($data);
        $account->role = User::ROLE_DRIVER; // Feature 3: the controller picks the role, never the form.
        $account->save();

        Auth::login($account);
        $request->session()->regenerate();

        // Feature 4: this event makes Laravel email the confirmation link.
        event(new Registered($account));

        return redirect()->route('verification.notice')
            ->with('status', "Welcome aboard, {$account->name}! We emailed you a link. Click it to unlock RunSheet.");
    }
}
