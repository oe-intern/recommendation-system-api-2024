<?php

namespace App\Contracts\Commands;

use App\Collections\Product as ProductCollection;
use App\Collections\Shop as ShopCollection;
use App\Objects\Enums\RecommendationType;
use App\Objects\Enums\RecommendationType as RecommendationTypeEnum;

interface Product
{
    /**
     * Create a product of a shop with data from Shopify.
     *
     * @param ShopCollection $shop
     * @param array $product
     * @return void
     */
    public function create(ShopCollection $shop, array $product): void;


    /**
     * Create a list products of a shop with data from Shopify.
     *
     * @param ShopCollection $shop
     * @param array $products
     * @return void
     */
    public function createMany(ShopCollection $shop, array $products): void;

    /**
     * Update a product of a shop with data from Shopify.
     *
     * @param array $product_data
     * @param ProductCollection $product
     * @return bool
     */
    public function update(ProductCollection $product, array $product_data): bool;

    /**
     * Update the recommendation type a product.
     *
     * @param ProductCollection $product
     * @param RecommendationTypeEnum $recommendation_type
     * @return bool
     */
    public function setRecommendationType(
        ProductCollection $product,
        RecommendationTypeEnum $recommendation_type
    ): bool;

    /**
     * Customize the recommendation products for each product.
     *
     * @param ProductCollection $product
     * @param string $shop_domain
     * @param RecommendationType $recommendation_type
     * @param array $recommendations
     * @return bool
     */
    public function setRecommendationProduct(
        ProductCollection $product,
        string $shop_domain,
        RecommendationType $recommendation_type,
        array $recommendations
    ): bool;

    /**
     * Delete a product.
     *
     * @param ProductCollection $product
     * @return bool
     */
    public function delete(ProductCollection $product): bool;

    /**
     * Add a product recommended for this product.
     *
     * @param string $product_id
     * @param string $recommended_product_id
     * @return void
     */
    public function addRecommendation(string $product_id, string $recommended_product_id): void;

    /**
     * Remove a product recommended for this product.
     *
     * @param string $product_id
     * @param string $recommended_product_id
     * @return void
     */
    public function removeProductRecommendation(
        string $product_id,
        string $recommended_product_id
    ): void;
}
