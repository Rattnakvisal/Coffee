<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if ($roles === []) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user) {
            abort(403, 'You are not authorized to access this page.');
        }

        if (! $user->hasRole(...$roles)) {
            if (in_array('admin', $roles, true) && $user->hasRole('cashier')) {
                return redirect()
                    ->route('cashier.index')
                    ->with('status', 'Cashier accounts cannot access admin dashboard.');
            }

            if (in_array('cashier', $roles, true) && $user->hasRole('admin')) {
                return redirect()
                    ->route('admin.index')
                    ->with('status', 'Admin accounts cannot access cashier workspace.');
            }

            abort(403, 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
