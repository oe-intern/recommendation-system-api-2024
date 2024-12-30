<?php

namespace App\Contracts\Recommendation;

use App\DTO\Request\UpdateNotificationSettingsRequestDTO;
use App\DTO\Response\GetJobProcessingStatusResponse;
use App\DTO\Response\GetShopRecommendationResponse;
use App\DTO\Response\UpdateShopRecommendationNotificationResponse;
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
     * @return GetJobProcessingStatusResponse
     */
    public function getProcessingStatus(string $shopId): GetJobProcessingStatusResponse;

    /**
     * Get the recommendations for a shop.
     *
     * @param string $shopId
     * @return GetShopRecommendationResponse
     */
    public function getShopRecommendations(string $shopId): GetShopRecommendationResponse;

    /**
     * Update the shop recommendation notification.
     *
     * @param string $shopId
     * @param UpdateNotificationSettingsRequestDTO $requestDTO
     * @return UpdateShopRecommendationNotificationResponse
     */
    public function updateShopRecommendationNotification(
        string $shopId,
        UpdateNotificationSettingsRequestDTO $requestDTO,
    ): UpdateShopRecommendationNotificationResponse;
}
