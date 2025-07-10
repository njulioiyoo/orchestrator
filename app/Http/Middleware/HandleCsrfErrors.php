<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCsrfErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (\Illuminate\Session\TokenMismatchException $exception) {
            // If it's a CSRF token mismatch on login page, redirect to fresh login
            if ($request->is('login')) {
                return redirect()->route('login')->with('error', 'Session expired. Please try again.');
            }
            
            // For other pages, throw the exception normally
            throw $exception;
        }
    }
}
