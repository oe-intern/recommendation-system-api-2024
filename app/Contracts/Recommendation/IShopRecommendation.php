<?php

namespace App\Contracts\Recommendation;

use App\Exceptions\JobRecommendationRunningException;
use App\Exceptions\RecommendationRefreshLimitException;

interface IShopRecommendation
{
    /**
     * Refresh recommendations for a shop.
     *
     * @param string $shop_id
     * @param string $shop_domain
     * @return void
     *
     * @throws RecommendationRefreshLimitException
     * @throws JobRecommendationRunningException
     */
    public function refreshRecommendations(string $shop_id, string $shop_domain): void;

    /**
     * Cancel recommendations for a shop.
     *
     * @param string $shop_id
     * @return void
     */
    public function cancelRecommendations(string $shop_id): void;

    /**
     * Get the processing status of a shop.
     *
     * @param string $shop_id
     * @return array
     */
    public function getProcessingStatus(string $shop_id): array;

    /**
     * Get the recommendations for a shop.
     *
     * @param string $shop_id
     * @return array
     */
    public function getShopRecommendations(string $shop_id): array;

    /**
     * Update the shop recommendation notification.
     *
     * @param string $shop_id
     * @param array $notification_data
     * @return array
     */
    public function updateShopRecommendationNotification(string $shop_id, array $notification_data): array;
}
