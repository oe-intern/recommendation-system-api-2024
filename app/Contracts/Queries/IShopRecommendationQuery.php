<?php

namespace App\Contracts\Queries;

use App\Collections\Schema\ShopRecommendationSchema;

interface IShopRecommendationQuery
{
    /**
     * Get the shop recommendations by shop ID.
     *
     * @param string $shop_id
     * @return ShopRecommendationSchema
     */
    public function getByShopId(string $shop_id): ShopRecommendationSchema;
}
