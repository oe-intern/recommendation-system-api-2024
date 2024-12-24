<?php

declare(strict_types=1);

namespace App\Lib\Handlers;

use App\Models\ShopifySession;
use App\Objects\Values\UserDomain;
use Shopify\Webhooks\Handler;
use App\Contracts\Queries\User as UserQuery;
use App\Contracts\Commands\User as UserCommand;

class AppUninstalled implements Handler
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
     * Create a new handler instance.
     */
    public function __construct(UserQuery $userQuery, UserCommand $userCommand)
    {
        $this->userQuery = $userQuery;
        $this->userCommand = $userCommand;
    }

    /**
     * Handle the incoming webhook.
     *
     * @param string $topic
     * @param string $shop
     * @param array $body
     * @return void
     */
    public function handle(string $topic, string $shop, array $body): void
    {
        ShopifySession::where('shop', $shop)->delete();

        $user = $this->userQuery->getByDomain(UserDomain::fromNative($shop));

        if(!$user) {
            return;
        }

        $this->userCommand->softDelete($user->getId());
    }
}
