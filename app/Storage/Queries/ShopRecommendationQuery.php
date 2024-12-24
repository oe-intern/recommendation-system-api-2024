<?php

namespace App\Storage\Queries;

use App\Collections\Schema\ShopRecommendationSchema;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Queries\IShopRecommendationQuery;

class ShopRecommendationQuery implements IShopRecommendationQuery
{
    /**
     * @var IShopQuery
     */
    protected IShopQuery $shopQuery;

    /**
     * ShopRecommendationQuery constructor.
     *
     * @param IShopQuery $shopQuery
     */
    public function __construct(IShopQuery $shopQuery)
    {
        $this->shopQuery = $shopQuery;
    }

    /**
     * Get the shop recommendations by shop ID.
     *
     * @param string $shopId
     * @return ShopRecommendationSchema
     */
    public function getByShopId(string $shopId): ShopRecommendationSchema
    {
        $shop = $this->shopQuery->getById($shopId);
        return $shop->shopRecommendation()->get();
    }
}
