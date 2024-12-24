<?php

namespace App\Services\Shopify;

use App\Contracts\Objects\Values\AccessToken as IAccessToken;
use App\Models\User;
use App\Contracts\Queries\User as UserQuery;
use App\Objects\Values\AccessToken;
use App\Objects\Values\UserDomain;
use Shopify\Auth\Session;

class UserContext
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Session
     */
    private $shopifySession;

    /**
     * @var UserQuery
     */
    protected UserQuery $userQuery;

    /**
     * UserContext constructor.
     *
     * @param UserQuery $userQuery
     */
    public function __construct(UserQuery $userQuery)
    {
        $this->userQuery = $userQuery;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Get the user
     *
     * @return ?User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the Shopify session
     *
     * @param Session $shopifySession
     */
    public function setShopifySession(Session $shopifySession): void
    {
        $this->shopifySession = $shopifySession;
    }

    /**
     * @return ?Session
     */
    public function getShopifySession(): ?Session
    {
        return $this->shopifySession;
    }

    /**
     * Get the access token
     *
     * @return IAccessToken
     */
    public function getAccessToken(): IAccessToken
    {
        if ($shopifySession = $this->getShopifySession()) {
            return AccessToken::fromNative($shopifySession->getAccessToken());
        }

        if ($user = $this->getUser()) {
            return $user->getAccessToken();
        }

        throw new \RuntimeException('No access token found.');
    }

    /**
     * Get the domain
     *
     * @return UserDomain
     */
    public function getDomain(): UserDomain
    {
        $domain = null;

        if ($user = $this->getUser()) {
            $domain = $user->name;
        }

        if ($this->getShopifySession()) {
            $domain = $this->shopifySession->getShop();
            $domain = UserDomain::fromNative($domain);
            $user = $this->userQuery->getByDomain($domain);

            $domain = $user->name;
        }

        if ($domain) {
            return UserDomain::fromNative($domain);
        }

        throw new \RuntimeException('No domain found.');
    }
}
