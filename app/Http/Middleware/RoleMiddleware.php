<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Eto kailangan natin para ma-check ang user

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Check kung may naka-login ba na user
        if (!Auth::check()) {
            return redirect('/login');
        }

        // 2. Check kung yung role ng nakalogin na user ay allowed sa route
        if (in_array(Auth::user()->role, $roles)) {
            return $next($request);
        }

        // 3. Kung bawal sila dun sa page, ibabato natin sila sa 403 (Forbidden) page
        abort(403, 'Unauthorized action. Wala kang access sa page na ito.');
    }
}