<?php

declare(strict_types=1);

namespace App\Lib\Handlers;

use App\Contracts\Queries\User as UserQuery;
use App\Contracts\Queries\IShopQuery;
use App\Exceptions\ShopNotFoundException;
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
     * The shop query.
     *
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * BaseShopHandler constructor.
     *
     * @param UserQuery $user_query
     * @param IShopQuery $shop_query
     */
    public function __construct(UserQuery $user_query, IShopQuery $shop_query)
    {
        $this->user_query = $user_query;
        $this->shop_query = $shop_query;
    }

    /**
     * Handle the shop data.
     *
     * @param string $topic
     * @param string $shop
     * @param array $body
     * @return void
     *
     * @throws ShopNotFoundException
     */
    public function handle(string $topic, string $shop, array $body): void
    {
        $user = $this->getUserFromShop($shop);

        if (!$user) {
            return;
        }

        $shop_id = $this->getShopId($shop);
        $this->processData($shop_id, $body);
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
     * Get the shop id.
     *
     * @throws ShopNotFoundException
     */
    private function getShopId(string $shop_domain): string
    {
        return $this->shop_query->getShopIdByDomain($shop_domain);
    }

    /**
     * Process the data after checking the user.
     *
     * @param string $shop_id
     * @param array $body
     * @return void
     */
    abstract protected function processData(string $shop_id, array $body): void;
}
