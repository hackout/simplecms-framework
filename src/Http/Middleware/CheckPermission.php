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
            if ($request->ajax()) {
                return json_permission();
            } else {
                if (method_exists($request->user(), 'failRedirect')) {
                    return $request->user()->failRedirect();
                } else {
                    return abort(401);
                }
            }
        }
        return $next($request);
    }
}