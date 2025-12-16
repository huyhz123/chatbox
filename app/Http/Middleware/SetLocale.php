<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->get('lang')
            ?? Session::get('locale')
            ?? $request->user()?->preferred_language
            ?? config('app.locale');

        if (in_array($locale, ['vi', 'en', 'zh'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);
        }

        return $next($request);
    }
}
