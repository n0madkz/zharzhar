<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BrokerDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('broker', 'broker/*')) {
            $local = app()->environment(['local', 'testing']) && in_array($request->getHost(), ['localhost', '127.0.0.1']);
            abort_unless($local || $request->getHost() === config('store.broker_domain'), 404);
            if ($request->user()) {
                abort_if($request->user()->status === 'inactive', 403);
            }
        }
        $response = $next($request);
        if ($request->is('broker', 'broker/*')) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            $response->headers->set('Referrer-Policy', 'no-referrer');
        }

        return $response;
    }
}
