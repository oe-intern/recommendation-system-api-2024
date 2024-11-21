<?php

declare(strict_types=1);

namespace App\Lib\Handlers;

use App\Contracts\Queries\User as UserQuery;
use App\Models\User as UserModel;
use App\Objects\Values\UserDomain;
use Shopify\Webhooks\Handler;

abstract class BaseShopHandler implements Handler
{
    /**
     * The user query.
     *
     * @var UserQuery
     */
    protected UserQuery $user_query;

    /**
     * BaseShopHandler constructor.
     *
     * @param UserQuery $user_query
     */
    public function __construct(UserQuery $user_query)
    {
        $this->user_query = $user_query;
    }

    /**
     * Handle the shop data.
     *
     * @param string $topic
     * @param string $shop
     * @param array $body
     * @return void
     */
    public function handle(string $topic, string $shop, array $body): void
    {
        $user = $this->getUserFromShop($shop);

        if (!$user) {
            return;
        }

        $this->processData($shop, $body);
    }

    /**
     * Get user from shop.
     *
     * @param string $shop
     * @return UserModel|null
     */
    public function getUserFromShop(string $shop): ?UserModel
    {
        $user_domain = UserDomain::fromNative($shop);
        return $this->user_query->getByDomain($user_domain);
    }

    /**
     * Process the data after checking the user.
     *
     * @param string $shop_domain
     * @param array $body
     * @return void
     */
    abstract protected function processData(string $shop_domain, array $body): void;
}
