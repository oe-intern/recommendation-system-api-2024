<?php

namespace App\Contracts\Recommendation;

use App\DTO\Request\SetActiveRecommendationRequestDTO;
use App\DTO\Request\SetProductRecommendationRequestDTO;
use App\DTO\Request\SetRecommendationTypeRequestDTO;
use App\DTO\Request\UpdateShopSettingRequestDTO;
use App\DTO\Response\GetProductRecommendationTypeResponse;
use App\DTO\Response\GetRecommendedProductsResponse;
use App\DTO\Response\SetManualRecommendationResponse;
use App\DTO\Response\SetRecommendationTypeResponse;
use App\DTO\Response\ShopSettingsResponse;
use App\Exceptions\ProductNotFoundException;

interface IProductRecommendation
{
    /**
     * Get list of recommended products for a product.
     *
     * @param string $shopId
     * @param string $productId
     * @return GetRecommendedProductsResponse
     *
     * @throws ProductNotFoundException
     */
    public function getRecommendedProducts(string $shopId, string $productId): GetRecommendedProductsResponse;

    /**
     * Get list of manual products gid for a product.
     *
     * @param string $productId
     * @return GetRecommendedProductsResponse
     *
     * @throws ProductNotFoundException
     */
    public function getManualProducts(string $productId): GetRecommendedProductsResponse;

    /**
     * Set list of recommended products for a product.
     *
     * @param string $shopId
     * @param string $productId
     * @param SetProductRecommendationRequestDTO $requestDTO
     * @return SetManualRecommendationResponse
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendedProducts(
        string $shopId,
        string $productId,
        SetProductRecommendationRequestDTO $requestDTO,
    ): SetManualRecommendationResponse;

    /**
     * Get product recommendation type.
     *
     * @param string $shopId
     * @param string $productId
     * @return GetProductRecommendationTypeResponse
     */
    public function getProductRecommendationType(
        string $shopId,
        string $productId
    ): GetProductRecommendationTypeResponse;

    /**
     * Set the recommendation type for a product.
     *
     * @param string $shopId
     * @param string $productId
     * @param SetRecommendationTypeRequestDTO $requestDTO
     * @return SetRecommendationTypeResponse
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendationType(
        string $shopId,
        string $productId,
        SetRecommendationTypeRequestDTO $requestDTO,
    ): SetRecommendationTypeResponse;

    /**
     * Get settings for auto recommendation.
     *
     * @param string $shopId
     * @return ShopSettingsResponse
     */
    public function getShopSettings(string $shopId): ShopSettingsResponse;

    /**
     * Set auto recommendation for a shop.
     *
     * @param string $shopId
     * @param UpdateShopSettingRequestDTO $requestDTO
     *
     * @return ShopSettingsResponse
     */
    public function setShopSettings(
        string $shopId,
        UpdateShopSettingRequestDTO $requestDTO,
    ): ShopSettingsResponse;

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
