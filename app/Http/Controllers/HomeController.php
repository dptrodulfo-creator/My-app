<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Sends each role to its own landing page.
 */
class HomeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        // Keep any flash message alive across this extra redirect.
        $request->session()->reflash();

        return $request->user()->isDispatcher()
            ? redirect()->route('desk.index')
            : redirect()->route('runsheet.index');
    }
}
