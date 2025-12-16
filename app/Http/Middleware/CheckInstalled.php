<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if install lock file exists
        if (file_exists(storage_path('installed'))) {
            // If accessing installer routes, redirect to home
            if ($request->is('install*')) {
                return redirect('/')->with('error', 'Application is already installed.');
            }
        } else {
            // If not installed and not accessing installer, redirect to installer
            if (!$request->is('install*')) {
                return redirect()->route('installer.welcome');
            }
        }

        return $next($request);
    }
}
