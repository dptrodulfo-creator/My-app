<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Account moderation: if a dispatcher puts a driver on hold, that driver is
 * signed out on their very next click instead of staying signed in.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->user();

        if ($account && $account->isOnHold()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'This account is on hold. Please contact the dispatch desk.']);
        }

        return $next($request);
    }
}
