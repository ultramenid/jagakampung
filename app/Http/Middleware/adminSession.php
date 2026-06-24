<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class adminSession
{
    /**
     * Restrict a route to administrators (role 0).
     * ponytail: assumes checkSession already ran on the parent route group,
     * so we only enforce the role here.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ((int) session('role_id') !== 0) {
            return redirect('/cms/dashboard');
        }

        return $next($request);
    }
}