<?php

namespace App\Lib;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Redirect;
use Shopify\Auth\OAuth;
use Shopify\Context;
use Shopify\Exception\CookieSetException;
use Shopify\Exception\PrivateAppException;
use Shopify\Exception\SessionStorageException;
use Shopify\Exception\UninitializedContextException;
use Shopify\Utils;

class AuthRedirection
{
    /**
     * Redirects to the Shopify OAuth page.
     *
     * @param Request $request
     * @param bool $isOnline
     * @return ResponseFactory|Application|RedirectResponse|Response|Redirector
     * @throws CookieSetException
     * @throws PrivateAppException
     * @throws SessionStorageException
     * @throws UninitializedContextException
     */
    public static function redirect(Request $request, bool $isOnline = false)
    {
        $shop = Utils::sanitizeShopDomain($request->query("shop"));

        if (Context::$IS_EMBEDDED_APP && $request->query("embedded", false) === "1") {
            return self::clientSideRedirectUrl($shop);
        } else {
            $redirect_url = self::serverSideRedirectUrl($shop, $isOnline);
        }

        return redirect($redirect_url);
    }

    /**
     * Server-side redirect URL.
     *
     * @param string $shop
     * @param bool $isOnline
     * @return string
     * @throws CookieSetException
     * @throws PrivateAppException
     * @throws SessionStorageException
     * @throws UninitializedContextException
     */
    private static function serverSideRedirectUrl(string $shop, bool $isOnline): string
    {
        return OAuth::begin(
            $shop,
            '/authenticate',
            $isOnline,
            ['App\Lib\CookieHandler', 'saveShopifyCookie'],
        );
    }

    /**
     * Client-side redirect URL.
     *
     * @param $shop
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    private static function clientSideRedirectUrl($shop)
    {
        $redirectUri = "auth?shop=$shop";
        $redirectTo = Redirect::to($redirectUri);

        return response(view('exit_iframe', [
            'redirect_url' => $redirectTo->getTargetUrl(),
        ]));
    }
}
