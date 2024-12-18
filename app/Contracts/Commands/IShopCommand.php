<?php

namespace App\Contracts\Commands;

use App\Collections\ShopCollection;
use App\Objects\Enums\RecommendationState;

interface IShopCommand
{
    /**
     * Create a shop.
     *
     * @param string $shop_domain
     * @return ShopCollection
     */
    public function create(string $shop_domain): ShopCollection;

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
     * @param string $shop_id
     * @param RecommendationState $state
     * @return bool
     */
    public function setRecommendationState(string $shop_id, RecommendationState $state): bool;

    /**
     * Update the last job recommendation by shop id.
     *
     * @param string $shop_id
     * @param string $job_recommendation_id
     *
     * @return bool
     */
    public function updateLastJobRecommendation(string $shop_id, string $job_recommendation_id): bool;
}
