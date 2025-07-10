<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // If this is a login page request, ensure fresh CSRF token
        if ($request->routeIs('login') && $request->isMethod('GET')) {
            // Regenerate CSRF token to prevent stale token issues
            $request->session()->regenerateToken();
        }

        return $response;
    }
}
