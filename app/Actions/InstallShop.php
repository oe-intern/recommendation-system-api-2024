<?php

namespace App\Actions;

use App\Contracts\Commands\User as UserCommand;
use App\Contracts\Queries\User as UserQuery;
use App\Objects\Values\AccessToken;
use App\Objects\Values\UserDomain;
use App\Objects\Values\UserId;
use Shopify\Auth\Session;

class InstallShop
{
    /**
     * @var UserQuery
     */
    protected UserQuery $userQuery;

    /**
     * @var UserCommand
     */
    protected UserCommand $userCommand;

    /**
     * @var ShopInstalledData
     */
    protected ShopInstalledData $shopInstalledData;

    /**
     * InstallShop constructor.
     */
    public function __construct(UserQuery $userQuery, UserCommand $userCommand, ShopInstalledData $shopInstalledData)
    {
        $this->userQuery = $userQuery;
        $this->userCommand = $userCommand;
        $this->shopInstalledData = $shopInstalledData;
    }

    /**
     * Execute the action
     *
     * @param UserDomain $domain
     * @param Session $session
     * @return UserId
     */
    public function __invoke(UserDomain $domain, Session $session): UserId
    {
        $user = $this->userQuery->getByDomain($domain, [], true);
        if ($user === null) {
            $this->userCommand->make($domain, AccessToken::fromNative($session->getAccessToken()));
            $user = $this->userQuery->getByDomain($domain);
            $this->installShopData($domain, false);
        }

        if ($user->trashed()) {
            $user->restore();
            $this->userCommand->setAccessToken($user->getId(), AccessToken::fromNative($session->getAccessToken()));
            $this->installShopData($domain, true);
        }

        return $user->getId();
    }

    /**
     * Install shop data
     *
     * @param UserDomain $domain
     * @param bool $isTrashed
     * @return void
     */
    private function installShopData(UserDomain $domain, bool $isTrashed): void
    {
        call_user_func(
            $this->shopInstalledData,
            $domain->toNative(),
            $isTrashed
        );
    }
}
