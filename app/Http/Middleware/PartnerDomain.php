<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PartnerDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('restaurant', 'restaurant/*')) {
            $local = app()->environment(['local', 'testing']) && in_array($request->getHost(), ['localhost', '127.0.0.1']);
            abort_unless($local || $request->getHost() === config('store.partner_domain'), 404);
        }

        $response = $next($request);
        if ($request->is('restaurant', 'restaurant/*')) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
