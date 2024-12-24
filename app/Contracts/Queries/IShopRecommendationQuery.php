<?php

namespace App\Contracts\Queries;

use App\Collections\Schema\ShopRecommendationSchema;

interface IShopRecommendationQuery
{
    /**
     * Get the shop recommendations by shop ID.
     *
     * @param string $shopId
     * @return ShopRecommendationSchema
     */
    public function getByShopId(string $shopId): ShopRecommendationSchema;
}
