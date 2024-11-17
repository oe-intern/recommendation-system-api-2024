<?php

namespace App\Actions;

use App\Contracts\Commands\User as UserCommand;
use App\Contracts\Queries\User as UserQuery;
use App\Objects\Values\AccessToken;
use App\Objects\Values\NullAccessToken;
use App\Objects\Values\UserDomain;
use App\Objects\Values\UserId;
use Shopify\Auth\Session;
use App\Jobs\ProcessShopInstalledData;

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
     * InstallShop constructor.
     */
    public function __construct(UserQuery $user_query, UserCommand $user_command)
    {
        $this->user_query = $user_query;
        $this->user_command = $user_command;
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
        }

        if ($user->trashed()) {
            $user->restore();
            $this->user_command->setAccessToken($user->getId(), AccessToken::fromNative($session->getAccessToken()));
        }

        ProcessShopInstalledData::dispatch($domain->toNative());

        return $user->getId();
    }
}
