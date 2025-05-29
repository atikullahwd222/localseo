<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Check if user is active
        if (!$request->user()->isActive()) {
            // Log the user out
            auth()->logout();
            
            // Redirect with message
            return redirect()->route('login')
                ->with('error', 'Your account is not active. Please contact an administrator.');
        }

        // Check if no specific roles are required or user is admin (bypass)
        if (empty($roles) || $request->user()->isAdmin()) {
            return $next($request);
        }

        // Check if user has one of the required roles
        foreach ($roles as $role) {
            $checkMethod = 'is' . ucfirst($role);
            
            if (method_exists($request->user(), $checkMethod) && $request->user()->$checkMethod()) {
                return $next($request);
            }
        }

        // If user doesn't have any of the required roles
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized. Insufficient permissions.'], 403);
        }

        return redirect()->route('dashboard')
            ->with('error', 'You do not have permission to access this resource.');
    }
}
