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
    protected UserQuery $userQuery;

    /**
     * The shop query.
     *
     * @var IShopQuery
     */
    protected IShopQuery $shopQuery;

    /**
     * BaseShopHandler constructor.
     *
     * @param UserQuery $userQuery
     * @param IShopQuery $shopQuery
     */
    public function __construct(UserQuery $userQuery, IShopQuery $shopQuery)
    {
        $this->userQuery = $userQuery;
        $this->shopQuery = $shopQuery;
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

        $shopId = $this->getShopId($shop);
        $this->processData($shopId, $body);
    }

    /**
     * Get user from shop.
     *
     * @param string $shop
     * @return UserModel|null
     */
    public function getUserFromShop(string $shop): ?UserModel
    {
        $userDomain = UserDomain::fromNative($shop);
        return $this->userQuery->getByDomain($userDomain);
    }

    /**
     * Get the shop id.
     *
     * @throws ShopNotFoundException
     */
    private function getShopId(string $shopDomain): string
    {
        return $this->shopQuery->getShopIdByDomain($shopDomain);
    }

    /**
     * Process the data after checking the user.
     *
     * @param string $shopId
     * @param array $body
     * @return void
     */
    abstract protected function processData(string $shopId, array $body): void;
}
