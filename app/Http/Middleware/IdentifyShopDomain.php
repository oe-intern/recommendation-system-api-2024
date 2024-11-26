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
    protected UserContext $user_context;

    /**
     * @param UserContext $user_context
     */
    public function __construct(UserContext $user_context)
    {
        $this->user_context = $user_context;
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
        $shop_domain = Utils::sanitizeShopDomain($request->header('origin'));
        $shop_session = User::query()->where('name', $shop_domain)->first();

        if (!$shop_session) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Shop not found'
            ], 401);
        }

        $this->user_context->setUser($shop_session);

        return $next($request);
    }
}
