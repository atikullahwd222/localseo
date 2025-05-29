<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    protected function authenticate($request, array $guards)
    {
        parent::authenticate($request, $guards);
        
        // After successful authentication, check if the user is active
        if ($request->user() && $request->user()->status !== 'active') {
            auth()->logout();
            abort(403, 'Your account is not active. Please contact an administrator.');
        }
    }
}
