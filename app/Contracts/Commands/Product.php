<?php

namespace App\Contracts\Commands;

use App\Collections\Shop as ShopCollection;
use App\Collections\Product as ProductCollection;
use App\Objects\Enums\RecommendationType;
use App\Objects\Enums\RecommendationType as RecommendationTypeEnum;

interface Product
{
    /**
     * Create a product of a shop with data from Shopify.
     *
     * @param array $product
     * @param ShopCollection $shop
     * @return void
     */
    public function create(array $product, ShopCollection $shop): void;


    /**
     * Create a list products of a shop with data from Shopify.
     *
     * @param array $products
     * @param ShopCollection $shop
     * @return void
     */
    public function createMany(array $products, ShopCollection $shop): void;

    /**
     * Update a product of a shop with data from Shopify.
     *
     * @param array $product
     * @param ShopCollection $shop
     * @return bool
     */
    public function update(array $product, ShopCollection $shop): bool;

    /**
     * Update the recommendation type a product.
     *
     * @param ProductCollection $product
     * @param RecommendationTypeEnum $recommendation_type
     * @return bool
     */
    public function setRecommendationType(ProductCollection $product, RecommendationTypeEnum $recommendation_type): bool;

    /**
     * Customize the recommendation products for each product.
     *
     * @param ProductCollection $product
     * @param ShopCollection $shop
     * @param RecommendationType $recommendation_type
     * @param array $recommendations
     * @return bool
     */
    public function setRecommendationProduct(ProductCollection $product, ShopCollection $shop, RecommendationType $recommendation_type, array $recommendations): bool;

    /**
     * Delete a product.
     *
     * @param string $product_id
     * @param ShopCollection $shop
     * @return bool
     */
    public function delete(string $product_id, ShopCollection $shop): bool;

    /**
     * Add a product recommended for this product.
     *
     * @param string $product_id
     * @param ShopCollection $shop
     * @param string $recommended_product_id
     * @return void
     */
    public function addRecommendation(string $product_id, ShopCollection $shop, string $recommended_product_id): void;

    /**
     * Remove a product recommended for this product.
     *
     * @param string $product_id
     * @param ShopCollection $shop
     * @param string $recommended_product_id
     * @return void
     */
    public function removeProductRecommendation(string $product_id, ShopCollection $shop, string $recommended_product_id): void;
}
