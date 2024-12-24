<?php

namespace App\Contracts\Commands;

use App\Collections\ShopCollection;
use App\Objects\Enums\RecommendationState;

interface IShopSettingCommand
{
    /**
     * Set the auto recommendation settings for a shop.
     *
     * @param ShopCollection $shop
     * @param array $settings
     * @return array
     */
    public function setShopSettings(ShopCollection $shop, array $settings): array;

    /**
     * Set the recommendation state for a shop.
     *
     * @param string $shopId
     * @param RecommendationState $state
     * @return bool
     */
    public function setRecommendationState(string $shopId, RecommendationState $state): bool;
}
