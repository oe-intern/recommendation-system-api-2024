<?php

namespace App\Http\Middleware;

use App\Exceptions\MissingShopDomainException;
use App\Lib\AuthRedirection;
use App\Models\ShopifySession;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Shopify\Exception\CookieSetException;
use Shopify\Exception\PrivateAppException;
use Shopify\Exception\SessionStorageException;
use Shopify\Exception\UninitializedContextException;
use Shopify\Utils;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopifyInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure(Request): (Response) $next
     * @return Response
     * @throws CookieSetException
     * @throws PrivateAppException
     * @throws SessionStorageException
     * @throws UninitializedContextException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $myshopifyDomain = $request->query('shop');

        if (!$myshopifyDomain) {
            throw new MissingShopDomainException;
        }

        $myshopifyDomain = $myshopifyDomain ? Utils::sanitizeShopDomain($myshopifyDomain) : null;
        $shopifySession = ShopifySession::where('shop', $myshopifyDomain)->whereNotNull('access_token')->first();
        $userExisted = User::where('myshopify_domain', $myshopifyDomain)->exists();

        if (!$shopifySession || !$userExisted || !$shopifySession->isValid()) {
            return AuthRedirection::redirect($request);
        }

        return $next($request);
    }
}
