<?php

namespace App\Http\Middleware;

use App\Objects\Values\SessionToken;
use Closure;
use Illuminate\Http\Request;
use Shopify\Context;
use Symfony\Component\HttpFoundation\Response;

class VerifyAuthenticationToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $shopSession = $request->query('token') ?? $request->query('id_token');
        $token = $this->isApiRequest($request) ? $request->bearerToken() : $shopSession;

        if (Context::$IS_EMBEDDED_APP) {
            SessionToken::fromNative($token);
        }

        return $next($request);
    }

    /**
     * Determine if the request is AJAX or expects JSON.
     *
     * @param Request $request The request object.
     *
     * @return bool
     */
    protected function isApiRequest(Request $request): bool
    {
        return $request->ajax() || $request->expectsJson();
    }
}
