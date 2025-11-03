<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            // Check if login route exists, otherwise return 401
            if (Route::has('login')) {
                return redirect()->route('login');
            }
            // If no login route, return 401 Unauthorized
            abort(401, 'Unauthorized. Please login to access this page.');
        }

        return $next($request);
    }
}

