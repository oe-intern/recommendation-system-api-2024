<?php

namespace App\Contracts\Recommendation;

use App\Collections\ProductCollection;
use App\Exceptions\ProductNotFoundException;

interface IProductRecommendation
{
    /**
     * Get list of recommended products for a product.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getRecommendedProducts(string $shop_id, string $product_id): array;

    /**
     * Get list of manual products gid for a product.
     *
     * @param string $product_id
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getManualProducts(string $product_id): array;

    /**
     * Set list of recommended products for a product.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param array $list_recommended_gid
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
	 */
    public function setRecommendedProducts(
        string $shop_id,
        string $product_id,
        array $list_recommended_gid,
    ): ProductCollection;

    /**
     * Set the recommendation type for a product.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param string $recommendation_type
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
	 */
    public function setRecommendationType(
		string $shop_id,
        string $product_id,
        string $recommendation_type
    ): ProductCollection;

    /**
     * Get full information of a product (including recommendations).
     *
     * @param string $shop_id
     * @param string $product_id
     * @return array
     *
     * @throws ProductNotFoundException
	 */
    public function getFullInfo(string $shop_id, string $product_id): array;

    /**
     * Get settings for auto recommendation.
     *
     * @param string $shop_id
     * @return array
     */
    public function getShopSettings(string $shop_id): array;

    /**
     * Set auto recommendation for a shop.
     *
     * @param string $shop_id
     * @param array $settings
     *
     * @return array
     */
    public function setShopSettings(
        string $shop_id,
        array $settings
    ): array;
}
