<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserOnly
{
    /**
     * Middleware to restrict access to user-only routes.
     * Admins and super admins should not be able to access user-specific functionality.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // If user is admin or super_admin, block access to user-only routes
        if ($user->isAdmin()) {
            abort(403, 'This page is only accessible to regular users. Please use the admin panel instead.');
        }

        return $next($request);
    }
}
