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
            return $this->createNewUser($domain, $session);
        }

        if ($user->trashed()) {
            return $this->restoreUser($user, $session, $domain);
        }

        return $user->getId();
    }

    /**
     * Create a new user
     *
     * @param UserDomain $domain
     * @param Session $session
     * @return UserId
     */
    private function createNewUser(UserDomain $domain, Session $session): UserId
    {
        $this->userCommand->make($domain, AccessToken::fromNative($session->getAccessToken()));
        $user = $this->userQuery->getByDomain($domain);

        $this->processShopData($domain, false);

        return $user->getId();
    }

    /**
     * Restore user from trashed
     *
     * @param $user
     * @param Session $session
     * @param UserDomain $domain
     * @return UserId
     */
    private function restoreUser($user, Session $session, UserDomain $domain): UserId
    {
        $user->restore();
        $this->userCommand->setAccessToken($user->getId(), AccessToken::fromNative($session->getAccessToken()));

        $this->processShopData($domain, true);

        return $user->getId();
    }

    /**
     * Process shop data to be installed
     *
     * @param UserDomain $domain
     * @param bool $isTrashed
     * @return void
     */
    private function processShopData(UserDomain $domain, bool $isTrashed): void
    {
        call_user_func(
            $this->shopInstalledData,
            $domain->toNative(),
            $isTrashed,
        );
    }
}
