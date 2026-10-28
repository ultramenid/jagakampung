<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class frontendBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = (string) config('app.frontend_auth.user');
        $pass = (string) config('app.frontend_auth.password');

        if ($pass === '') {
            return $next($request);
        }

        if (
            !hash_equals($user, (string) $request->getUser()) ||
            !hash_equals($pass, (string) $request->getPassword())
        ) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Jaga Kampung", charset="UTF-8"',
            ]);
        }

        return $next($request);
    }
}
