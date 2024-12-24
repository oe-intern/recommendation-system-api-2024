<?php

namespace App\Http\Middleware;

use App\Lib\Utils;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthEventBridgeWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $incomingApiKey = $request->header('X-Api-Key');
        $expectedApiKey = Utils::getShopifyConfig('webhook_event_bridge_secret');

        if ($incomingApiKey !== $expectedApiKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
