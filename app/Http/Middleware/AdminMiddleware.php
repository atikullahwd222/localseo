<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied. Only administrators can access this resource.'
                ], 403);
            }
            
            // For non-AJAX requests, redirect to dashboard with error message
            return redirect()->route('dashboard')->with('error', 'Permission denied. Admin access required.');
        }
        
        return $next($request);
    }
}
