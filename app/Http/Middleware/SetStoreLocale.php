<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetStoreLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->getHost() === config('store.admin_domain')
            ? 'ru'
            : $request->session()->get('store_locale', 'kk');

        app()->setLocale(in_array($locale, ['kk', 'ru'], true) ? $locale : 'kk');

        return $next($request);
    }
}
