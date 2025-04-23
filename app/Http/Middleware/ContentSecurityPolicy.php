<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        $cspHeader = "default-src 'self'; " .
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com https://*.stripe.network; " .
                    "style-src 'self' 'unsafe-inline'; " .
                    "img-src 'self' data: https://*.stripe.com; " .
                    "font-src 'self'; " .
                    "connect-src 'self' https://*.stripe.com; " .
                    "frame-src 'self' https://*.stripe.com; " .
                    "object-src 'none'; " .
                    "base-uri 'self';";
        
        $response->headers->set('Content-Security-Policy', $cspHeader);
        
        return $response;
    }
}
