<?php

namespace App\Contracts\Recommendation;

use App\Collections\ProductCollection;
use App\Exceptions\ProductNotFoundException;
use App\Objects\Enums\RecommendationType;

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
     * @param RecommendationType|null $recommendation_type
     * @return ProductCollection
     */
    public function setRecommendedProducts(
        string $shop_domain,
        string $product_id,
        array $recommended_products,
        ?RecommendationType $recommendation_type
    ): ProductCollection;

    /**
     * Set the recommendation type for a product.
     *
     * @param string $product_id
     * @param RecommendationType $recommendation_type
     * @return ProductCollection
     */
    public function setRecommendationType(
        string $product_id,
        RecommendationType $recommendation_type
    ): ProductCollection;

    /**
     * Get full information of a product (including recommendations).
     *
     * @param string $product_id
     * @return array
     */
    public function getFullInfo(string $product_id): array;
}
