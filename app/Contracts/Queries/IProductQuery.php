<?php

namespace App\Contracts\Queries;

use App\Collections\ProductCollection;
use App\Collections\ShopCollection;
use App\Exceptions\ProductNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface IProductQuery
{
    /**
     * Get a product of a shop by ID.
     *
     * @param string $productId
     * @return ProductCollection|null
     */
    public function getById(string $productId): ?ProductCollection;

    /**
     * Get a product of a shop by GID.
     *
     * @param string $gid
     * @return ProductCollection|null
     */
    public function getByGid(string $gid): ?ProductCollection;

    /**
     * Get list products of a shop by IDs
     *
     * @param array $productIds
     * @param string $shopId
     * @return array
     */
    public function getByShopIdAndIds(string $shopId, array $productIds): array;

    /**
     * Get list products of a shop by collection
     *
     * @param ShopCollection $shop
     * @return array
     */
    public function getByShopCollection(ShopCollection $shop): array;

    /**
     * Check if the products exist in the database.
     *
     * @param string $shopId
     * @param string $productId
     * @return ProductCollection
     *
     * @throws ModelNotFoundException
     */
    public function checkProductExist(string $shopId, string $productId): ProductCollection;

    /**
     * Get a product of a shop by ID and shop domain.
     *
     * @param string $shopId
     * @param string $productId
     * @return ProductCollection|null
     */
    public function getByShopIdAndId(string $shopId, string $productId): ?ProductCollection;

    /**
     * Get a product of a shop by Shopify ID.
     *
     * @param string $shopId
     * @param string $gid
     * @return ProductCollection|null
     */
    public function getByShopIdAndGid(string $shopId, string $gid): ?ProductCollection;

    /**
     * Get list products ID using this product for recommendation.
     *
     * @param string $productId
     * @return array
     */
    public function getReferencedProducts(string $productId): array;

    /**
     * Get list of optional products for recommendation.
     *
     * * @param string $productId
     * * @return array
     */
    public function getManualProducts(string $productId): array;

    /**
     * Get list of optional products for recommendation.
     *
     * @param string $productId
     * @return array
     */
    public function getDefaultProducts(string $productId): array;

    /**
     * Get list of optional products for recommendation.
     *
     * @param string $productId
     * @return array
     */
    public function getAutoRecommendationProducts(string $productId): array;

	/**
	 * Validate product IDs exist in the shop.
	 *
	 * @param string $shopId
	 * @param array $productIds
	 * @return array
	 *
	 * @throws ProductNotFoundException
	 */
	public function validateProductIds(string $shopId, array $productIds): array;

    /**
     * Validate product IDs exist in the shop.
     *
     * @param string $shopId
     * @param array $productGids
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function validateListProductGid(string $shopId, array $productGids): array;

	/**
	 * Validate product ID exist in the shop.
	 *
	 * @param string $shopId
	 * @param string $productId
	 * @return ProductCollection
	 *
	 * @throws ProductNotFoundException
	 */
	public function validateProductId(string $shopId, string $productId): ProductCollection;

    /**
     * Get list of product GID by IDs.
     *
     * @param array $productIds
     * @return array
     */
    public function getListGidByIds(array $productIds): array;

    /**
     * Get list of product ID by GIDs.
     *
     * @param array $productGids
     * @return array
     */
    public function getIdsByGids(array $productGids): array;

    /**
     * Get product GID by ID.
     *
     * @param string $productId
     * @return string
     */
    public function getGidById(string $productId): string;

    /**
     * Get all product of a shop not in a list of product ids.
     *
     * @param string $shopId
     * @param array $productIds
     * @return array
     */
    public function getProductsNotIn(string $shopId, array $productIds): array;

    /**
     * Get all product of a shop not in a list of product GIDs.
     *
     * @param string $shopId
     * @param array $productGids
     * @return array
     */
    public function getProductGidsNotIn(string $shopId, array $productGids): array;

    /**
     * Get list of product active by IDs.
     *
     * @param array $ids
     * @return array
     */
    public function getActiveProducts(array $ids): array;

    /**
     * Get handle and GID by IDs.
     *
     * @param array $productIds
     * @return array
     */
    public function getHandleAndGidByIds(array $productIds): array;

    /**
     * Get existing products by GIDs and shop ID.
     *
     * @param string $shopId
     * @param array $productGids
     * @return array
     */
    public function getExistingProductsGid(string $shopId, array $productGids): array;

    /**
     * Get product ID and GID by shop ID.
     *
     * @param string $shopId
     * @return array
     */
    public function getIdAndGidByShopId(string $shopId): array;

    /**
     * Get map of product ID with key GID by shop ID.
     *
     * @param string $shopId
     * @return array
     */
    public function getMapIdWithKeyGidByShopId(string $shopId): array;
}
