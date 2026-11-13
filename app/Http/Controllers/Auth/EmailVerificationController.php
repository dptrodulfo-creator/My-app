<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Feature 4: email verification. Accounts must click the emailed link before
 * they can use any page behind the "verified" middleware.
 */
class EmailVerificationController extends Controller
{
    /** The "check your inbox" page. */
    public function notice(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('home')
            : view('auth.verify-email');
    }

    /** The emailed link lands here. EmailVerificationRequest checks the id and hash in the link. */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill(); // marks the email as verified

        return redirect()->route('home')->with('status', 'Email confirmed. Welcome to RunSheet!');
    }

    /** "Send it again" button. */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A fresh confirmation link is on its way to your inbox.');
    }
}
