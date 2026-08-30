<?php

namespace SimpleCMS\Framework\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string $role
     * @return mixed
     *
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();
        if (!$user->hasRole($role)) {
            return $request->user()->failRedirect($request);
        }
        return $next($request);
    }
}