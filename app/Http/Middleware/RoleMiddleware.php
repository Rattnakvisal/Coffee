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

        if (! $user || ! $user->hasRole(...$roles)) {
            abort(403, 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
