<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Feature 3: role check.
 * Used in routes as EnsureRole::class.':dispatcher'. Anyone whose role does not match
 * gets the 403 page, even when they type the address by hand.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        abort_unless($request->user()?->role === $role, 403);

        return $next($request);
    }
}
