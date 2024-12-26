<?php

namespace App\Contracts\Recommendation;

use App\Collections\ProductCollection;
use App\DTO\Request\SetActiveRecommendationRequestDTO;
use App\DTO\Request\SetProductRecommendationRequestDTO;
use App\DTO\Request\SetRecommendationTypeRequestDTO;
use App\DTO\Request\UpdateShopSettingRequestDTO;
use App\Exceptions\ProductNotFoundException;

interface IProductRecommendation
{
    /**
     * Get list of recommended products for a product.
     *
     * @param string $shopId
     * @param string $productId
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getRecommendedProducts(string $shopId, string $productId): array;

    /**
     * Get list of manual products gid for a product.
     *
     * @param string $productId
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getManualProducts(string $productId): array;

    /**
     * Set list of recommended products for a product.
     *
     * @param string $shopId
     * @param string $productId
     * @param SetProductRecommendationRequestDTO $requestDTO
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendedProducts(
        string $shopId,
        string $productId,
        SetProductRecommendationRequestDTO $requestDTO,
    ): ProductCollection;

    /**
     * Set the recommendation type for a product.
     *
     * @param string $shopId
     * @param string $productId
     * @param SetRecommendationTypeRequestDTO $requestDTO
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendationType(
        string $shopId,
        string $productId,
        SetRecommendationTypeRequestDTO $requestDTO,
    ): ProductCollection;

    /**
     * Get settings for auto recommendation.
     *
     * @param string $shopId
     * @return array
     */
    public function getShopSettings(string $shopId): array;

    /**
     * Set auto recommendation for a shop.
     *
     * @param string $shopId
     * @param UpdateShopSettingRequestDTO $requestDTO
     *
     * @return array
     */
    public function setShopSettings(
        string $shopId,
        UpdateShopSettingRequestDTO $requestDTO
    ): array;

    /**
     * Activate recommendation for all product of a shop.
     *
     * @param string $shopId
     * @param SetActiveRecommendationRequestDTO $requestDTO
     * @return bool
     */
    public function activateRecommendation(
        string $shopId,
        SetActiveRecommendationRequestDTO $requestDTO,
    ): bool;

    /**
     * Update product default recommendation for a shop.
     *
     * @param array $recommendationData
     * @param array $gidToIdMap
     * @return bool
     */
    public function updateManyDefaultRecommendation(array $recommendationData, array $gidToIdMap): bool;

    /**
     * Update product recommendation for a shop.
     *
     * @param array $recommendationData
     * @param array $gidToIdMap
     * @return bool
     */
    public function updateManyRecommendation(array $recommendationData, array $gidToIdMap): bool;
}
