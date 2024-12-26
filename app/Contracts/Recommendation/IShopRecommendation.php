<?php

namespace App\Contracts\Recommendation;

use App\DTO\Request\UpdateNotificationSettingsRequestDTO;
use App\Exceptions\JobRecommendationRunningException;
use App\Exceptions\RecommendationRefreshLimitException;

interface IShopRecommendation
{
    /**
     * Refresh recommendations for a shop.
     *
     * @param string $shopId
     * @param string $shopDomain
     * @return void
     *
     * @throws RecommendationRefreshLimitException
     * @throws JobRecommendationRunningException
     */
    public function refreshRecommendations(string $shopId, string $shopDomain): void;

    /**
     * Cancel recommendations for a shop.
     *
     * @param string $shopId
     * @return void
     */
    public function cancelRecommendations(string $shopId): void;

    /**
     * Get the processing status of a shop.
     *
     * @param string $shopId
     * @return array
     */
    public function getProcessingStatus(string $shopId): array;

    /**
     * Get the recommendations for a shop.
     *
     * @param string $shopId
     * @return array
     */
    public function getShopRecommendations(string $shopId): array;

    /**
     * Update the shop recommendation notification.
     *
     * @param string $shopId
     * @param UpdateNotificationSettingsRequestDTO $requestDTO
     * @return array
     */
    public function updateShopRecommendationNotification(string $shopId, UpdateNotificationSettingsRequestDTO $requestDTO): array;
}
