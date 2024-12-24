<?php

declare(strict_types=1);

namespace App\Lib\Handlers;

use App\Objects\Values\UserDomain;
use Shopify\Webhooks\Handler;
use App\Contracts\Queries\User as UserQuery;

class ShopUpdate implements Handler
{
    /**
     * @var UserQuery
     */
    protected UserQuery $userQuery;

    /**
     * Shop Update constructor.
     */
    public function __construct(UserQuery $userQuery)
    {
        $this->userQuery = $userQuery;
    }

    /**
     * @inheritdoc
     */
    public function handle(string $topic, string $shop, array $body): void
    {
        $userDomain = UserDomain::fromNative($shop);
        $user = $this->userQuery->getByDomain($userDomain);

        if (!$user) {
            return;
        }

        $data = $body;
        $data['name'] = $userDomain->toNative();
        $data['email'] =  "shop@{$userDomain->toNative()}";
        $data['shop_name'] = data_get($body, 'name');
        $data['shop_email'] = data_get($body, 'email');
        $data['shop_id'] = data_get($body, 'id');

        $user->update($data);
    }
}
