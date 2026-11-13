<?php

namespace App\Http\Controllers\Desk;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Account monitoring and moderation: see every account, put a driver on hold, reinstate them.
 */
class AccountController extends Controller
{
    public function index(): View
    {
        return view('desk.accounts', [
            'accounts' => User::orderBy('role')->orderBy('name')->get(),
        ]);
    }

    public function hold(User $account): RedirectResponse
    {
        if ($account->isDispatcher()) {
            return back()->with('warning', 'Dispatcher accounts can’t be put on hold.');
        }

        $account->forceFill(['suspended_at' => now()])->save();

        return back()->with('status', "{$account->name}’s account is on hold. They are signed out on their next click.");
    }

    public function reinstate(User $account): RedirectResponse
    {
        $account->forceFill(['suspended_at' => null])->save();

        return back()->with('status', "{$account->name}’s account is active again.");
    }
}
