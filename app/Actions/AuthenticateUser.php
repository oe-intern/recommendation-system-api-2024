<?php

namespace App\Actions;

use App\Objects\Values\UserDomain;
use App\Services\Shopify\UserContext;
use Illuminate\Http\Request;
use Shopify\Auth\OAuth;
use App\Models\User;

class AuthenticateUser
{
    /**
     * @var User
     */
    protected User $user;

    /**
     * @var InstallShop
     */
    protected InstallShop $installShop;

    /**
     * @var AfterAuthorize
     */
    protected AfterAuthorize $afterAuthorize;

    /**
     * Create a new action instance.
     */
    public function __construct(
        InstallShop $installShop,
        AfterAuthorize $afterAuthorize,

    ) {
        $this->installShop = $installShop;
        $this->afterAuthorize = $afterAuthorize;
    }

    /**
     * Execute the action.
     *
     * @param Request $request
     * @return void
     */
    public function __invoke(Request $request): void
    {
        $session = OAuth::callback(
            $request->cookie(),
            $request->query(),
            ['App\Lib\CookieHandler', 'saveShopifyCookie'],
        );

        $domain = UserDomain::fromNative($request->query('shop'));
        $userContext = app(UserContext::class);
        $userContext->setShopifySession($session);

        // Install the shop
        $userId = call_user_func(
            $this->installShop,
            $domain,
            $session
        );

        call_user_func($this->afterAuthorize, $userId);
    }
}
