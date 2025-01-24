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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next , ...$roles): Response
    // {
    //     if (!auth()->check()) {
    //         return response()->json(['message' => 'Unauthenticated'], 401);
    //     }

    //     if (in_array(auth()->user()->role, $roles)) {
    //         return $next($request);
    //     }
    //     \Log::info('User role check:', [
    //         'user_role' => auth()->user()->role,
    //         'required_roles' => $roles
    //     ]);
    //     return response()->json(['message' => 'Unauthorized'], 403);
    // }
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (auth()->user() && auth()->user()->role === $role) {
            return $next($request);
        }
        \Log::info('User role check:', [
            'user_role' => auth()->user()->role,
            'required_roles' => $role
        ]);
        return response()->json(['message' => 'Unauthorized'], 403);
    }

}
