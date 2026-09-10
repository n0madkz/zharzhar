<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPartnerLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->preferred_language;
        $locale = in_array($locale, ['kk', 'ru', 'en'], true) ? $locale : 'kk';

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
