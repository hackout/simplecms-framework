<?php

namespace SimpleCMS\Framework\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LoadLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$locale = $request->header('X-Accept-Language')) {
            $locale = $request->header('Accept-Language',config('app.locale'));
        }
        App::setLocale($locale);
        return $next($request);
    }
}