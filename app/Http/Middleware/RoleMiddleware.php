<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user || !in_array(strtolower((string) $user->role), array_map('strtolower', $roles), true)) {
            abort($user ? 403 : 401);
        }
        return $next($request);
    }
}
