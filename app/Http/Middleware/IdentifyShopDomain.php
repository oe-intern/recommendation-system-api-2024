<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Shopify\UserContext;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopify\Utils;

class IdentifyShopDomain
{
    /**
     * @var UserContext
     */
    protected UserContext $userContext;

    /**
     * @param UserContext $userContext
     */
    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $shopDomain = Utils::sanitizeShopDomain($request->header('origin'));
        $shopSession = User::query()->where('name', $shopDomain)->first();

        if (!$shopSession) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Shop not found'
            ], 401);
        }

        $this->userContext->setUser($shopSession);

        return $next($request);
    }
}
