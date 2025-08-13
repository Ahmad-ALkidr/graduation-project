<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogBroadcastRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        \Log::debug('Broadcast Request', [
            'headers' => $request->headers->all(),
            'content' => $request->getContent()
        ]);

        return $next($request);
    }
}
