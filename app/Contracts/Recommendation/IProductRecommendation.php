<?php

namespace App\Contracts\Recommendation;

use App\Collections\ProductCollection;
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
     * @param array $recommendedGids
     * @param string|null $recommendationType
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendedProducts(
        string $shopId,
        string $productId,
        array $recommendedGids,
        ?string $recommendationType,
    ): ProductCollection;

    /**
     * Set the recommendation type for a product.
     *
     * @param string $shopId
     * @param string $productId
     * @param string $recommendationType
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendationType(
        string $shopId,
        string $productId,
        string $recommendationType
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
     * @param array $settings
     *
     * @return array
     */
    public function setShopSettings(
        string $shopId,
        array $settings
    ): array;

    /**
     * Activate recommendation for all product of a shop.
     *
     * @param string $shopId
     * @param string $status
     * @return bool
     */
    public function activateRecommendation(
        string $shopId,
        string $status,
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
