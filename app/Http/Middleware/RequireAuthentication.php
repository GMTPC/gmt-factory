<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs([
            'login',
            'login.store',
            'login-cover',
            'login-illustration',
            'forgot-password',
            'forgot-password-cover',
            'forgot-password-illustration',
            'reset-password',
            'reset-password-cover',
            'reset-password-illustration',
            'register',
            'register.store',
            'register-cover',
            'register-illustration',
            'email-verification',
            'email-verification-cover',
            'email-verification-illustration',
        ])) {
            return $next($request);
        }

        if (! $request->user()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
