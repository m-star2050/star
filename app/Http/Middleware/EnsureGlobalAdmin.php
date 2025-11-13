<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureGlobalAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->get('global_admin.authenticated')) {
            if ($request->expectsJson()) {
                abort(401);
            }
            return redirect()->route('global.login');
        }

        return $next($request);
    }
}

