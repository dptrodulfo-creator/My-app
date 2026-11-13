<?php

namespace App\Http\Controllers;

use App\Support\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Feature 2: the zone picker. The pick is saved in the session only (nothing goes to the database),
 * so it lasts until sign-out wipes the session.
 */
class ZoneController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'zone' => ['required', Rule::in(Zone::all())],
        ]);

        $request->session()->put('zone', $data['zone']);

        return back()->with('status', "Zone switched to {$data['zone']}.");
    }
}
