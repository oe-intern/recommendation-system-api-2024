<?php

namespace App\Contracts\Commands;

use App\Collections\ProductCollection;
use App\Collections\ShopCollection;
use App\Objects\Enums\RecommendationType;

interface IProductCommand
{
    /**
     * Create a product of a shop with data from Shopify.
     *
     * @param ShopCollection $shop
     * @param array $product
     * @return ProductCollection
     */
    public function create(ShopCollection $shop, array $product): ProductCollection;


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
     * @param array $productData
     * @param ProductCollection $product
     * @return bool
     */
    public function update(ProductCollection $product, array $productData): bool;

    /**
     * Update a list products of a shop with data from Shopify.
     *
     * @param array $productData
     * @return bool
     */
    public function updateManyByGid(array $productData): bool;

    /**
     * Update recommendation for a product.
     *
     * @param string $productId
     * @param array $recommendations
     * @return bool
     */
    public function updateProductRecommendation(string $productId, array $recommendations): bool;

    /**
     * Update recommendation for a new product.
     *
     * @param string $productId
     * @param array $recommendations
     * @param RecommendationType $recommendationType
     * @return bool
     */
    public function updateNewProductRecommendation(string $productId, array $recommendations, RecommendationType $recommendationType): bool;

    /**
     * Update list recommendation for products.
     *
     * @param array $recommendationData
     * @return bool
     */
    public function updateManyRecommendation(array $recommendationData): bool;

    /**
     * Update list recommendation for products.
     *
     * @param array $recommendationData
     * @return bool
     */
    public function updateManyDefaultRecommendation(array $recommendationData): bool;

    /**
     * Update the recommendation type a product.
     *
     * @param ProductCollection $product
     * @param RecommendationType $recommendationType
     * @return bool
     */
    public function setRecommendationType(
        ProductCollection $product,
        RecommendationType $recommendationType
    ): bool;

    /**
     * Customize the recommendation products for each product.
     *
     * @param ProductCollection $product
     * @param string $shopId
     * @param array $recommendations
     * @param RecommendationType $recommendationType
     * @return bool
     */
    public function setManualProduct(
        ProductCollection $product,
        string $shopId,
        array $recommendations,
        RecommendationType $recommendationType,
    ): bool;

    /**
     * Delete a product.
     *
     * @param ProductCollection $product
     * @return bool
     */
    public function delete(ProductCollection $product): bool;

    /**
     * Delete a list of products by gid.
     *
     * @param array $productGids
     * @return bool
     */
    public function deleteManyByGid(array $productGids): bool;

    /**
     * Add a product recommended for this product.
     *
     * @param string $productId
     * @param string $recommendedProductId
     * @return void
     */
    public function addRecommendation(string $productId, string $recommendedProductId): void;

    /**
     * Remove a product recommended for this product.
     *
     * @param string $productId
     * @param string $recommendedProductId
     * @return void
     */
    public function removeProductRecommendation(
        string $productId,
        string $recommendedProductId
    ): void;

    /**
     * Active the recommendation for all products of a shop.
     *
     * @param string $shopId
     * @return bool
     */
    public function activateRecommendation(string $shopId): bool;

    /**
     * Deactivate the recommendation for all products of a shop.
     *
     * @param string $shopId
     * @return bool
     */
    public function deactivateRecommendation(string $shopId): bool;
}
