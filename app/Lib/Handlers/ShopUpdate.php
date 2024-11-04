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
    protected UserQuery $user_query;

    /**
     * Shop Update constructor.
     */
    public function __construct(UserQuery $user_query)
    {
        $this->user_query = $user_query;
    }

    /**
     * @inheritdoc
     */
    public function handle(string $topic, string $shop, array $body): void
    {
        $user_domain = UserDomain::fromNative($shop);
        $user = $this->user_query->getByDomain($user_domain);

        if (!$user) {
            return;
        }

        $data = $body;
        $data['name'] = $user_domain->toNative();
        $data['email'] =  "shop@{$user_domain->toNative()}";
        $data['shop_name'] = data_get($body, 'name');
        $data['shop_email'] = data_get($body, 'email');
        $data['shop_id'] = data_get($body, 'id');

        $user->update($data);
    }
}
