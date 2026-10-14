<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Admin has universal override access across all departments
        if ($request->user()->isAdmin()) {
            return $next($request);
        }

        if (in_array($request->user()->role, $roles, true)) {
            return $next($request);
        }

        abort(403, 'Unauthorized. You do not have permissions to access this hospital department.');
    }
}
