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

        // Finishing department staff only use the Finishing module.
        if ($request->user()->isFinishingDepartment() && ! $request->routeIs('finishing.*', 'api.finishing.*', 'index', 'logout')) {
            if ($request->expectsJson() || ! $request->isMethod('GET')) {
                abort(403, 'แผนกฟินิชชิ่งใช้งานได้เฉพาะเมนู Finishing');
            }

            return redirect()->route('finishing.overview');
        }

        return $next($request);
    }
}
