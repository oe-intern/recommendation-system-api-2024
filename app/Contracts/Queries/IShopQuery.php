<?php

namespace App\Contracts\Queries;

use App\Collections\ShopCollection;

interface IShopQuery
{
    /**
     * Get a shop by domain.
     *
     * @param string $shop_domain
     * @return ShopCollection|null
     */
    public function getByDomain(string $shop_domain): ?ShopCollection;

    /**
     * Get the auto recommendation settings for a shop.
     *
     * @param ShopCollection $shop
     * @return array
     */
    public function getAutoRecommendationSettings(ShopCollection $shop): array;
}
