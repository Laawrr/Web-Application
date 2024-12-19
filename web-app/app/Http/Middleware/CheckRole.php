<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Check if the user is authenticated
        if (auth()->guest()) {
            return redirect()->route('login');
        }

        // If the user has the 'admin' role, block access to non-admin routes like dashboard or profile
        if (auth()->user()->role === 'admin') {
            // Check if the user is trying to access dashboard or profile and redirect accordingly
            if ($request->is('dashboard') || $request->is('profile')) {
                return redirect('/admin');  // Redirect to the admin dashboard
            }
        }

        // Check if the authenticated user has the required role
        if (auth()->user()->role !== $role) {
            return redirect('/');  // Or to a specific page
        }

        return $next($request);
    }
}
