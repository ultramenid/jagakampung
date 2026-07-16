<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class adminSession
{
    /**
     * Restrict a route to administrators (role 0).
     * ponytail: enforce login + role here too, not just role, so an admin route
     * mounted outside the checkSession group can't be bypassed by an
     * unauthenticated request ((int)null === 0).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('id') || (int) session('role_id') !== 0) {
            return redirect('/cms/dashboard');
        }

        return $next($request);
    }
}