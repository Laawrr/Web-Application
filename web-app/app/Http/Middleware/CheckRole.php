<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Assuming you have a 'role' column in your 'users' table
        if (auth()->user()->role !== $role) {
            // If the user is not the admin, redirect them back
            return redirect('/'); // Or to a specific page, like the dashboard
        }

        return $next($request);
    }
}
