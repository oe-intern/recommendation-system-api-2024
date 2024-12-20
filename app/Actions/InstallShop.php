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
    protected UserQuery $user_query;

    /**
     * @var UserCommand
     */
    protected UserCommand $user_command;

    /**
     * @var ShopInstalledData
     */
    protected ShopInstalledData $shop_installed_data;

    /**
     * InstallShop constructor.
     */
    public function __construct(UserQuery $user_query, UserCommand $user_command, ShopInstalledData $shop_installed_data)
    {
        $this->user_query = $user_query;
        $this->user_command = $user_command;
        $this->shop_installed_data = $shop_installed_data;
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
        $user = $this->user_query->getByDomain($domain, [], true);
        if ($user === null) {
            $this->user_command->make($domain, AccessToken::fromNative($session->getAccessToken()));
            $user = $this->user_query->getByDomain($domain);
            $this->installShopData($domain, false);
        }

        if ($user->trashed()) {
            $user->restore();
            $this->user_command->setAccessToken($user->getId(), AccessToken::fromNative($session->getAccessToken()));
            $this->installShopData($domain, true);
        }

        return $user->getId();
    }

    /**
     * Install shop data
     *
     * @param UserDomain $domain
     * @param bool $is_trashed
     * @return void
     */
    private function installShopData(UserDomain $domain, bool $is_trashed): void
    {
        call_user_func(
            $this->shop_installed_data,
            $domain->toNative(),
            $is_trashed
        );
    }
}
