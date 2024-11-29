<?php

namespace App\Contracts\Recommendation;

use App\Collections\ProductCollection;
use App\Exceptions\ProductNotFoundException;

interface IProductRecommendation
{
    /**
     * Get list of recommended products for a product.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getRecommendedProducts(string $shop_domain, string $product_id): array;

    /**
     * Set list of recommended products for a product.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @param array $recommended_products
     * @param string|null $recommendation_type
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
	 */
    public function setRecommendedProducts(
        string $shop_domain,
        string $product_id,
        array $recommended_products,
        ?string $recommendation_type
    ): ProductCollection;

    /**
     * Set the recommendation type for a product.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @param string $recommendation_type
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
	 */
    public function setRecommendationType(
		string $shop_domain,
        string $product_id,
        string $recommendation_type
    ): ProductCollection;

    /**
     * Get full information of a product (including recommendations).
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return array
     *
     * @throws ProductNotFoundException
	 */
    public function getFullInfo(string $shop_domain, string $product_id): array;

    /**
     * Get settings for auto recommendation.
     *
     * @param string $shop_domain
     * @return array
     */
    public function getAutoRecommendationSettings(string $shop_domain): array;

    /**
     * Set auto recommendation for a shop.
     *
     * @param string $shop_domain
     * @param array $settings
     *
     * @return array
     */
    public function setAutoRecommendationSettings(
        string $shop_domain,
        array $settings
    ): array;
}
