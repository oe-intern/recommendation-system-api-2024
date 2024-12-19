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
    protected IShopQuery $shop_query;

    /**
     * ShopRecommendationQuery constructor.
     *
     * @param IShopQuery $shop_query
     */
    public function __construct(IShopQuery $shop_query)
    {
        $this->shop_query = $shop_query;
    }

    /**
     * Get the shop recommendations by shop ID.
     *
     * @param string $shop_id
     * @return ShopRecommendationSchema
     */
    public function getByShopId(string $shop_id): ShopRecommendationSchema
    {
        $shop = $this->shop_query->getById($shop_id);
        return $shop->shopRecommendation()->get();
    }
}
